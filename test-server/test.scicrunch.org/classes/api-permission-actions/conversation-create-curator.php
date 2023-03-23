<?php

    return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
        if(!is_null($api_key)){
            if($api_key->hasPermission("curator")) return true;
        }

        $check_user = is_null($api_user) ? $user : $api_user;
        if(is_null($check_user)) return false;
        if($check_user->role > 0) return true;

        $ref_type = $data["ref_type"];
        $ref_id = $data["ref_id"];
        if($ref_type == UserMessageConversation::TABLE_RESOURCES) {
            $resource = new Resource();
            $resource->getByID($ref_id);
            if(!$resource->id) return false;
            if($resource->isAuthorizedOwner($check_user->id)) return true;
        }

        return false;
    }

?>
