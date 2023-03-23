<?php

    return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
        if(is_null($user) && is_null($api_user)) return false;
        $check_user = is_null($api_user) ? $user : $api_user;
        if($check_user->role === 0 && !Resource::isOwnerOfAnyResource($check_user->id)) return false;
        return true;
    }

?>
