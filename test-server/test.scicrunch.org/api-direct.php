<?php

/****************************************************************************************************
main entry point for api
****************************************************************************************************/

require_once __DIR__."/classes/classes.php";

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

try{
    \helper\scicrunch_session_start();

    /*
    if user is coming from the website (not using api directly), then they do not need an API key for some functions
    web flag gets set to true in session
    */
    $web_set = isset($_SESSION["web"]);

    $AP = "/api/1";

    $app = new Silex\Application();
    $app['debug'] = false;

    $app->before(function(Request $request) use($app, $web_set) {
        $app["config.user"] = isset($_SESSION["user"]) ? $_SESSION["user"] : NULL;

        /* if content-type is application/json, json_decode it */
        if (0 === strpos($request->headers->get('Content-Type'), 'application/json')) {
            $data = json_decode($request->getContent(), true);
            $request->request->replace(is_array($data) ? $data : array());
        }

        /* get the api key string if set */
        $app["config.api_key"] = NULL;
        $key = filter_var($request->headers->get("X-API-KEY"), FILTER_SANITIZE_STRING);
        if(is_null($key) || $key === "") $key = filter_var($request->request->get("key"), FILTER_SANITIZE_STRING);
        if(is_null($key) || $key === "") $key = filter_var($request->query->get("key"), FILTER_SANITIZE_STRING);
        if(is_null($key) || $key === "") $key = filter_var($request->request->get("api_key"), FILTER_SANITIZE_STRING);
        if(is_null($key) || $key === "") $key = filter_var($request->query->get("api_key"), FILTER_SANITIZE_STRING);

        if($key == "SCICRUNCH-DATA-SERVICES" && \helper\startsWith($request->server->get("REQUEST_URI"), "/api/1/dataservices")) {
            /* special key for requesting dataservices through API */
            APIKeyLog::createNewObj(NULL, NULL, \helper\getIP($_SERVER), "SCICRUNCH-DATA-SERVICES", true);
        } elseif(!is_null($key) && $key !== "") {
            /* else get the api key object and save it to the application object */
            $request->request->remove("key");
            $request->query->remove("key");
            $request->request->remove("api_key");
            $request->query->remove("api_key");
            $key = filter_var($key, FILTER_SANITIZE_STRING);
            $api_obj = APIKey::loadByKey($key);
            if(is_null($api_obj)) return $app->json("Could not complete request", 401);
            $app["config.api_key"] = $api_obj;
        }
        if($key === "" && !$web_set) {
            return $app->json("Could not complete request", 401);
        }
    });

    $app->after(function(Request $request, Response $response) {
        if($response->headers->get("Content-Type") === "application/json" && !\helper\startsWith($_SERVER["REQUEST_URI"], "/api/1/nifservices")){
            $status_code = $response->getStatusCode();
            if($status_code >= 200 && $status_code <= 299) $success = true;
            else $success = false;
            $json_content = $response->getContent();
            if($json_content == "{}") $content = new stdClass();
            else $content = json_decode($response->getContent(), true);
            if($success) $content = Array("data" => $content);
            else $content = Array("errormsg" => $content);
            $content["success"] = $success;
            $content = json_encode($content);
            $response->setContent($content);
        }
    });

    $app->error(function(Exception $e, $code) use($app) {
        if($app["debug"]) return;
        $r = new Response();
        $r->setContent(json_encode("Could not complete request"));
        $r->headers->set("Content-Type", "application/json");
        return $r;
    });

    include_once __DIR__."/api-classes/api-controller.php";

    // ODC-SCI large data files will throw memory error, so set to 512M
    if (\helper\startsWith($_SERVER["REQUEST_URI"], "/api/1/datasets/full-upload"))
        ini_set('memory_limit', '512M');

    $app->run();

}catch (Exception $e){
    $result = Array();
    $result['success'] = false;
    $result['errormsg'] = $e->getMessage();
    header('X-PHP-Response-Code: 500', true, 500);
    return json_encode($result);
}

?>
