<?php

return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
    $check_user = is_null($api_user) ? $user : $api_user;
    if(is_null($check_user)) return false;
    $lab = $data["lab"];
    if(is_null($lab)) return false;
    $level = LabMembership::getLevel($check_user, $lab);
    if($level !== false && $level > 1) return true;

    return false;
}

?>
