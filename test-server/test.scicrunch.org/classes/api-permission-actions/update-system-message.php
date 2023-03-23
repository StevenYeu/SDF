<?php

    return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
        $check_user = is_null($api_user) ? $user : $api_user;
        if(is_null($check_user)) return false;

        if(is_null($data["cid"])) return false;

        if($check_user->role > 0) return true;
        if($data["cid"] !== -1 && isset($check_user->levels[$data["cid"]]) && $check_user->levels[$data["cid"]] > 1) {
            return true;
        }

        return false;
    }

?>
