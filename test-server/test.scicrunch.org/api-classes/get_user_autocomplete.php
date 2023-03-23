<?php

function userAutocomplete($user, $api_key, $name){
    if(!\APIPermissionActions\checkAction("search-for-users", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);
    $user_search = new User();
    $found_users = $user_search->findUser($name, 20);
    $results = Array();
    foreach($found_users as $fusr){
        $result = Array(
            "id" => $fusr->id,
            "name" => $fusr->getFullName()
        );
        if($cuser->role > 0) $result["email"] = $fusr->email;
        $results[] = $result;
    }
    return APIReturnData::build($results, true);
}

?>
