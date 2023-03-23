<?php

function createConversation($user, $api_key, $uids, $message, $conversation_name, $ref_type, $ref_id) {
    if(!\APIPermissionActions\checkAction("conversation-create", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    // create users
    $users = Array();
    foreach($uids as $uid) {
        $user = new User();
        $user->getByID($uid);
        if($user->id) $users[] = $user;
    }

    // make conversation
    $conversation = UserMessageConversation::newConversation($users, $cuser, $message, $conversation_name, $ref_type, $ref_id);

    if(is_null($conversation)) return APIReturnData::quick400("could not create conversation");
    return APIReturnData::build($conversation, true, 201);
}

function createCuratorConversation($user, $api_key, $ref_type, $ref_id) {
    if(!\APIPermissionActions\checkAction("conversation-create-curator", $api_key, $user, Array("ref_type" => $ref_type, "ref_id" => $ref_id))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $existing = UserMessageConversation::loadBy(Array("foreign_table", "foreign_key"), Array($ref_type, $ref_id));
    if(!is_null($existing)){
        $existing->addUser($cuser, 0);
        return APIReturnData::build($existing, true, 200);
    }

    $conversation = UserMessageConversation::newCuratorConversation($cuser, $ref_type, $ref_id);

    return APIReturnData::build($conversation, true, 201);
}

function createMessage($user, $api_key, $conversation_id, $message_string) {
    if(!\APIPermissionActions\checkAction("conversation-message", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    // get conversation
    $conversation = UserMessageConversation::loadBy(Array("id"), Array($conversation_id));
    if(is_null($conversation)) return APIReturnData::quick400("could not find conversation");

    // send the message
    $message = $conversation->sendMessage($message_string, $cuser);
    return APIReturnData::build($message, true, 201);
}

function addRemoveUserConversation($user, $api_key, $action, $conversation_id, $uid) {
    if(!\APIPermissionActions\checkAction("conversation-add-remove-user", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    // make sure it's a valid action
    if($action !== "add" && $action !== "remove") return APIReturnData::quick400("invalid action");

    // get the user
    $user = new User();
    $user->getByID($uid);
    if(!$user->id) return APIReturnData::quick400("invalid uid");

    // get the conversation
    $conversation = UserMessageConversation::loadBy(Array("id"), Array($conversation_id));
    if(is_null($conversation)) return APIReturnData::quick400("invalid conversation id");

    if($action === "add") { // add the user
        $conversation->addUser($user, 1);   // new flag = 1
        return APIReturnData::build(true, true, 201);
    } else {    // or remove them
        $conversation->removeUser($user);
        return APIReturnData::build(true, true, 200);
    }
}

function leaveConversation($user, $api_key, $conversation_id) {
    if(!\APIPermissionActions\checkAction("conversation-leave", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    // get the conversation
    $conversation = UserMessageConversation::loadBy(Array("id"), Array($conversation_id));
    if(is_null($conversation)) return APIReturnData::quick400("invalid conversation id");

    $conversation->removeUser($cuser);
    return APIReturnData::build(true, true, 200);
}

function getNewMessages($user, $api_key, $conversation_id, $offset) {
    if(!\APIPermissionActions\checkAction("conversation-get-messages", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $conversation_user = UserMessageConversationUser::loadBy(Array("uid", "conversation_id"), Array($cuser->id, $conversation_id));
    if(is_null($conversation_user)) return APIReturnData::quick400("user not in conversation");

    $messages = $conversation_user->loadNewMessages($offset);
    return APIReturnData::build($messages, true, 200);
}

function getMessages($user, $api_key, $conversation_id, $count) {
    if(!\APIPermissionActions\checkAction("conversation-get-messages", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $messages = UserMessage::loadArrayBy(Array("conversation_id"), Array($conversation_id), false, $count);
    return APIReturnData::build($messages, true, 200);
}

function getAllConversations($user, $api_key) {
    if(!\APIPermissionActions\checkAction("conversation-get-conversations", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $conversations_user = UserMessageConversationUser::loadArrayBy(Array("uid"), Array($cuser->id));

    return APIReturnData::build($conversations_user, true, 200);
}

function checkExistingConversation($user, $api_key, $ref_table, $ref_key, $check_self) {
    if(!\APIPermissionActions\checkAction("conversation-check-existing", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $conversation = UserMessageConversation::loadBy(Array("foreign_table", "foreign_key"), Array($ref_table, $ref_key));

    // if check self flag, check if user is member of existing conversation else return null
    if($check_self & !is_null($conversation)) {
        $user_check = UserMessageConversationUser::loadBy(Array("uid", "conversation_id"), Array($cuser->id, $conversation->id));
        if(is_null($user_check)) $conversation = NULL;
    }

    return APIReturnData::build($conversation, true);
}

function getUsersInConversation($user, $api_key, $conversation_id) {
    if(!\APIPermissionActions\checkAction("conversation-get-users", $api_key, $user, Array("conversation_id" => $conversation_id))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $converation_users = UserMessageConversationUser::loadArrayBy(Array("conversation_id"), Array($conversation_id));

    $users = Array();
    foreach($converation_users as $conv_user) {
        $add_user = new User();
        $add_user->getByID($conv_user->uid);
        if($add_user->id) {
            $users[] = Array("id" => $add_user->id, "name" => $add_user->getFullName());
        }
    }

    return APIReturnData::build($users, true);
}

?>
