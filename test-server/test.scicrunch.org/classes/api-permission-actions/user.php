<?php

return function($api, $user, $api_user, $data){
    if(!is_null($api_user) || !is_null($user)) return true;
    return false;
}

?>
