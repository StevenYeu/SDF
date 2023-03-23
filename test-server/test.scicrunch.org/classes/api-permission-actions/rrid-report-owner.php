<?php

    return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
        $check_user = is_null($api_user) ? $user : $api_user;
        if(is_null($check_user)) return false;

        $rrid_report = $data["rrid-report"];
        if(is_null($rrid_report)) return false;

        if($rrid_report->uid === $check_user->id) return true;

        return false;
    }

?>
