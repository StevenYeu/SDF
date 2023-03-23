<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

$app->post($AP."/usermessages/conversation", function(Request $request) use($app) {
    require_once __DIR__."/user_messages.php";
    $uids = $request->request->get("uids");
    $message = aR($request->request->get("message"), "s");
    $conversation_name = aR($request->request->get("name"), "s");
    $ref_type = $request->request->get("reference_type");
    $ref_id = $request->request->get("reference_id");
    $curator = $request->request->get("curator");

    if(!is_null($curator)) {
        return appReturn($app, createCuratorConversation($app["config.user"], $app["config.api_key"], $ref_type, $ref_id), true);
    } else {
        return appReturn($app, createConversation($app["config.user"], $app["config.api_key"], $uids, $message, $conversation_name, $ref_type, $ref_id), true);
    }
});

$app->post($AP."/usermessages/conversation/{conversation_id}/message", function(Request $request, $conversation_id) use($app) {
    require_once __DIR__."/user_messages.php";
    $message = aR($request->request->get("message"), "s");
    return appReturn($app, createMessage($app["config.user"], $app["config.api_key"], $conversation_id, $message), true);
});

$app->post($AP."/usermessages/conversation/{conversation_id}/user/add", function(Request $request, $conversation_id) use($app) {
    require_once __DIR__."/user_messages.php";
    $uid = $request->request->get("uid");
    return appReturn($app, addRemoveUserConversation($app["config.user"], $app["config.api_key"], "add", $conversation_id, $uid), false);
});

$app->post($AP."/usermessages/conversation/{conversation_id}/user/remove", function(Request $request, $conversation_id) use($app) {
    require_once __DIR__."/user_messages.php";
    $uid = $request->request->get("uid");
    return appReturn($app, addRemoveUserConversation($app["config.user"], $app["config.api_key"], "remove", $conversation_id, $uid), false);
});

$app->post($AP."/usermessages/conversation/{conversation_id}/user/leave", function(Request $request, $conversation_id) use($app) {
    require_once __DIR__."/user_messages.php";
    return appReturn($app, leaveConversation($app["config.user"], $app["config.api_key"], $conversation_id), false);
});

$app->get($AP."/usermessages/conversation/{conversation_id}/messages/new", function(Request $request, $conversation_id) use($app) {
    require_once __DIR__."/user_messages.php";
    $offset = $request->query->get("offset");
    return appReturn($app, getNewMessages($app["config.user"], $app["config.api_key"], $conversation_id, $offset), false, true);
});

$app->get($AP."/usermessages/conversation/{conversation_id}/messages", function(Request $request, $conversation_id) use($app) {
    require_once __DIR__."/user_messages.php";
    $count = $request->query->get("count");
    return appReturn($app, getMessages($app["config.user"], $app["config.api_key"], $conversation_id, $count), false, true);
});

$app->get($AP."/usermessages/conversation", function(Request $request) use($app) {
    require_once __DIR__."/user_messages.php";
    return appReturn($app, getAllConversations($app["config.user"], $app["config.api_key"]), false, true);
});

$app->get($AP."/usermessages/conversationcheck", function(Request $request) use($app) {
    require_once __DIR__."/user_messages.php";
    $ref_table = $request->query->get("reference_type");
    $ref_key = $request->query->get("reference_id");
    $check_self = is_null($request->query->get("checkself")) ? false : true;

    $result = checkExistingConversation($app["config.user"], $app["config.api_key"], $ref_table, $ref_key, $check_self);
    if($result->success) {
        if(!is_null($result->data)) return $app->json($result->data->arrayForm(), $result->status_code);
        return $app->json($result->data, $result->status_code);
    }
    return $app->json($result->status_msg, $result->status_code);
});

$app->get($AP."/usermessages/conversation/{conversation_id}/users", function(Request $request, $conversation_id) use($app) {
    require_once __DIR__."/user_messages.php";
    return appReturn($app, getUsersInConversation($app["config.user"], $app["config.api_key"], $conversation_id));
});

?>
