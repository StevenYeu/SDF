<?php

function curateTermCommunity($user, $api_key, $id, $fields){
    $dbObj = new DbObj();
    if(!\APIPermissionActions\getUser($api_key, $user)) return "not allowed";

    $tc = new TermCommunity($dbObj);
    $tc->getById($id);
    $tc->updateDB("time_curated", time());

    foreach ($fields as $field => $value) {
        if ( $field !== 'id' && in_array($field, TermCommunity::$properties) ) {
            $tc->updateDB($field, $value);
        }
    }

    return $tc->forPrint();
}

?>
