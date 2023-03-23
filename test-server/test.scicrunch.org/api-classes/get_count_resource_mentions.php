<?php

function getCountResourceMentions($user, $api_key, $scrid, $confidence){
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $rid = \helper\getIDFromRID($scrid);
    $resource = new Resource();
    $resource->getByID($rid);
    $is_authorized_owner = is_null($cuser) ? false : $resource->isAuthorizedOwner($cuser->id);
    if(!$rid) return APIReturnData::build(NULL, false, 400, "invalid resource id");
    if(is_null($confidence)) $confidence = "low";
    $count = ResourceMention::getCountByRID($rid, $confidence, $is_authorized_owner);
    return APIReturnData::build($count, 200);
}

?>
