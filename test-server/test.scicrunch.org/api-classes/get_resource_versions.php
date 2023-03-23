<?php

function getResourceVersions($user, $api_key, $scrid){
    $cuser = \APIPermissionActions\getUser($api_key, $user);
    $rid = \helper\getIDFromRID($scrid);
    $resource = new Resource();
    $resource->getByID($rid);
    $is_owner = is_null($cuser) ? false : $resource->isAuthorizedOwner($cuser->id);
    $versions = $resource->getVersions();
    $versions_reshaped = Array();
    foreach($versions as $ver){
        $ver_user = new User();
        $ver_user->getByID($ver['uid']);
        if($ver_user->id){
            $username = $ver_user->getFullName();
        }else{
            $username = "anonymous";
        }
        $next_version = Array(
            "version" => $ver['version'],
            "status" => $ver['status'],
            "time" => $ver['time'],
            "uid" => $ver['uid'],
            "username" => $username,
            "cid" => $ver['cid']
        );
        if($is_owner && $ver["last_curator"]) {
            $version_owner = new User();
            $version_owner->getByID($ver["last_curator"]);
            $next_version["curated_by"] = $version_owner->getFullName();
        }
        $versions_reshaped[] = $next_version;
    }
    return APIReturnData::build($versions_reshaped, true);
}

?>
