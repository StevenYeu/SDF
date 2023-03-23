<?php

function ilxAdd($user, $api_key, $term, $defining_url=NULL, $note=NULL, $fragment=NULL){
    if(!\APIPermissionActions\checkAction("ilx-add", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);
    $uid = $cuser->id;

    $existing = IlxIdentifier::checkExisting($term, $uid);
    if(!is_null($existing))
        return APIReturnData::build($existing, true, 200);

    // Needs to be IlxIdentifier object to be called for additional meta.
    $ilx_id = IlxIdentifier::createNewObj($term, $uid, $note, $defining_url, $fragment);
    if(!is_null($ilx_id))
        return APIReturnData::build($ilx_id, true, 201);

    return APIReturnData::quick500("could not generate ILX identifier");
}

?>
