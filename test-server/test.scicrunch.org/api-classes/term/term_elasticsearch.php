<?php

function termElasticSearch($user, $api_key, $fields){
    global $config;

    $host = $config['elasticsearch']['protocol'] . "://" . $config['elasticsearch']['host'] . "/" . $config['elasticsearch']['index'] . "/" . $config['elasticsearch']['type'];
    //$host = $config['elasticsearch']['protocol'] . "://" . $config['elasticsearch']['host'];
    $searchTerm = isset($fields['term']) && $fields['term'] != "null" ? $fields['term'] : "";
    $from = isset($fields['from']) && $fields['from'] != "null" ? $fields['from'] : 0;
    $size = isset($fields['size']) && $fields['size'] != "null" ? $fields['size'] : 200;
    $type = isset($fields['type']) && $fields['type'] != "null" ? $fields['type'] : 'all';
    $cid = isset($fields['cid']) && $fields['cid'] != "null" ? $fields['cid'] : -1;
    $query = isset($fields['query']) && $fields['query'] != "" && $fields['query'] != NULL ? $fields['query'] : "";
    $phrase = preg_match('/^(["\'])/m', $searchTerm) || preg_match('/^&#34;/', $searchTerm) || preg_match('/^&#39;/', $searchTerm) ? true : false;
    $return = array();

    //return $fields;
    $highlight = '{
        "fields":{
            "label" : {},
            "type" : {},
            "definition": {},
            "ilx": {},
            "ontology.url": {},
            "synonyms.literal": {},
            "existing_ids.curie": {},
            "existing_ids.iri": {},
            "comment": {},
            "superclass.ilx": {},
            "superclasses.label": {},
            "relationships.term1_label": {},
            "relationships.term2_label": {},
            "relationships.term1_ilx": {},
            "relationships.term2_ilx": {},
            "relationships.relationship_term_label": {},
            "relationships.relationship_term_ilx": {},
            "annotations.term_label": {},
            "annotations.term_ilx": {},
            "annotations.annotation_term_label": {},
            "annotations.annotation_term_ilx": {},
            "annotations.value": {}
        }
    }';

    $phrase_highlight = '{
        "fields":{
            "label": {},
            "definition": {},
            "comment": {},
            "synonyms.literal": {},
            "superclasses.label": {},
            "relationships.term1_label": {},
            "relationships.term2_label": {},
            "relationships.relationship_term_label": {},
            "annotations.term_label": {},
            "annotations.annotation_term_label": {},
            "annotations.value": {}
        }
    }';

    $match_fields =  '
        "ilx^3",
        "label^4",
        "type^2",
        "definition^2",
        "comment",
        "ontologies.url^2",
        "synonyms.literal^3",
        "existing_ids.curie^2",
        "existing_ids.iri^2",
        "superclasses.ilx",
        "superclasses.label",
        "relationships.term1_label",
        "relationships.term1_ilx",
        "relationships.term12_label",
        "relationships.term2_ilx",
        "relationships.relationship_term_label",
        "relationships.relationship_term_ilx",
        "annotations.term_label",
        "annotations.term_ilx",
        "annotations.annotation_term_label",
        "annotations.annotation_term_ilx",
        "annotations.value"
    ';

    $phrase_match_fields = '
        "label^4",
        "definition^2",
        "comment^2",
        "synonyms.literal^3",
        "superclasses.label",
        "relationships.term1_label",
        "relationships.term12_label",
        "relationships.relationship_term_label",
        "annotations.term_label",
        "annotations.annotation_term_label",
        "annotations.value"
    ';

    if ($phrase == true) {
        $searchTerm = str_replace('&#34;', "", $searchTerm);
        $searchTerm = str_replace('&#39;', "", $searchTerm);
        $searchTerm = str_replace('\'', "", $searchTerm);
        $searchTerm = str_replace('"', "", $searchTerm);

        $highlight = $phrase_highlight;
        $match_fields = $phrase_match_fields;
    }
    $highlight = preg_replace('/\s+/', '', $highlight);
    $match_fields = preg_replace('/\s+/', '', $match_fields);

    $ancestor_filter = "";
    if ($cid != -1 && $cid != "") {
        require_once $_SERVER["DOCUMENT_ROOT"] . '/api-classes/term/get_community_terms.php';

        $ancestor_filter = "\"terms\":{\"ancestors.ilx\":[]}";
        $ancestors = getCommunityTerms($user, $api_key, $cid);
        if (count($ancestors) > 0) {
            $ancestor_ilxes = implode('","', $ancestors);
            $ancestor_ilxes = '"' . $ancestor_ilxes . '"';
            $ancestor_filter = "\"terms\":{\"ancestors.ilx\":[$ancestor_ilxes]}";
        }
    }

    $type_filter = '';
    if ($type != 'all') {
        $type_filter = "\"term\":{\"type\":\"$type\"}";
    }

    if ($query == ''){
        if ($searchTerm == '') {
            $query = "{\"bool\":{\"must\":[{\"match_all\":{}},{{$type_filter}},{{$ancestor_filter}}]}}";
        }
        else {
            $multi_match = "{\"query\": \"$searchTerm\",\"fields\":[$match_fields]}";
            if ($phrase == true) {
                $multi_match = "{\"query\":\"$searchTerm\",\"type\":\"phrase\",\"fields\":[$match_fields]}";
            }
            $query = "{ \"bool\":{ \"must\":[{\"multi_match\": $multi_match},{{$type_filter}},{{$ancestor_filter}}]}}";
        }
    }
    // "must" option can no longer have empty dictionaries
    // if inner empty
    $query = str_replace("{},", "", $query);
    // if end empty
    $query = str_replace(",{}", "", $query);

    $cmd  = $host . "/_search?pretty";
    $json = "
    {
        \"from\" : $from,
        \"size\" : $size,
        \"query\": $query,
        \"highlight\": $highlight
    }
        ";
//    echo "\n" . $json . "\n";
//      return $json;


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $cmd);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $return  = curl_exec($ch);
    curl_close($ch);

    $return = json_decode($return);
    return $return;
}

function termElasticUpsert($user, $api_key, $tid, $skip_verify = false) {
    if(!$skip_verify && !\APIPermissionActions\getUser($api_key, $user)) return "not allowed";
    if ($tid < 0){
        return "Term ID is not specified.";
    }

    global $config;
    $host = $config['elasticsearch']['protocol'] . "://" . $config['elasticsearch']['host'] . "/" . $config['elasticsearch']['index'] . "/" . $config['elasticsearch']['type'];

    $dbObj = new DbObj();
    $termObj = new Term($dbObj);
    $termObj->getById($tid);

    $term = DbObj::termForElasticSearch($termObj, true);
    $data_json = json_encode($term);
    $url = $host . "/" . $term['ilx'];
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
    curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response  = curl_exec($ch);
    curl_close($ch);
    $return = json_decode($response);
    return $return;
}

function termElasticUpsertBulk($user, $api_key, $term_ids, $skip_verify = false) {
    if(!$skip_verify && !\APIPermissionActions\getUser($api_key, $user)) return "not allowed";

    $dbObj = new DbObj();
    $terms = Array();
    foreach($term_ids as $ti) {
        $termObj = new Term($dbObj);
        $termObj->getById($ti);
        if($termObj->ilx) {
            $terms[] = $termObj;
        }
    }

    $bulk_array = Array();
    foreach($terms as $term) {
        $bulk_array[] = json_encode(Array("index" => Array("_id" => $term->ilx)));
//        $term->getExistingIds();
//        $term->getSynonyms();
//        $term->getSuperclasses();
//        $term->getOntologies();
//        $term->getRelationships();
//        $term->getAnnotations();
//        if ($term->type == 'annotation'){
//            $term->getAnnotationType();
//        }
        $bulk_array[] = json_encode(DbObj::termForElasticSearch($term, true));
        // $bulk_array[] = json_encode($term);
    }
    $bulk_str = implode("\n", $bulk_array) . "\n";

    $ch = curl_init();
    $url = $GLOBALS["config"]["elasticsearch"]["protocol"] . "://" . $GLOBALS["config"]["elasticsearch"]["host"] . "/" . $GLOBALS["config"]["elasticsearch"]["index"] . "/" . $GLOBALS["config"]["elasticsearch"]["type"] . "/_bulk";
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type: application/x-ndjson"));
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $bulk_str);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);

    $return = json_decode($response, true);
    return $return;
}


function termElasticDelete($user, $api_key, $ilx) {
    if(!\APIPermissionActions\getUser($api_key, $user)) return "not allowed";
    if (trim($ilx) == ""){
        return "ILX is not specified.";
    }

    global $config;
    $host = $config["elasticsearch"]["protocol"] . '://' . $config['elasticsearch']['host'] . "/" . $config['elasticsearch']['index'] . "/" . $config['elasticsearch']['type'];

    $url = $host . "/" . $ilx;

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL,$url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    //$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $return = json_decode($response);
    return $return;
}

?>
