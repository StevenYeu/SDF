<?php

return function($api_key = NULL, $user = NULL, $api_user = NULL, $data = NULL) {
    if(!is_null($api_key) && $api_key->hasPermission("term")) {
        return true;
    }

    $check_user = is_null($api_user) ? $user : $api_user;
    if($check_user->role > 0) {
        return true;
    }

    return false;
}

?>
