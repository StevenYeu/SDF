<?php

function resourceOwners($user, $api_key, $action, $rid, $uid=NULL){
    $permission_check = \APIPermissionActions\checkAction("get-resource-owners", $api_key, $user, $rid);
    if(!$permission_check) return APIReturnData::quick403();
    $resource_id = \helper\getIDFromRID($rid);
    if(is_null($resource_id)) return APIReturnData::build(NULL, false, 400, "cannot find rid");
    if($action === "check") {
        // return APIReturnData::build($permission_check, true);
        ## fixed checking “is_owner” status function -- Vicky-2019-6-25
        $cuser = \APIPermissionActions\getUser($api_key, $user);
        $rel = ResourceUserRelationship::loadBy(Array("uid", "rid", "type"), Array($cuser->id, $resource_id, "owner"));
        $owners = !is_null($rel);
        return APIReturnData::build($owners, true);
    }
    if($action === "get"){
        $owners = ResourceUserRelationship::loadArrayBy(Array("rid", "type"), Array($resource_id, "owner"));
        return APIReturnData::build($owners, true);
    }elseif($action === "add"){
        $owner = ResourceUserRelationship::loadBy(Array("rid", "uid", "type"), Array($resource_id, $uid, "owner"));
        if(!is_null($owner)) return APIReturnData::build($owner, true);
        try{
            $owner = ResourceUserRelationship::createNewObj($resource_id, $uid, "owner");
        }catch(Exception $e){
            $message = $e->getMessage();
            if($message === "invalid uid" || $message === "invalid rid") return APIReturnData::quick400($message);
            else throw $e;
        }
        return APIReturnData::build($owner, true, 201);
    }elseif($action === "del"){
        $owner = ResourceUserRelationship::loadBy(Array("rid", "uid", "type"), Array($resource_id, $uid, "owner"));
        if(!is_null($owner)) ResourceUserRelationship::deleteObj($owner, $user->id);
        return APIReturnData::build(true, true);
    }else{
        throw new Exception("invalid action");
    }
}

?>
