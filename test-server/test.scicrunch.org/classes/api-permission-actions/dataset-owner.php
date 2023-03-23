<?php

return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
    $check_user = is_null($api_user) ? $user : $api_user;
    if(is_null($check_user)) return false;
    $dataset = $data["dataset"];

    if($dataset->canEdit($check_user)) return true;

    return false;
}

?>
