<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

$app->post($AP."/rrid-report/add-item", function(Request $request) use($app) {
    require_once __DIR__."/rrid-report.php";

    $rrid_report_id = aR($request->request->get("id"), "i");
    $type = aR($request->request->get("type"), "s");
    $subtype = aR($request->request->get("subtype"), "s");
    $rrid = aR($request->request->get("rrid"), "s");
    $uuid = aR($request->request->get("uuid"), "s");  ## input uuid value -- Vicky-2019-2-15
    $uid = aR($request->request->get("uid"), "s");  ## input uid value -- Vicky-2019-2-21

    return appReturn($app, addReportItem($app["config.user"], $app["config.api_key"], $rrid_report_id, $type, $subtype, $rrid, $uuid, $uid), true);   ## input uuid value -- Vicky-2019-2-15
});

$app->post($AP."/rrid-report/delete-item", function(Request $request) use($app) {
    require_once __DIR__."/rrid-report.php";

    $rrid_report_id = aR($request->request->get("id"), "i");
    $uuid = aR($request->request->get("uuid"), "s");
    $type = aR($request->request->get("type"), "s");
    $subtype = aR($request->request->get("subtype"), "s");
    $full_delete = $request->request->get("full_delete") ? true : false;

    return appReturn($app, deleteReportItem($app["config.user"], $app["config.api_key"], $rrid_report_id, $uuid, $type, $subtype, $full_delete), false);
});

$app->get($AP."/rrid-report/items/byuuid", function(Request $request) use($app) {
    require_once __DIR__."/rrid-report.php";

    $rrid_report_id = $request->query->get("id");
    $uuid = $request->query->get("uuid");

    $result = getReportItemsByUUID($app["config.user"], $app["config.api_key"], $rrid_report_id, $uuid);
    if(!is_null($result->data)) {
        return appReturn($app, $result, true);
    } else {
        $result->data = false;
        return appReturn($app, $result, false);
    }
});

$app->get($AP."/rrid-report/items", function(Request $request) use($app) {
    require_once __DIR__."/rrid-report.php";

    $rrid_report_id = $request->query->get("id");

    return appReturn($app, getReportItems($app["config.user"], $app["config.api_key"], $rrid_report_id), false, true);
});

$app->post($AP."/rrid-report/new", function(Request $request) use($app) {
    require_once __DIR__."/rrid-report.php";
    $name = aR($request->request->get("name"), "s");
    $description = aR($request->request->get("description"), "s");

    return appReturn($app, newReport($app["config.user"], $app["config.api_key"], $name, $description), true);
});

?>
