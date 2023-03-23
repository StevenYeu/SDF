<?php
require_once "term_elasticsearch.php";
//require_once "term_elastic_upsert.php";

function editTermAnnotation($user, $api_key, $id, $args, $add2elastic='1'){
    $dbObj = new DbObj();
    $api_user = \APIPermissionActions\getUser($api_key, $user);
    if(!$api_user) return "not allowed";

    if(!isset($id) || strlen($id) == 0
        || !isset($args['tid']) || strlen($args['tid']) == 0
        || !isset($args['annotation_tid']) || strlen($args['annotation_tid']) == 0
        || !isset($args['term_version']) || strlen($args['term_version']) == 0
        || !isset($args['annotation_term_version']) || strlen($args['annotation_term_version']) == 0
        || !isset($args['value']) || strlen($args['value']) == 0)
    {
            return array("errormsg"=>"Missing required field(s).");
    }

    if($args["batch-elastic"]) {
        $add2elastic = false;
    }

    $ta = new TermAnnotation($dbObj);
    $ta->getById($id);
    $changed_type = "annotation modified";
    $changed_des = "";
    $comment = "";
    foreach($args as $field => $val){
        if(in_array($field, TermAnnotation::$properties)){
            if ($ta->$field != "$val"){
                $ta->$field = $val;
                $ta->updateDB($field, $val);
                if($field == "withdrawn" && $val == 1) $changed_type = "annotation withdrawn";
            }
        }
    }
    $term = new Term($dbObj);
    $term->getById($ta->annotation_tid);
    $annotation_type = $term->label;
    $term->getById($ta->tid);
    $changed_des = $term->label . " " . $annotation_type . " " . $ta->value;
    $term->insertTermUpdateLog($changed_type, $changed_des, $comment);

    //clean up data before return
    $return_values = Array();
    foreach(TermAnnotation::$properties as $name){
        $return_values[$name] = $ta->$name;
    }

    termElasticUpsertBulk($user, $api_key, [$args['tid'], $args['annotation_tid']]);
//    if ($add2elastic == '1'){
//        termElasticUpsert($user, $api_key, $args['tid']);
//        termElasticUpsert($user, $api_key, $args['annotation_tid']);
//    } else {
//        $term = $ta->term();
//        $term_annotation = $ta->termAnnotation();
//        if(!is_null($term) && !is_null($term_annotation)) {
//            TermFlag::createNewObj($term, $api_user, "elastic-upsert");
//            TermFlag::createNewObj($term_annotation, $api_user, "elastic-upsert");
//        }
//    }

    return $return_values;
}

?>
