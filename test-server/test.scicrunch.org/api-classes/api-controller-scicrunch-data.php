<?php

use Symfony\Component\HttpFoundation\Request;

$app->get($AP."/scicrunch-data/types", function(Request $request) use($app) {
    require_once __DIR__."/scicrunch-data.php";
    return appReturn($app, getAllTypes($app["config.user"], $app["config.api_key"]));
});

$app->get($AP."/scicrunch-data/type", function(Request $request) use($app) {
    require_once __DIR__."/scicrunch-data.php";
    $type = $request->query->get("type");
    $query = $request->query->get("q");
    $options = Array(
        "limit" => 20,
        "offset" => 0,
        "order-by" => Array("id", "asc"),
    );
    if(!is_null($request->query->get("count"))) {
        $options["limit"] = min(100, $request->query->get("count"));
    }
    if(!is_null($request->query->get("offset"))) {
        $options["offset"] = $request->query->get("offset");
    }
    if($request->query->get("sort") === "desc") {
        $options["order-by"][1] = "desc";
    }
    if(!is_null($request->query->get("sort-field"))) {
        $options["order-by"][0] = $request->query->get("sort-field");
    }
    $options["order-by"] = implode(" ", $options["order-by"]);
    return appReturn($app, getSingleType($app["config.user"], $app["config.api_key"], $type, $query, $options));
});

?>
