<?php
require_once "term_elasticsearch.php";

function addTermAnnotation($user, $api_key, $args){
    $dbObj = new DbObj();
    $api_user = \APIPermissionActions\getUser($api_key, $user);
    if( !$api_user )
        return "not allowed";
    if( !isset($args['tid']) || strlen($args['tid'] ) == 0
        || !isset($args['annotation_tid']) || strlen($args['annotation_tid']) == 0
        || !isset($args['value']) || strlen($args['value']) == 0)
    {
        return "Missing required field(s).";
    }
    // Need ID to proceed since mysql table only has term IDs and does not contain ilx ids
    $term = new Term($dbObj);
    if (is_numeric($args['tid'])) {
        $term->getById($args['tid']);
    } else {
        $term->getByIlx($args['tid']);
    }
    if( !isset($term->id) )
        return "tid given does not exist.";
    $args['tid'] = $term->id;
    $args['term_version'] = $term->version;
    $term = new Term($dbObj);
    if (is_numeric($args['annotation_tid'])) {
        $term->getById($args['annotation_tid']);
    } else {
        $term->getByIlx($args['annotation_tid']);
    }
    if( !isset($term->id) )
        return "annotation_tid given does not exist.";
    $args['annotation_tid'] = $term->id;
    $args['annotation_term_version'] = $term->version;
    $existing = TermAnnotation::exists($dbObj, $args['tid'], $args['annotation_tid'], $args['value']);
    if ( $existing )
        return "Annotation already exists. You can edit it instead.";

    $ta = insertTermAnnotation($dbObj, $args, $user->id);

    $term = new Term($dbObj);
    $term->getById($ta->annotation_tid);
    $annotation_type = $term->label;
    $term->getById($ta->tid);
    $changed_type = "annotation added";
    $changed_des = $term->label . " " . $annotation_type . " " . $ta->value;
    $comment = "";
    $term->insertTermUpdateLog($changed_type, $changed_des, $comment);
    // Clean up data before return
    $return_values = Array();
    foreach( TermAnnotation::$properties as $name ) {
        $return_values[$name] = $ta->$name;
    }
//    termElasticUpsert($user, $api_key, $args['tid']);
//    termElasticUpsert($user, $api_key, $args['annotation_tid']);
    termElasticUpsertBulk($user, $api_key, [$args['tid'], $args['annotation_tid']]);

    return $return_values;
}

function insertTermAnnotation($dbObj, $args, $uid){
    include_once '/assets/plugins/purifier/HTMLPurifier.auto.php';

    $ta = new TermAnnotation($dbObj);
    $ta->orig_uid = $uid;
    $ta->orig_time = time();
    $ta->withdrawn = '0';
    $ta->curator_status = '0';

    $purifier_config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($purifier_config);
    foreach($args as $field => $val) {
        if( in_array($field, TermAnnotation::$properties) ){
            $val = $purifier->purify($val);
            $ta->$field = $val;
        }
    }

    $ta->insertDB();

    return $ta;
}

?>
