<?php

function addAPIKey($user, $api_key, $uid, $description=NULL, $project_name=NULL){
    if(!\APIPermissionActions\checkAction("api-key-add", $api_key, $user)) return APIReturnData::quick403();
    $api_user = new User();
    $api_user->getByID($uid);
    if(!$api_user->id) return APIReturnData::build("bad uid", false);

    $new_key = APIKey::createNewObj($api_user->id, 0, 0 ,1, $project_name, $description);
    $new_key->addPermission("user");

    return APIReturnData::build($new_key, true, 201);
}

function enableDisableAPIKey($user, $api_key, $update_key, $action){
    if($action !== "enable" && $action !== "disable") throw new Exception("invalid key action");
    if(!\APIPermissionActions\checkAction("api-key-enable-disable", $api_key, $user)) return APIReturnData::quick403();
    $key_obj = APIKey::loadByKey($update_key);
    if(is_null($key_obj)) return APIReturnData::build(NULL, false, 400, "bad key");
    $active = $action === "enable" ? 1 : 0;
    $key_obj->active = $active;
    $key_obj->updateDB();
    return APIReturnData::build($key_obj, true);
}

function keyUpdate($user, $api_key, $update_key, $description=NULL, $project_name=NULL){
    if(!\APIPermissionActions\checkAction("api-key-update", $api_key, $user, $update_key)) return APIReturnData::quick403();
    $key_obj = APIKey::loadByKey($update_key);
    if(!is_null($description)) $key_obj->description = $description;
    if(!is_null($project_name)) $key_obj->project_name = $project_name;
    $key_obj->updateDB();
    return APIReturnData::build($key_obj, true);
}

?>
