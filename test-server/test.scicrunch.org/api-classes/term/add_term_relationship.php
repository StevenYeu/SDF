<?php
require_once "term_elasticsearch.php";
//require_once "term_elastic_upsert.php";

function addTermRelationship($user, $api_key, $args){
    $dbObj = new DbObj();
    if(!\APIPermissionActions\getUser($api_key, $user)) return "not allowed";

    if( !isset($args['term1_id']) || strlen($args['term1_id']) == 0
        || !isset($args['relationship_tid']) || strlen($args['relationship_tid']) == 0
        || !isset($args['term2_id']) || strlen($args['term2_id']) == 0 )
    {
        return "Missing required field(s).";
    }

    // Need ID to proceed since mysql table only has term IDs and does not contain ilx ids
    $term = new Term($dbObj);
    if (is_numeric($args['term1_id'])) {
        $term->getById($args['term1_id']);
    } else {
        $term->getByIlx($args['term1_id']);
    }
    if( !isset($term->id) )
        return "term1_id given does not exist.";
    $args['term1_id'] = $term->id;
    $args['term1_version'] = $term->version;

    $term = new Term($dbObj);
    if (is_numeric($args['relationship_tid'])) {
        $term->getById($args['relationship_tid']);
    } else {
        $term->getByIlx($args['relationship_tid']);
    }
    if( !isset($term->id) )
        return "relationship_tid given does not exist.";
    $args['relationship_tid'] = $term->id;
    $args['relationship_term_version'] = $term->version;

    $term = new Term($dbObj);
    if (is_numeric($args['term2_id'])) {
        $term->getById($args['term2_id']);
    } else {
        $term->getByIlx($args['term2_id']);
    }
    if( !isset($term->id) )
        return "term2_id given does not exist.";
    $args['term2_id'] = $term->id;
    $args['term2_version'] = $term->version;

    $exiting = TermRelationship::exists($dbObj, $args['term1_id'], $args['term2_id'], $args['relationship_tid']);
    if ( $exiting ) {
        return "Relationship already exists. You can edit it instead.";
    }

    # Add to MySQL
    $tr = insertTermRelationship($dbObj, $args, $user->id);

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
    $changed_type = "relationship added";
    $changed_des = $term1->label . " " . $relationship_term->label . " " . $term2->label;
    $comment = "";
    $term1->insertTermUpdateLog($changed_type, $changed_des, $comment);

    // Clean up data before return
    $return_values = Array();
    foreach( TermRelationship::$properties as $name ){
        $return_values[$name] = $tr->$name;
    }
    $return_values['term1_ilx'] = $term1->ilx;
    $return_values['term2_ilx'] = $term2->ilx;
    $return_values['relationship_term_ilx'] = $relationship_term->ilx;

//    $start = round(microtime(true) * 1000);
//    termElasticUpsert($user, $api_key, $args['term1_id']);
//    $return_values['es-time1'] = round(microtime(true) * 1000) - $start;
//    $start = round(microtime(true) * 1000);
//    termElasticUpsert($user, $api_key, $args['term2_id']);
//    $return_values['es-time2'] = round(microtime(true) * 1000) - $start;
//    $start = round(microtime(true) * 1000);
//    termElasticUpsert($user, $api_key, $args['relationship_tid']);
    termElasticUpsertBulk($user, $api_key, [$args['term1_id'], $args['relationship_tid'], $args['term2_id']]);
//    $return_values['es-time3'] = round(microtime(true) * 1000) - $start;

    return $return_values;
}

function insertTermRelationship($dbObj, $args, $uid){

    $tr = new TermRelationship($dbObj);
    $tr->orig_uid = $uid;
    $tr->orig_time = time();
    $tr->withdrawn = '0';
    $tr->curator_status = '0';

    foreach($args as $field => $val) {
        if( in_array($field, TermRelationship::$properties) ){
            $tr->$field = $val;
        }
    }

    $tr->insertDB();

    return $tr;
}

?>
