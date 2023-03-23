<?php

use Swagger\Annoataions as SWG;
use Symfony\Component\HttpFoundation\Request;

$app->post($AP."/systemmessages/add", function(Request $request) use($app) {
    require_once __DIR__."/system_messages.php";

    $start_time = aR($request->request->get("start_time"), "i");
    $end_time = aR($request->request->get("end_time"), "i");
    $redirect = aR($request->request->get("redirect"), "s");
    $message = aR($request->request->get("message"), "s");
    $type = aR($request->request->get("type"), "s");
    $cid = aR($request->request->get("cid"), "i");

    return appReturn($app, addSystemMessage($app["config.user"], $app["config.api_key"], $message, $cid, $type, $redirect, $start_time, $end_time), true);

});

$app->post($AP."/systemmessages/delete", function(Request $request) use($app) {
    require_once __DIR__."/system_messages.php";

    $id = $request->request->get("id");

    return appReturn($app, deleteSystemMessage($app["config.user"], $app["config.api_key"], $id));
});

$app->get($AP."/systemmessages", function(Request $request) use($app) {
    require_once __DIR__."/system_messages.php";

    return appReturn($app, listSystemMessages($app["config.user"], $app["config.api_key"]), false, true);
});

?>
