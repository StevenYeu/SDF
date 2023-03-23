<?php

function addEntityMapping($user, $api_key, $source, $table, $column, $value, $identifier, $external_id, $relation, $match_substring, $status){
    if(!\APIPermissionActions\checkAction("entitymapping-add", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    try{
        $entity_mapping = EntityMapping::createNewObj($source, $table, $column, $value, $identifier, $relation, $cuser->id, $external_id, $match_substring, $status);
    }catch(Exception $e){
        $message = $e->getMessage();
        if($message === "entity mapping already exists"){
            $ent = EntityMapping::loadBy(Array("source", "table_name", "col", "value", "identifier"), Array($source, $table, $column, $value, $identifier));
            return APIReturnData::build($ent, true);
        }elseif($message === "curated mapping already exists for this value" || $message === "invalid source id"){
            return APIReturnData::quick400($message);
        }else{
            throw $e;
        }
    }
    if(is_null($entity_mapping)) return APIReturnData::build(NULL, false, 400, "could not create entity mapping");
    return APIReturnData::build($entity_mapping, true, 201);
}

?>
