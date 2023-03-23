<?php

return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
    $check_user = is_null($api_user) ? $user : $api_user;
    if(is_null($check_user)) return false;

    $requested_level = $data["level"];
    $affected_lab_membership = $data["lab_membership"];
    $lab = $data["lab"];

    $user_level = LabMembership::getLevel($check_user, $lab);
    $affected_user_level = $affected_lab_membership->level;
    if($requested_level < 0 || $requested_level > 3 || $user_level < 2 || $user_level < $requested_level || is_null($affected_lab_membership) || is_null($lab)) return false;

    /* if user level is above affected user, then action is okay */
    if($user_level > $affected_user_level) return true;

    /* if user themself is demoting from PI to manager, then okay */
    if($user->id == $affected_lab_membership->uid && $user_level === 3 && $requested_level === 2) return true;

    return false;
}

?>
