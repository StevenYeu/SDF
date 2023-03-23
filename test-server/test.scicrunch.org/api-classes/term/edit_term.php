<?php
require_once "term_elasticsearch.php";
//require_once "term_elastic_upsert.php";

//insert into terms_versions table: old term table values;
//for term_versions.term_related,
//create json of term related tables with old version
//update terms table: new term values and incremented version
//delete from term related tables: entries with old tid and version

/*
 * Get total entity metadata for term from either its mysql id or ilx fragment.
 */
function getTermInfo($dbObj, $id){
    $term = new Term($dbObj);
    if ( is_numeric($id) ) {
        $term->getById($id);
    } else {
        $term->getByIlx($id);
    }
    $term->getById($id);
    $term->getExistingIds();
    $term->getSynonyms();
    $term->getSuperclasses();
    $term->getOntologies();
    if ( $term->type == 'annotation' )
        $term->getAnnotationType();
    return $term;
}

function editTerm($user, $api_key, $term_id, $cid, $args, $add2elastic='1'){
    $dbObj = new DbObj();
    if(!\ApiPermissionActions\checkAction("term", $api_key, $user, Array("cid" => $cid, "dbObj" => $dbObj))) return "not allowed";
    $cuser = \APIPermissionActions\getUser($api_key, $user);
    unset($args['curie']);
    // get current term with its version
    $term = getTermInfo($dbObj, $term_id);
    if (!$term) return "Term id does not exist: " . $term_id;
    // Filter for only needed fields
    $related_tables = DbObj::verifyRalatedTables($args);
    if ($related_tables['status'] === false)
        return $related_tables['msg'];
    // Check for duplicate existing ids amongst total terms
    foreach($related_tables['existing_ids'] as $ex) {
        $rows = TermExistingId::exists($ex['iri']);
        if ($rows != false) {
            foreach($rows as $row) {
                if ($row['tid'] != $term->id) {
                    $exterm = getTermInfo($dbObj, $row['tid']);
                    if ($exterm->status == 0)
                        return "IRI already exists: " . $ex['iri'];
                }
            }
        }
    }
    $term = updateTerm($dbObj, $term, $args, $user->id, $related_tables);
    $return_values = DbObj::printableTerm($term);
    // $start = round(microtime(true) * 1000);
    // Either add or delete entity from Elasticsearch
    if ($term->status == 0) {
       termElasticUpsert($user, $api_key, $term->id);
    }
    elseif ($term->status < 0){
       termElasticDelete($user, $api_key, $term->ilx);
    }
    // $return_values['es-time'] = round(microtime(true) * 1000) - $start;
    return $return_values;
}

function updateTerm($dbObj, $term, $args, $uid, $related_tables){
    include_once '/assets/plugins/purifier/HTMLPurifier.auto.php';

    // Used as reference if we needed to del superclass, synonyms, or existing_ids
    $old_version = $term->version;

    //insert into term_versions
    $version = new TermVersion($dbObj);
    $version->tid = $term->id;

    $json_array = array();
    $array = get_object_vars($term);
    // info from terms table
    foreach($array as $field => $value){
        if(in_array($field, TermVersion::$properties) && $field !== "id"){
            $version->$field = $value;
        }
        if(in_array($field, TermVersion::$term_properties)){
            $json_array[$field] = $value;
        }
        if(in_array($field, TermVersion::$other_properties)) {
            $json_array[$field] = $value;
        }
    }

    //convert term related tables into json to insert into term_versions
    foreach ($term->synonyms as $syn) {
        $json_array['synonyms'][] = DbObj::toArray('TermSynonym', $syn);
    }

    foreach ($term->superclasses as $sup) {
        $json_array['superclasses'][] = DbObj::toArray('TermSuperclass', $sup);
    }

    foreach ($term->existing_ids as $eid) {
        $json_array['existing_ids'][] = DbObj::toArray('TermExistingId', $eid);
    }

    foreach ($term->ontologies as $ont) {
        $json_array['ontologies'][] = $ont;
    }

    $version->term_info = json_encode($json_array);
    $version->insertDB();

    //update term properties from request
    $purifier_config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($purifier_config);

    foreach($args as $field => $value){
        if(in_array($field, Term::$properties)){
            $value = $purifier->purify($value);
            $term->$field = $value;
        }
    }

    $term->version = $old_version + 1;
    $term->time = time();
    $term->uid = $uid;
    // Update Term
    $term->updateRowDB();

    /*
        Create new entries for related tables of current entity
    */
    if (!empty($related_tables['synonyms'])) {
        foreach ($related_tables['synonyms'] as $arr) {
            $obj = new TermSynonym($dbObj);
            $obj->tid = $term->id;
            foreach ($arr as $key => $val) {
                $obj->$key = $val;
            }
            $obj->version = $old_version + 1;

            $obj->insertDB();
            $term->synonyms[] = $obj;
        }
        TermSynonym::deleteDB($dbObj, $term->id, $old_version);
    } else {
        $dbObj->update('term_synonyms', 'si', array("version"), array($term->version, $term->id), 'where tid=?');
    }
    if (!empty($related_tables['superclasses'])) {
        foreach ($related_tables['superclasses'] as $arr) {
            $obj = new TermSuperclass($dbObj);
            $obj->tid = $term->id;
            foreach ($arr as $key=>$val) {
                $obj->$key = $val;
            }
            $obj->version = $old_version + 1;

            $obj->insertDB();
            $term->superclasses[] = $obj;
        }
        TermSuperclass::deleteDB($dbObj, $term->id, $old_version);
    } else {
        $dbObj->update('term_superclasses', 'si', array("version"), array($term->version, $term->id), 'where tid=?');
    }
    if (!empty($related_tables['existing_ids'])) {
        // Give preferred to newest.
        $min_time = 0;
        // todo disable ilx curie from being viewed and create a new one in first index as default preferred
        $preferred_curie = $related_tables['existing_ids'][0]['curie'];
        foreach ($related_tables['existing_ids'] as $arr) {
            if ($arr['preferred'] == '1') {
                if ($min_time == 0) {
                    $min_time = $arr['time'];
                    $preferred_curie = $arr['curie'];
                } elseif ($min_time > $arr['time']) {
                    $min_time = $arr['time'];
                    $preferred_curie = $arr['curie'];
                }
            }
        }
        foreach ($related_tables['existing_ids'] as $arr) {
            $obj = new TermExistingId($dbObj);
            $obj->tid = $term->id;
            // reassign peferred based on newest selected
            if ($arr['curie'] == $preferred_curie) $arr['preferred'] = '1';
            else $arr['preferred'] = '0';
            foreach ($arr as $key=>$val) {
                $obj->$key = $val;
            }
            $obj->version = $old_version + 1;

            $obj->insertDB();
            $term->existing_ids[] = $obj;
        }
        TermExistingId::deleteDB($dbObj, $term->id, $old_version);
    } else {
        $dbObj->update('term_existing_ids', 'si', array("version"), array($term->version, $term->id), 'where tid=?');
    }

    //update terms
    $array = get_object_vars($term);
    $changed_type = "term edited";
    $changed_des = "";
    $comment = "";
    foreach ($array as $field => $value) {
        // if ( $field !== 'id' && in_array($field, Term::$properties) && !in_array($field,array_keys(Term::$related_map)) ) {
            // $term->updateDB($field, $value);
            if($field == "status") {
                if($value == -1) {
                    $changed_type = "term inactive";
                    $changed_des = $term->label . " - " . $term->ilx . " has been inactive.";
                }
                else if($value == -2) {
                    $changed_type = "term deleted";
                    $changed_des = $term->label . " - " . $term->ilx . " has been deleted.";
                }
            }
        // }
    }
    if($changed_des == "") $changed_des = $term->label . " - " . $term->ilx . " has been changed.";
    $term->insertTermUpdateLog($changed_type, $changed_des, $comment, $uid);

    //remove old ontology links and insert new ones
    $term->disjoinFromOntologies();
    foreach ($related_tables['ontologies'] as $arr) {
        $term->joinToOntology($arr['id']);
    }

    if ($term->type == 'annotation'){
        isset($args['annotation_type']) ? $term->updateAnnotationType($args['annotation_type']) : $term->updateAnnotationType('text');
    }
    $term->getAnnotationType();

    // remove old from related tables
    // TermSynonym::deleteDB($dbObj, $term->id, $old_version);
    // TermSuperclass::deleteDB($dbObj, $term->id, $old_version);
    // TermExistingId::deleteDB($dbObj, $term->id, $old_version);

    // Easy purge of old with new output
    // TODO: Preferred to return just server response, but that would involve a complete rewrite.
    // $term = getTermInfo($dbObj, $term->id);
    $term->getExistingIds();
    $term->getSynonyms();
    $term->getSuperclasses();
    // $start = round(microtime(true) * 1000);
    // $term->getAnnotations();
    // $term['annotation-time'] = round(microtime(true) * 1000) - $start;
//    $start = round(microtime(true) * 1000);
    // $term->getRelationships();
//    $term['relationship-time'] = round(microtime(true) * 1000) - $start;
    return $term;
}

?>
