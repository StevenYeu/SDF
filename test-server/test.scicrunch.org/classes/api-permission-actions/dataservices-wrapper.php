<?php

    return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
        /* if using API key, make sure user has dataservices or user permission */
        if(!is_null($api_key)){
            if($api_key->hasPermission("dataservices") || $api_key->hasPermission("user")) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

?>
