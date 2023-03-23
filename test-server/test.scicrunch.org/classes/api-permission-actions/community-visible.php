<?php

    return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
        $check_user = is_null($api_user) ? $user : $api_user;
        $community = $data;
        $visible = $community->isVisible($check_user);

        return $visible;
    }

?>
