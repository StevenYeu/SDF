<?php

function curateResource($user, $api_key, $scrid, $version, $status){
    $rid = \helper\getIDFromRID($scrid);
    $resource = new Resource();
    $resource->getByID($rid);
    if(!$resource->id) return APIReturnData::build(NULL, false, 400, "invalid resource id");
    if(!\APIPermissionActions\checkAction("curate-resource", $api_key, $user, $resource)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    if($status != "Pending" && $status != "Rejected" && $status != "Curated") return APIReturnData::build(NULL, false, 400, "bad status string");
    $resource->updateStatus($status, $version, $cuser->id);

    return APIReturnData::build(true, true);
}

function rejectResource($user, $api_key, $scrid) {
    $rid = \helper\getIDFromRID($scrid);
    $resource = new Resource();
    $resource->getByID($rid);
    if(!$resource->id) return APIReturnData::build(NULL, false, 400, "invalid resource id");
    if(!\APIPermissionActions\checkAction("curate-resource", $api_key, $user, $resource)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $versions = $resource->getVersions();
    foreach($versions as $ver) {
        $resource->updateStatus("Rejected", $ver["version"], $cuser->id);
    }

    return APIReturnData::build(true, true);
}

?>
