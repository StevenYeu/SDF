<?php

    return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
        if(!is_null($api_key)){
            if($api_key->hasPermission("ilx")) return true;
            if($api_key->hasPermission("curator")) return true;
            if($api_key->hasPermission("user")) return true;
        }

        $check_user = is_null($api_user) ? $user : $api_user;
        if(is_null($check_user)) return false;

        return true;
    }

?>
