<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

$app->post($AP."/data/dataservices/notify", function(Request $request) use($app) {  // done
    require_once __DIR__."/notify_from_data_services.php";
    $id = aR($request->request->get("id"), "s");
    $message = aR($request->request->get("message"), "s");
    $host = aR($request->request->get("host"), "s");
    if(is_null($id) || is_null($message) || is_null($host)) return $app->json("id and message and host are required", 400);

    return appReturn($app, notifyFromDataServices($user, $api_key, $id, $message, $host));
});

// disable for now
//$app->post($AP."/data/external_registry/notify/{rid}", function(Request $request, $rid) use($app) {
//
//});

?>
