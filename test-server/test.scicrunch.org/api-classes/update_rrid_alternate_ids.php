<?php

function updateRRIDAlternateIDs($user, $api_key, $rrid, $action, $altid, $active=NULL){
    if(!\APIPermissionActions\checkAction("rrid-update-alt", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);
    if($action == "add"){
        $rrid_map = RRIDMap::createNewObj($cuser->id, $altid, $rrid);
        if(is_null($rrid_map)) return APIReturnData::build(NULL, false, 400, "could not create mapping");
        return APIReturnData::build($rrid_map, true, 201);
    }elseif($action == "active"){
        $rrid_map = RRIDMap::loadBy(Array("issued_rrid", "replace_by"), Array($altid, $rrid));
        if(is_null($rrid_map)) return APIReturnData::build(NULL, false, 400, "rrid mapping does not exist");
        if($active == "true") $active_int = 1;
        elseif($active == "false") $active_int = 0;
        else return APIReturnData::build(NULL, false, 400, "invalid active string");
        $rrid_map->active = $active_int;
        $rrid_map->updateDB();
        return APIReturnData::build($rrid_map, true);
    }
}

?>
