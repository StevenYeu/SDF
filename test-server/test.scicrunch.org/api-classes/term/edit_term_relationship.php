<?php
require_once "term_elasticsearch.php";
//require_once "term_elastic_upsert.php";

function editTermRelationship($user, $api_key, $id, $args){
    $dbObj = new DbObj();
    if(!\APIPermissionActions\getUser($api_key, $user)) return "not allowed";

    if(!isset($id) || sizeof($id) == 0
        || !isset($args['term1_id']) || strlen($args['term1_id']) == 0
        || !isset($args['term2_id']) || strlen($args['term2_id']) == 0
        || !isset($args['relationship_tid']) || strlen($args['relationship_tid']) == 0
        || !isset($args['term1_version']) || strlen($args['term1_version']) == 0
        || !isset($args['term2_version']) || strlen($args['term2_version']) == 0
        || !isset($args['relationship_term_version']) || strlen($args['relationship_term_version']) == 0)
    {
            return array("errormsg"=>"Missing required field(s).");
    }

    $tr = new TermRelationship($dbObj);
    $tr->getById($id);
    $changed_type = "relationship modified";
    $changed_des = "";
    $comment = "";
    foreach($args as $field => $val){
        if(in_array($field, TermRelationship::$properties)){
            if ($tr->$field != "$val"){
                $tr->$field = $val;
                $tr->updateDB($field, $val);
                if($field == "withdrawn" && $val == 1) $changed_type = "relationship withdrawn";
            }
        }
    }

    # TERM 1 meta
    $term1 = new Term($dbObj);
    $term1->getById($tr->term1_id);
    # RELATIONSHIP term meta
    $relationship_term = new Term($dbObj);
    $relationship_term->getById($tr->relationship_tid);
    # TERM 2 meta
    $term2 = new Term($dbObj);
    $term2->getById($tr->term2_id);

    # LOG
    $changed_type = "relationship edited";
    $changed_des = $term1->label . " " . $relationship_term->label . " " . $term2->label;
    $comment = "";
    $term1->insertTermUpdateLog($changed_type, $changed_des, $comment);

    //clean up data before return
    $return_values = Array();
    foreach(TermRelationship::$properties as $name){
        $return_values[$name] = $tr->$name;
    }
    termElasticUpsertBulk($user, $api_key, [$args['term1_id'], $args['relationship_tid'], $args['term2_id']]);

    return $return_values;
}

?>
