<?php

return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
    $rid = \helper\getIDFromRID($data);
    $resource = new Resource();
    $resource->getByID($rid);
    if(!$resource->id) return false;

    if(!is_null($api_key)){
        if($api_key->hasPermission("curator")) return true;
    }

    $check_user = is_null($api_user) ? $user : $api_user;
    if(is_null($check_user)) return false;
    if($check_user->role > 0) return true;
    if($resource->isAuthorizedOwner($check_user->id)) return true;

    return false;
}

?>
