<?php

    return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
        if(!is_null($api_key)){
            if($api_key->hasPermission("api-moderator")) return true;
        }

        if(!is_null($user)){
            if($user->role > 1) return true;
        }

        if(!is_null($api_user)){
            if($api_user->role > 1) return true;
        }

        return false;
    }

?>
