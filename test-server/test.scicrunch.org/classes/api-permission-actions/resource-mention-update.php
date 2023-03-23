<?php

return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL) {
    if(is_null($api_key)) {
        return false; // api key required
    }
    if($api_key->hasPermission("resource-mentions")) return true;
    return false;
}

?>
