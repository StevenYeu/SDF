<?php

    return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
        $other_key = APIKey::loadByKey($data);
        if(is_null($other_key)) return false;
        if(!is_null($api_key)){
            if($api_key->hasPermission("api-moderator")) return true;
            if($api_key->uid === $other_key->uid) return true;
        }

        if(!is_null($user)){
            if($user->role > 1) return true;
            if($user->id === $other_key->uid) return true;
        }

        if(!is_null($api_user)){
            if($api_user->role > 1) return true;
            if($api_user->id === $other_key->uid) return true;
        }

        return false;
    }

?>
