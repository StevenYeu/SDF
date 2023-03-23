<?php

function changeResourceType($user, $api_key, $rid, $type_id){
    if(!\APIPermissionActions\checkAction("change-resource-type", $api_key, $user)) return APIReturnData::quick403();

    $resource = new Resource();
    $resource->getByRID($rid);
    $resource->updateTypeID($type_id);
    return APIReturnData::build(true, true);
}

?>
