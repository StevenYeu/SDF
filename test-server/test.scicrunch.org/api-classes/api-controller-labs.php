<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->post($AP."/lab/create", function(Request $request) use($app) {
    require_once __DIR__."/labs.php";
    $portal_name = aR($request->request->get("portalname"), "s");
    $name = aR($request->request->get("name"), "s");
    $description = aR($request->request->get("description"), "s");
    return appReturn($app, createLab($app["config.user"], $app["config.api_key"], $portal_name, $name, $description), true);
});

$app->post($AP."/lab/review", function(Request $request) use($app) {
    require_once __DIR__."/labs.php";
    $labid = aR($request->request->get("labid"), "i");
    $review = aR($request->request->get("review"), "s");
    return appReturn($app, reviewLab($app["config.user"], $app["config.api_key"], $labid, $review), false);
});

$app->post($AP."/lab/join", function(Request $request) use($app) {
    require_once __DIR__."/labs.php";
    $labid = aR($request->request->get("labid"), "i");
    return appReturn($app, joinLab($app["config.user"], $app["config.api_key"], $labid), false);
});

$app->post($AP."/lab/review-user", function(Request $request) use($app) {
    require_once __DIR__."/labs.php";
    $labid = aR($request->request->get("labid"), "i");
    $uid = aR($request->request->get("uid"), "i");
    $review = aR($request->request->get("review"), "s");
    return appReturn($app, reviewUserLab($app["config.user"], $app["config.api_key"], $labid, $uid, $review), false);
});

$app->get($AP."/lab/users", function(Request $request) use($app) {
    require_once __DIR__."/labs.php";
    $labid = $request->query->get("labid");
    return appReturn($app, getLabUsers($app["config.user"], $app["config.api_key"], $labid), false, true);
});

$app->get($AP."/lab/id", function(Request $request) use($app) {
    require_once __DIR__."/labs.php";
    $labname = $request->query->get("labname");
    $portalname = $request->query->get("portalname");
    return appReturn($app, getLabID($app["config.user"], $app["config.api_key"], $labname, $portalname), false);
});

$app->get($AP."/lab/datasets", function(Request $request) use($app) {
    require_once __DIR__."/labs.php";
    $labid = $request->query->get("labid");
    $result = getLabDatasets($app["config.user"], $app["config.api_key"], $labid);
    $new_data = Array();
    if($result->success) {
        foreach($result->data as $d) {
            $new_data[] = $d->arrayForm(true);
        }
    }
    $new_result = APIReturnData::build($new_data, $result->success, $result->status_code, $result->status_msg);
    return appReturn($app, $new_result);
});

$app->get($AP."/lab", function(Request $request) use($app) {
    require_once __DIR__."/labs.php";
    $labid = $request->query->get("labid");
    return appReturn($app, getLabInfo($app["config.user"], $app["config.api_key"], $labid), true);
});

$app->post($AP."/lab/update", function(Request $request) use($app) {
    require_once __DIR__."/labs.php";
    $labid = aR($request->request->get("labid"), "i");
    $name = aR($request->request->get("name"), "s");
    $public_description = aR($request->request->get("public_description"), "s");
    $private_description = aR($request->request->get("private_description"), "s");
    $broadcast_message = aR($request->request->get("broadcast_message"), "s");
    return appReturn($app, editLabInfo($app["config.user"], $app["config.api_key"], $labid, $name, $private_description, $public_description, $broadcast_message), true);
});

?>
