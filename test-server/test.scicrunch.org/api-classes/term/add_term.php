<?php

/*
 * Add to both ilx_identifiers and terms table in one go.
 * Specifically created to reduce API latency via remote POST.
 */
function addTerm($user, $api_key, $cid, $args){
    require_once "term_elasticsearch.php";
    require_once "term_curie_by_prefix.php";
    require_once "term_by_ilx.php";
    $dbObj = new DbObj();
    // Check User meta for permissions
    if(!\APIPermissionActions\checkAction("term", $api_key, $user, Array("cid" => $cid, "dbObj" => $dbObj)))
        return APIReturnData::build("not allowed", false, 403);
    $cuser = \APIPermissionActions\getUser($api_key, $user);
    // Label duplicate check
    // Note: keep if and elif just in case we want to do something extra for these cases.
    if (($cuser->role == '2') && ($args['force'] == true)) {
    // User is level 2 and wants to force it.
    } elseif ($args['type'] == 'pde') {
    // PDE types are going to have valid duplicates.
    } else {
        $existing = Term::checkExisting($args['label'], $cuser->id, $args['type']);
        if (!is_null($existing))
            return APIReturnData::build(getTermByIlx($user, $api_key, $existing->ilx), true, 200);
    }
    // Check if there are any missing fields. Extra fields are okay, they will be skipped.
    foreach(Term::$required as $req)
        if(!isset($args[$req]) && ($req != 'ilx'))  // 'ilx' will be directly checked during creation
            return APIReturnData::quick400("missing required field: " . $req);
    // Adds ILX ID assigned from table "ilx_identifiers" to current args
    $new_entity = IlxIdentifier::createNewObj($args['label'], $cuser->id, $note=NULL, $defining_url=NULL, $fragment=NULL, $type=$args['type']);
    if(is_null($new_entity))
        return APIReturnData::quick500("Server could not handle input: " . $args . " With DB out: " . $new_entity);
    $args['ilx'] = $new_entity->arrayForm()['fragment'];
    // Check if there are any missing fields. Extra fields are okay, they will be skipped.
    foreach(Term::$required as $req)
        if(!isset($args[$req]))
            return APIReturnData::quick400("missing required field: " . $req);
    // DbObj::verifyRalatedTables is picky and will break if these fields exist
    unset($args['force']);
    // Validate fields fields with list values.
    $related_tables = DbObj::verifyRalatedTables($args);
    if ($related_tables['status'] === false) return $related_tables['msg'];
    foreach($related_tables['existing_ids'] as $ex) {
        if (TermExistingId::exists($iri=$ex['iri'])){
            return APIReturnData::quick400("IRI already exists: " . $iri);
        }
    }
    // Insert entity into "terms" table
    $term = insertTerm($dbObj, $args, $cuser->id, $cid, $related_tables);
    // Update Logs
    $changed_type = "new term";
    $changed_des = $term->label . " (". $term->ilx .") has beed added.";
    $comment = "";
    $term->insertTermUpdateLog($changed_type, $changed_des, $comment);
    $return_values = DbObj::printableTerm($term); // convert Object to Array
    termElasticUpsert($cuser, $api_key, $term->id);
    // Return entity Array with populated fields.
    return APIReturnData::build($return_values, true, 201);
}

function insertTerm($dbObj, $args, $uid, $cid, $related_tables){
    include_once '/assets/plugins/purifier/HTMLPurifier.auto.php';

    $term = new Term($dbObj);
    //check if correct data structure before insert
    $purifier_config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($purifier_config);
    foreach($args as $field => $val){
        //print $field . ":" . $val . " ";
        if(in_array($field, Term::$properties)){
            $val = $purifier->purify($val);
            $term->$field = $val;
        }
    }

    // insert into db
    $term->version = 1;
    $term->display_superclass = 1;
    $term->status = 0;
    $term->orig_uid = $uid;
    $term->orig_cid = $cid;
    $term->cid = $cid;
    $term->uid = $uid;
    $term->orig_time = time();
    $term->insertDB();

   foreach ($related_tables['synonyms'] as $synonym) {
        $syn = new TermSynonym($dbObj);
        $syn->tid = $term->id;
        foreach ($synonym as $key=>$val) {
            $syn->$key = $val;
        }
        $syn->version = 1;

        $syn->insertDB();
        $term->synonyms[] = $syn;
   }

   foreach ($related_tables['superclasses'] as $superclass) {
       $sup = new TermSuperclass($dbObj);
       $sup->tid = $term->id;
       foreach ($superclass as $key=>$val) {
           $sup->$key = $val;
       }
       $sup->version = 1;

       $sup->insertDB();
       $term->superclasses[] = $sup;
   }

   foreach ($related_tables['existing_ids'] as $existing_id) {
       $eid = new TermExistingId($dbObj);
       $eid->tid = $term->id;
       foreach ($existing_id as $key=>$val) {
           $eid->$key = $val;
       }
       $eid->version = 1;

       $eid->insertDB();
       $term->existing_ids[] = $eid;
   }
   // add ILX existing_id entry
   $eid = new TermExistingId($dbObj);
   $eid->version = 1;
   $eid->tid = $term->id;
   $parts = explode("_", $term->ilx);
   $cc_entry = getTermCurieByPrefix($user, $api_key, strtoupper($parts[0]));
   $eid->curie = strtoupper($parts[0]) . ":" . $parts[1];
   $eid->iri = $cc_entry['namespace'] . $parts[1];
   $eid->curie_catalog_id = $cc_entry['id'];

   if (!isset($related_tables['existing_ids']) || count($related_tables['existing_ids']) == 0){
       $eid->preferred = 1;
   } else {
       $eid->preferred = 0;
   }
   $eid->insertDB();
   $term->existing_ids[] = $eid;


   foreach ($related_tables['ontologies'] as $onto) {
       foreach ($onto as $key=>$val) {
           if ($key == 'id') {
               $term->joinToOntology($val);
           }
       }
   }
   $term->ontologies = $term->getOntologies();

   if ($term->type == 'annotation'){
       $atype = new TermAnnotationType($dbObj);
       $atype->annotation_tid = $term->id;
       $atype->type = isset($args['annotation_type']) ? $args['annotation_type'] : 'text';
       $atype->insertDB();

       $term->annotation_type = $atype->type;
   }

   return $term;
}

?>
