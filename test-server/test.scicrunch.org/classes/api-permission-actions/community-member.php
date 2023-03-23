<?php

return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
    $community = $data["community"];

    $check_user = is_null($api_user) ? $user : $api_user;
    if(is_null($check_user)) return false;
    if(!isset($check_user->levels[$community->id])) return false;
    if($check_user->levels[$community->id] > 0) return true;

    return false;
}

?>
