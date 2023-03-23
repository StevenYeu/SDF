<?php

return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
    $check_user = is_null($api_user) ? $user : $api_user;
    if(is_null($check_user)) return false;

    $community = $data["community"];
    $dataset = $data["dataset"];
    if(is_null($dataset) || is_null($community)) return false;

    if($dataset->uid !== $check_user->id) return false;
    if(!isset($check_user->levels[$community->id]) || $check_user->levels[$community->id] < 1) return false;

    return true;
}

?>
