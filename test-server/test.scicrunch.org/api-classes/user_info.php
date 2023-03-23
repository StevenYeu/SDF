<?php

function userInfo($user, $api_key, $lookup=NULL, $no_datasets=false, $no_labs=false){
    $return_array = Array();
    if(is_null($lookup)){
        $search_user = \APIPermissionActions\getUser($api_key, $user);
        if(is_null($search_user)) return APIReturnData::build(Array("logged_in" => false), true);
        $return_array["logged_in"] = true;
    }else{
        if(!\APIPermissionActions\checkAction("user-info", $api_key, $user)) return APIReturnData::quick403();
        $search_user = new User();
        $search_user->getByID($lookup);
        if(is_null($search_user)) return APIReturnData::build(NULL, false, 400, "bad_user");
    }
    $return_array["first_name"] = $search_user->firstname;
    $return_array["last_name"] = $search_user->lastname;
    $return_array["role"] = (int) $search_user->role;
    $return_array["communities"] = getCommunities($search_user);
    if(!$no_labs) {
        $return_array["labs"] = getLabs($search_user);
    }
    if(!$no_datasets) {
        $return_array["datasets"] = getDatasets($search_user);
    }
    $return_array["id"] = (int) $search_user->id;
    return APIReturnData::build($return_array, true);
}

function getCommunities($user) {
    $return_array = Array();
    foreach($user->levels as $cid => $level) {
        if($level == 0) continue;
        $community = new Community();
        $community->getByID($cid);
        $return_array[] = Array("portalName" => $community->portalName, "cid" => $cid, "level" => $level);
    }
    return $return_array;
}

function getDatasets($user) {
    $return_array = Array();
    $datasets = Dataset::loadArrayBy(Array("uid"), Array($user->id));
    foreach($datasets as $dataset) {
        $return_array[] = $dataset->arrayForm($no_template_fields=True);
    }
    return $return_array;
}

function getLabs($user) {
    $return_array = Array();
    $userClass = new User();
    $labs = $userClass->getLabs($user->id);
    return $labs;
}
?>
