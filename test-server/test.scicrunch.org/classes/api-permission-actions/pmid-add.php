<?php

    return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
        if($data["input_source"] !== ResourceMention::SOURCE_USER && is_null($api_key)) return false; // rdw adding can only be with api key with resource-mentions permission
        if(!is_null($api_key)){
            if($api_key->hasPermission("resource-mentions")) return true;
            if($data["input_source"] === ResourceMention::SOURCE_USER){
                if($api_key->hasPermission("curator")) return true;
                if($api_key->hasPermission("user")) return true;
            }
        }
        if($data["input_source"] !== ResourceMention::SOURCE_USER) return false;  // only api key with resource-mentions permission can add rdw

        $check_user = is_null($api_user) ? $user : $api_user;
        if(is_null($check_user)) return false;
        else return true;
    }

?>
