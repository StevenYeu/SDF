<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->match($AP."/sparc-scigraph/{path}", function(Request $request, $path) use($app) {
    require_once __DIR__ . "/scigraphservices_wrapper_sparc.php";

    $real_path = str_replace("/api/1/sparc-scigraph/", "", $request->getPathInfo());
    if(isset($_SERVER["QUERY_STRING"]) && $_SERVER["QUERY_STRING"]) $query_string = "?" . $_SERVER["QUERY_STRING"];
    else $query_string = "";
    $full_path = $real_path . $query_string;
    $nif_response = getScigraphResponse($app["config.user"], $app["config.api_key"], $full_path);

    if(get_class($nif_response) == "APIReturnData") {
        return appReturn($app, $nif_response);
    } else {
        $response = new Response($nif_response["body"], $nif_response["header"]["http_code"]);
        if(isset($nif_response["header"]["Content-Type"])) $response->headers->set("Content-Type", $nif_response["header"]["Content-Type"]);
        return $response;
    }
})->method("GET|POST")->assert('path', '.+');

$app->get($AP."/scigraph-service/annotations-entities.tsv", function(Request $request) use($app) {
    require_once __DIR__ . "/scigraph-sparc-services.php";

    $terms = $request->query->get("terms");
    if(!$terms) {
        $terms = $request->request->get("terms");
    }

    $response = new Response(getAnnotationEntitiesTSV($terms), 200);
    $response->headers->set("Content-Type", "text/plain");
    return $response;
});

?>
