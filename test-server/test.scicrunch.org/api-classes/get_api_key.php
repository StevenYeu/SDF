<?php

function getAPIKey($user, $api_key, $uid){
    if(!\APIPermissionActions\checkAction("api-key-get", $api_key, $user)) return APIReturnData::quick403();
    $keys = APIKey::loadArrayBy(Array("uid"), Array($uid));
    return APIReturnData::build($keys, true);
}

?>
