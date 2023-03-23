<?php

/****************************************************************************************************
Main entry point for most pages (except /api, /php and /forms)

a silex router application is created and all paths are added to the app throught the addRoute function
see :addRoute
****************************************************************************************************/

require_once __DIR__ . "/classes/classes.php";
\helper\scicrunch_session_start();
$_SESSION["web"] = true;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/* strip trailing slash in url path */
$_SERVER["REQUEST_URI"] = preg_replace("/(\/[^?]*)\/((?:\?.*)?$)/", "$1$2", $_SERVER["REQUEST_URI"]);

$app = new Silex\Application();
$app["debug"] = false;

addRoute($app, "/resolver/page/{page}", "resolver-page.php", Array(), Array("page" => "\d+"));
addRoute($app, "/resolver/{id}", "resolver-page.php");
addRoute($app, "/resolver/{id}/direct", "resolver-page.php", Array("direct" => true));
addRoute($app, "/resolver/{id}/mentions", "resolver-page.php", Array("mentions" => true));
addRoute($app, "/resolver/{id}/co-mentions", "resolver-page.php", Array("co-mentions" => true));
addRoute($app, "/resolver", "resolver.php");
//addRoute($app, "/reslover/{id}", "resolver-page.php");
addRoute($app, "/faq/{type}/{id}", "faq-page.php");
addRoute($app, "/404/{name}", "errorr.php", Array("type" => "404"));
addRoute($app, "/private/{name}", "errorr.php", Array("type" => "private"));
addRoute($app, "/noresource", "errorr.php", Array("type" => "noresource"));
addRoute($app, "/faq/{type}", "faq-page.php");
addRoute($app, "/faq", "faq-page.php");

addRoute($app, "/browse/{type}/original/{id}/{article}", "browser.php", Array("mode" => "edit", "use" => "original_id"), Array("article" => "\d+"));
addRoute($app, "/browse/{type}/original/{id}/edit", "browser.php", Array("mode" => "edit", "use" => "original_id"));
addRoute($app, "/browse/{type}/original/{id}/{article}", "browser.php", Array("use" => "original_id"), Array("article" => "\d+"));
addRoute($app, "/browse/{type}/original/{id}", "browser.php", Array("use" => "original_id"));

addRoute($app, "/browse/{type}/{id}/page/{page}", "browser.php", Array(), Array("page" => "\d+"));
addRoute($app, "/browse/{type}", "browser.php");
addRoute($app, "/browse/{type}/page/{page}", "browser.php", Array(), Array("page" => "\d+"));
addRoute($app, "/browse/{type}/{id}/{article}/edit", "browser.php", Array("mode" => "edit"), Array("article" => "\d+"));
addRoute($app, "/browse/{type}/{id}/edit", "browser.php", Array("mode" => "edit"));
addRoute($app, "/browse/{type}/{id}/{article}", "browser.php", Array(), Array("article" => "\d+"));
addRoute($app, "/browse/{type}/{id}", "browser.php");
addRoute($app, "/browse/{type}/{id}/page/{page}", "browser.php", Array(), Array("page" => "\d+"));
addRoute($app, "/browse/{type}", "browser.php");
addRoute($app, "/browse", "browser.php");

addRoute($app, "/create/{type}", "creater.php");
addRoute($app, "/information/{page}", "pages.php");

addRoute($app, "/account/{page}/{arg1}/{arg2}/{arg3}/{arg4}", "community-redirect.php", Array("portalName" => "scicrunch", "type" => "account"));
addRoute($app, "/account/{page}/{arg1}/{arg2}/{arg3}", "community-redirect.php", Array("portalName" => "scicrunch", "type" => "account"));
addRoute($app, "/account/{page}/{arg1}/{arg2}", "community-redirect.php", Array("portalName" => "scicrunch", "type" => "account"));
addRoute($app, "/account/{page}/{arg1}", "community-redirect.php", Array("portalName" => "scicrunch", "type" => "account"));
addRoute($app, "/account/{page}", "community-redirect.php", Array("portalName" => "scicrunch", "type" => "account"));
addRoute($app, "/account", "community-redirect.php", Array("portalName" => "scicrunch", "type" => "account"));

addRoute($app, "/ResourceWatch", "community-redirect.php", Array("portalName" => "scicrunch", "type" => "resource-watch"));
addRoute($app, "/ResourceWatch/{title}", "community-redirect.php", Array("portalName" => "scicrunch", "type" => "resource-watch"));

addRoute($app, "/register", "registrar.php");

addRoute($app, "/page/{title}/{id}", "community-redirect.php", Array("portalName" => "scicrunch", "type" => "about"), Array("id" => "\d+"));
addRoute($app, "/page/{title}", "community-redirect.php", Array("portalName" => "scicrunch", "type" => "about"));

addRoute($app, "/verification", "verification-redirect.php");
addRoute($app, "/verification/", "verification-redirect.php");
addRoute($app, "/verification/{verstring}", "verification-redirect.php");

addStrippedRoute($app, "/{pmid}/resource/{resource}", "community-redirect.php", Array("portalName" => "scicrunch", "type" => "search", "category" => "literature"), Array("pmid" => "\d+"));
addStrippedRoute($app, "/{pmid}", "community-redirect.php", Array("portalName" => "scicrunch", "type" => "search", "category" => "literature"), Array("pmid" => "\d+"));
addStrippedRoute($app, "/{portalName}/{pmid}/resource/{resource}", "community-redirect.php", Array("type" => "search", "category" => "literature"), Array("pmid" => "\d+"));
addStrippedRoute($app, "/{portalName}/{pmid}", "community-redirect.php", Array("type" => "search", "category" => "literature"), Array("pmid" => "\d+"));

addStrippedRoute($app, "/{portalName}/verification/{verstring}", "verification-redirect.php");
addStrippedRoute($app, "/{portalName}/join", "community-redirect.php", Array("type" => "join"));
addStrippedRoute($app, "/{portalName}/account/{page}/{arg1}/{arg2}/{arg3}/{arg4}", "community-redirect.php", Array("type" => "account"));
addStrippedRoute($app, "/{portalName}/account/{page}/{arg1}/{arg2}/{arg3}", "community-redirect.php", Array("type" => "account"));
addStrippedRoute($app, "/{portalName}/account/{page}/{arg1}/{arg2}", "community-redirect.php", Array("type" => "account"));
addStrippedRoute($app, "/{portalName}/account/{page}/{arg1}", "community-redirect.php", Array("type" => "account"));
addStrippedRoute($app, "/{portalName}/account/{page}", "community-redirect.php", Array("type" => "account"));
addStrippedRoute($app, "/{portalName}/account", "community-redirect.php", Array("type" => "account"));
addStrippedRoute($app, "/{portalName}/rrid-report/{subtype}", "community-redirect.php", Array("type" => "rrid-report"));

addStrippedRoute($app, "/{portalName}/lab", "community-redirect.php", Array("type" => "lab"));
addStrippedRoute($app, "/{portalName}/lab/{subtype}", "community-redirect.php", Array("type" => "lab"));
addStrippedRoute($app, "/{portalName}/community-labs/{subtype}", "community-redirect.php", Array("type" => "community-labs"));
addStrippedRoute($app, "/{portalName}/data/public", "community-redirect.php", Array("type" => "data", "subtype"=>"list"));
addStrippedRoute($app, "/{portalName}/data/{dataset_id}", "community-redirect.php", Array("type" => "data", "subtype"=>"metadata"), Array("dataset_id" => "\d+"));

addStrippedRoute($app, "/{portalName}/about/{title}/page/{page}", "community-redirect.php", Array("type" => "about"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/about/{title}/{id}/{article}/edit", "community-redirect.php", Array("type" => "about", "mode" => "edit"));
addStrippedRoute($app, "/{portalName}/about/{title}/{id}/edit", "community-redirect.php", Array("type" => "about", "mode" => "edit"));
addStrippedRoute($app, "/{portalName}/about/{title}/{id}/{article}", "community-redirect.php", Array("type" => "about"));
// Manu added the three lines below
addStrippedRoute($app, "/{portalName}/about/{type}/{id}", "browser.php");
addStrippedRoute($app, "/{portalName}/browse/{type}", "browser.php");
addStrippedRoute($app, "/{portalName}/resources", "browser-sdf.php");
addStrippedRoute($app, "/{portalName}/browse/{type}/{id}", "browser.php");

// Manu added the following for CILOGON integration
addStrippedRoute($app, "/{portalName}/callback", "browser_manu.php");

addStrippedRoute($app, "/{portalName}/about/{title}/{id}", "community-redirect.php", Array("type" => "about"));
addStrippedRoute($app, "/{portalName}/about/{title}", "community-redirect.php", Array("type" => "about"));
addStrippedRoute($app, "/{portalName}/about/{title}/{id}/page/{page}", "community-redirect.php", Array("type" => "about"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/about/{title}/{id}/page/{page}/search", "community-redirect.php", Array("type" => "about"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/interlex/{title}", "community-redirect.php", Array("type" => "interlex"));
addStrippedRoute($app, "/{portalName}/interlex/{title}/{id}", "community-redirect.php", Array("type" => "interlex"));
addStrippedRoute($app, "/{portalName}/rin/{subtype}", "community-redirect.php", Array("type" => "rin"));
addStrippedRoute($app, "/{portalName}/rin/{subtype}/{arg1}", "community-redirect.php", Array("type" => "rin"));
addStrippedRoute($app, "/{portalName}/rin/{subtype}/{arg1}/{arg2}", "community-redirect.php", Array("type" => "rin"));
addStrippedRoute($app, "/{portalName}/{nif}/resolver/{id}", "community-redirect.php", Array("type" => "search", "category" => "discovery", "subcategory" => "knowledge-base"));

addStrippedRoute($app, "/{portalName}/{category}/{subcategory}/source/{nif}/page/{page}/record/{view}/{uuid}/search", "community-redirect.php", Array("type" => "search"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/{category}/{subcategory}/source/{nif}/page/{page}/record/{view}/{rrid}/resolver", "community-redirect.php", Array("type" => "search"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/{category}/{subcategory}/source/{nif}/page/{page}/record/{view}/{rrid}/resolver/{resolvertab}", "community-redirect.php", Array("type" => "search"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/{category}/{subcategory}/source/{nif}/page/{page}/search", "community-redirect.php", Array("type" => "search"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/{category}/{subcategory}/source/{nif}/record/{view}/{uuid}/search", "community-redirect.php", Array("type" => "search"));
addStrippedRoute($app, "/{portalName}/{category}/{subcategory}/source/{nif}/record/{view}/{rrid}/resolver", "community-redirect.php", Array("type" => "search"));
addStrippedRoute($app, "/{portalName}/{category}/{subcategory}/source/{nif}/record/{view}/{rrid}/resolver/{resolvertab}", "community-redirect.php", Array("type" => "search"));
addStrippedRoute($app, "/{portalName}/{category}/{subcategory}/source/{nif}/search", "community-redirect.php", Array("type" => "search"));
addStrippedRoute($app, "/{portalName}/{category}/{subcategory}/page/{page}/record/{view}/{uuid}/search", "community-redirect.php", Array("type" => "search"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/{category}/{subcategory}/page/{page}/record/{view}/{rrid}/resolver", "community-redirect.php", Array("type" => "search"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/{category}/{subcategory}/page/{page}/record/{view}/{rrid}/resolver/{resolvertab}", "community-redirect.php", Array("type" => "search"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/{category}/{subcategory}/page/{page}/search", "community-redirect.php", Array("type" => "search"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/{category}/{subcategory}/record/{view}/{uuid}/search", "community-redirect.php", Array("type" => "search"));
addStrippedRoute($app, "/{portalName}/{category}/{subcategory}/record/{view}/{rrid}/resolver", "community-redirect.php", Array("type" => "search"));
addStrippedRoute($app, "/{portalName}/{category}/{subcategory}/record/{view}/{rrid}/resolver/{resolvertab}", "community-redirect.php", Array("type" => "search"));
addStrippedRoute($app, "/{portalName}/{category}/{subcategory}/search", "community-redirect.php", Array("type" => "search"));
addStrippedRoute($app, "/{portalName}/{category}/source/{nif}/page/{page}/record/{view}/{uuid}/search", "community-redirect.php", Array("type" => "search"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/{category}/source/{nif}/page/{page}/record/{view}/{rrid}/resolver", "community-redirect.php", Array("type" => "search"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/{category}/source/{nif}/page/{page}/record/{view}/{rrid}/resolver/{resolvertab}", "community-redirect.php", Array("type" => "search"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/{category}/source/{nif}/page/{page}/search", "community-redirect.php", Array("type" => "search"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/{category}/source/{nif}/record/{view}/{uuid}", "community-redirect.php", Array("type" => "search"));
addStrippedRoute($app, "/{portalName}/{category}/source/{nif}/search", "community-redirect.php", Array("type" => "search"));
addStrippedRoute($app, "/{portalName}/{category}/page/{page}/record/{view}/{uuid}/search", "community-redirect.php", Array("type" => "search"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/{category}/page/{page}/record/{view}/{rrid}/resolver", "community-redirect.php", Array("type" => "search"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/{category}/page/{page}/record/{view}/{rrid}/resolver/{resolvertab}", "community-redirect.php", Array("type" => "search"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/{category}/page/{page}/search", "community-redirect.php", Array("type" => "search"), Array("page" => "\d+"));
addStrippedRoute($app, "/{portalName}/{category}/record/{view}/{uuid}/search", "community-redirect.php", Array("type" => "search"));
addStrippedRoute($app, "/{portalName}/{category}/record/{view}/{rrid}/resolver", "community-redirect.php", Array("type" => "search"));
addStrippedRoute($app, "/{portalName}/{category}/record/{view}/{rrid}/resolver/{resolvertab}", "community-redirect.php", Array("type" => "search"));
addStrippedRoute($app, "/{portalName}/resolver/{id}", "resolver-page.php");
addStrippedRoute($app, "/{portalName}/{category}/search", "community-redirect.php", Array("type" => "search"));

addStrippedRoute($app, "/{portalName}", "community-redirect.php");
addStrippedRoute($app, "/{portalName}/virtual-booth", "community-redirect.php", Array("type" => "virtual-booth", "title" => "Virtual Booth"));
addStrippedRoute($app, "/{portalName}/virtual-booth/{subtype}", "community-redirect.php", Array("type" => "virtual-booth"));

// Manu - modified below
//addRoute($app, "/", "community-redirect.php", Array("portalName" => "scicrunch"));
//addRoute($app, "/", "community-redirect.php", Array("portalName" => "product-discovery-portal"));
// //addRoute($app, "/index.php", "community-redirect.php", Array("portalName" => "scicrunch"));
//addRoute($app, "/index.php", "community-redirect.php", Array("portalName" => "product-discovery-portal"));

// Steven - Changed the default page
addRoute($app, "/", "community-redirect.php", Array("portalName" => "Software-Discovery-Portal"));
addRoute($app, "/index.php", "community-redirect.php", Array("portalName" => "Software-Discovery-Portal"));


addRoute($app, "/auth/cilogon", "cilogon.php");


$app->error(function(Exception $e, $request, $code) use($app) {
    $r = new Response();
    switch($code) {
        case 404:
            $r->setContent(\helper\errorPage("404-generic", NULL, false));
            return $r;
        default:
            $r->setContent(\helper\errorPage("400", NULL, false));
            return $r;
    }
});

// Steven - Add CILogon Callback page


$app->run();

/**
 * addRoute
 * adds a route to the router app
 *
 * @param \Silex\Application
 * @param string the route path. arguments are wrapped in {}
 * @param string the file that is included if the path matches
 * @param Array if this path is matched, these fields are added to the $_GET parameters. passed in key => val form
 * @param Array assertions for path variables, such as making sure a page number is all numeric or it won't match
 *
 * MARK: addRoute
 */
function addRoute(&$app, $route, $include_file, $extra_vars = Array(), $asserts = Array()) {
    $controller = $app->get($route, function(Request $request) use($include_file, $extra_vars) {
        $response = new StreamedResponse();
        $response->setCallback(function() use(&$request, $include_file, $extra_vars) {
            $_GET = array_merge($_GET, $request->attributes->get("_route_params"), $extra_vars);
            include $_SERVER["DOCUMENT_ROOT"] . "/" . $include_file;
        });
        return $response->send();
    });
    foreach($asserts as $key => $val) {
        $controller->assert($key, $val);
    }
}

/**
 * addStrippedRoute
 * convenience function
 * adds the route and a route with /stripped appended to the end with stripped in the extra_vars
 *
 * @param \Silex\Application
 * @param string the route path. arguments are wrapped in {}
 * @param string the file that is included if the path matches
 * @param Array if this path is matched, these fields are added to the $_GET parameters. passed in key => val form
 * @param Array assertions for path variables, such as making sure a page number is all numeric or it won't match
 */
function addStrippedRoute(&$app, $route, $include_file, $extra_vars = Array(), $asserts = Array()) {
    addRoute($app, $route, $include_file, $extra_vars, $asserts);
    $route_stripped = str_replace("{portalName}", "{portalName}/stripped", $route);
    if($route_stripped == $route) return;
    addRoute($app, $route_stripped, $include_file, array_merge(Array("stripped" => "true"), $extra_vars), $asserts);
}

?>
