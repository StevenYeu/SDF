<?php

    return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
        if(!is_null($api_key)){
            if($api_key->hasPermission("resource-adder")) return true;
        }

        $check_user = is_null($api_user) ? $user : $api_user;
        if(is_null($check_user)) return false;

        if($check_user->role > 0) return true;

        return false;
    }

?>
