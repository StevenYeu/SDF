<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 *
 *  @SWG\Definition(
 *      definition="elasticIndices",
 *      type="object",
 *      @SWG\Property(
 *          property="data",
 *          type="array",
 *          @SWG\Items(
 *              @SWG\Property(property="health", type="string"),
 *              @SWG\Property(property="status", type="string"),
 *              @SWG\Property(property="index", type="string"),
 *              @SWG\Property(property="docs.count", type="string"),
 *              @SWG\Property(property="pri.store.size", type="string"),
 *          ),
 *      ),
 *      @SWG\Property(property="success", type="boolean"),
 *
 *
 *  )
 */

/**
 *  @SWG\Get(path="/elastic/_indices", summary="Get a list of all indices", tags={"Elastic"},
 *      @SWG\Response(response="200", ref="#/definitions/elasticIndices"),
 *  )
 **/

/* elastic non-wrapper services */
$app->get($AP."/elasticservices/{viewid}/search", function(Request $request, $viewid) use($app) {
    require_once __DIR__ . "/elasticsearch_services.php";
    $query = $request->query->get("q");
    $page = $request->query->get("page");
    $per_page = $request->query->get("per_page");
    $sort = $request->query->get("sort");
    $column = $request->query->get("column");
    $filters = $request->query->get("filter");
    return appReturn($app, searchByViewID($app["config.user"], $app["config.api_key"], $viewid, $query, $page, $per_page, $sort, $column, $filters));
});

/* elastic wrapper endpoints */
$app->match($AP."/{elastic_type}/_indices", function(Request $request, $elastic_type) use($app) {
    require_once __DIR__ . "/elasticsearch_wrapper.php";
    $res = getIndices($app["config.user"], $app["config.api_key"], $request->getMethod(), getElasticType($elastic_type));
    return appReturn($app, $res);
})->method("GET|POST")->assert("elastic_type", "^(elastic|elastic-ilx)$");

$app->match($AP."/{elastic_type}/{action}", function(Request $request, $elastic_type, $action) use($app) {
    require_once __DIR__ . "/elasticsearch_wrapper.php";
    $post_string = file_get_contents("php://input");
    $res = allSearch($app["config.user"], $app["config.api_key"], $action, $request->getMethod(), $request->query->all(), $post_string, getElasticType($elastic_type));
    return elasticReturn($app, $res);
})->method("GET|POST")->assert("elastic_type", "^(elastic|elastic-ilx)$")->assert("action", "^(_search|_count|_mappings)$");

$app->match($AP."/{elastic_type}/{index}/{action}", function(Request $request, $elastic_type, $index, $action) use($app) {
    require_once __DIR__ . "/elasticsearch_wrapper.php";
    $post_string = file_get_contents("php://input");
    $res = indexSearch($app["config.user"], $app["config.api_key"], $index, $action, $request->getMethod(), $request->query->all(), $post_string, getElasticType($elastic_type));
    return elasticReturn($app, $res);
})->method("GET|POST")->assert("elastic_type", "^(elastic|elastic-ilx)$")->assert("index", "^[^_.][^/]*")->assert("action", "^(_search|_count|_validate|_settings|_mappings)$");

$app->match($AP."/{elastic_type}/{index}", function(Request $request, $elastic_type, $index) use($app) {
    require_once __DIR__ . "/elasticsearch_wrapper.php";
    $post_string = file_get_contents("php://input");
    $res = indexOverview($app["config.user"], $app["config.api_key"], $index, $request->getMethod(), $request->query->all(), $post_string, getElasticType($elastic_type));
    return elasticReturn($app, $res);
})->method("GET|POST")->assert("elastic_type", "^(elastic|elastic-ilx)$")->assert("index", "^[^_.][^/]*");

$app->match($AP."/{elastic_type}/{index}/{type}/{action}", function(Request $request, $elastic_type, $index, $type, $action) use($app) {
    require_once __DIR__ . "/elasticsearch_wrapper.php";
    $post_string = file_get_contents("php://input");
    $res = typeSearch($app["config.user"], $app["config.api_key"], $index, $type, $action, $request->getMethod(), $request->query->all(), $post_string, getElasticType($elastic_type));
    return elasticReturn($app, $res);
})->method("GET|POST")->assert("elastic_type", "^(elastic|elastic-ilx)$")->assert("index", "^[^_.][^/]*")->assert("type", "^[^_.][^/]*")->assert("action", "^(_search|_count|_mappings)$");


/**********************************/
/*** COPIES WITH TRAILING SLASH ***/
/**********************************/

$app->match($AP."/{elastic_type}/_indices/", function(Request $request, $elastic_type) use($app) {
    return $app->handle(duplicateElasticRequest($request), HttpKernelInterface::SUB_REQUEST, false);
})->method("GET|POST")->assert("elastic_type", "^(elastic|elastic-ilx)$");

$app->match($AP."/{elastic_type}/{action}/", function(Request $request, $elastic_type, $action) use($app) {
    return $app->handle(duplicateElasticRequest($request), HttpKernelInterface::SUB_REQUEST, false);
})->method("GET|POST")->assert("elastic_type", "^(elastic|elastic-ilx)$")->assert("action", "^(_search|_count|_mappings)$");

$app->match($AP."/{elastic_type}/{index}/{action}/", function(Request $request, $elastic_type, $index, $action) use($app) {
    return $app->handle(duplicateElasticRequest($request), HttpKernelInterface::SUB_REQUEST, false);
})->method("GET|POST")->assert("elastic_type", "^(elastic|elastic-ilx)$")->assert("index", "^[^_.][^/]*")->assert("action", "^(_search|_count|_validate|_settings|_mappings)$");

$app->match($AP."/{elastic_type}/{index}/", function(Request $request, $elastic_type, $index) use($app) {
    return $app->handle(duplicateElasticRequest($request), HttpKernelInterface::SUB_REQUEST, false);
})->method("GET|POST")->assert("elastic_type", "^(elastic|elastic-ilx)$")->assert("index", "^[^_.][^/]*");

$app->match($AP."/{elastic_type}/{index}/{type}/{action}/", function(Request $request, $elastic_type, $index, $type, $action) use($app) {
    return $app->handle(duplicateElasticRequest($request), HttpKernelInterface::SUB_REQUEST, false);
})->method("GET|POST")->assert("elastic_type", "^(elastic|elastic-ilx)$")->assert("index", "^[^_.][^/]*")->assert("type", "^[^_.][^/]*")->assert("action", "^(_search|_count|_mappings)$");

function duplicateElasticRequest($request) {
    $subRequest = Request::create(
        $request->getBasePath() . rtrim($request->getPathInfo(), "/"),
        $request->getMethod(),
        $request->query->all(),
        $request->cookies->all(),
        $request->files->all(),
        $request->server->all(),
        $request->getContent()
    );
    return $subRequest;
}

function elasticReturn($app, $res) {
    $response = new Response($res->data["body"], $res->status_code);
    if(isset($res->data["header"]["Content-Type"])) $response->headers->set("Content-Type", $res->data["header"]["Content-Type"]);
    return $response;
}

function getElasticType($elastic_url_type) {
    if($elastic_url_type == "elastic-ilx") {
        return "ilx";
    }
    return "normal";
}

?>
