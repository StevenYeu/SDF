<?php

    return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
        // just needs to have user permission
        $check_user = is_null($api_user) ? $user : $api_user;
        if(is_null($check_user)) return false;
        else return true;
    }

?>
