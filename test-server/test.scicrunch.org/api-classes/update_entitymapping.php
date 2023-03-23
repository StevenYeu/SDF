<?php

function updateEntityMapping($user, $api_key, $source, $table, $column, $value, $identifier, $updates){
    if(!\APIPermissionActions\checkAction("entitymapping-update", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $entity = entityMapping::loadBy(Array("source", "table_name", "col", "value", "identifier"), Array($source, $table, $column, $value, $identifier));
    if(is_null($entity)) return APIReturnData::quick400("could not find entity mapping");
    foreach($updates as $key => $val){
        if(!is_null($val)){
            $entity->$key = $val;
        }
    }
    $entity->uid_updater = $cuser->id;
    $entity->updateDB();

    return APIReturnData::build($entity, true);
}

?>
