<?php

function addSystemMessage($user, $api_key, $message, $cid, $type, $redirect, $start_time, $end_time){
    if(!\APIPermissionActions\checkAction("update-system-message", $api_key, $user, Array("cid" => $cid))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $system_message = SystemMessage::createNewObj($message, $cuser->id, $cid, $start_time, $end_time, $type);
    if(is_null($system_message)) return APIReturnData::quick400("could not create system message");

    return APIReturnData::build($system_message, true, 201);
}

function deleteSystemMessage($user, $api_key, $id) {
    $system_message = SystemMessage::loadBy(Array("id"), Array($id));
    if(is_null($system_message)) return APIReturnData::quick400("could not find system message");

    if(!\APIPermissionActions\checkAction("update-system-message", $api_key, $user, Array("cid" => $system_message->cid))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $system_message->active = 0;
    $system_message->updateDB();

    return APIReturnData::build(true, true, 200);
}

function listSystemMessages($user, $api_key) {
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $system_messages = Array();
    if($cuser->role > 0) {
        // if curator get everything
        $system_messages = SystemMessage::getNonExpiredMessages();
    } else {
        // if not curator, only get for communities they are admin of
        foreach($cuser->levels as $cid => $level) {
            if($level > 1) {
                $comm_messages = SystemMessage::getNonExpiredMessages($cid);
                $system_messages = array_merge($system_messages, $comm_messages);
            }
        }
    }
    return APIReturnData::build($system_messages, true, 200);
}

?>
