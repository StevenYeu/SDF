<?php

function curateEntityMapping($user, $api_key, $source, $table, $column, $value, $identifier, $curation_status){
    if(!\APIPermissionActions\checkAction("entitymapping-curate", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $entity_mapping = EntityMapping::loadBy(Array("source", "table_name", "col", "value", "identifier"), Array($source, $table, $column, $value, $identifier));
    if(is_null($entity_mapping)) return APIReturnData::quick400("could not find entity mapping");
    $entity_mapping->curation_status = $curation_status;
    $entity_mapping->updateDB();

    return APIReturnData::build($entity_mapping, true);
}

?>
