<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 *  @SWG\Definition(
 *      definition="communityMapping",
 *      type="object",
 *      @SWG\Property(property="snippet", type="string")
 *  )
 *
 *  @SWG\Definition(
 *      definition="apiReturn_comm",
 *      @SWG\Property(property="data", ref="#/definitions/communityMapping"),
 *      @SWG\Property(property="success", type="boolean"),
 *  )
 *
 *  @SWG\Get(path="/community/{portalname}/snippet/{nifid}", summary="Get snippets for a view from a community", tags={"Community"},
 *      @SWG\Parameter(name="portalname", description="portalname of the community", in="path", required=true, type="string"),
 *      @SWG\Parameter(name="nifid", description="the view id", in="path", required=true, type="string"),
 *      @SWG\Response(response="default", ref="#/definitions/apiReturn_comm"),
 *  )
 **/
$app->get($AP."/community/{portalname}/snippet/{nifid}", function(Request $request, $portalname, $nifid) use($app) {
    require_once __DIR__."/get_community_snippet.php";
    $portalname = aR($portalname, "s");
    $nifid = aR($nifid, "s");

    return appReturn($app, getCommunitySnippet($app["config.user"], $app["config.api_key"], $portalname, $nifid), false);
});

/**
 *  @SWG\Definition(
 *      definition="genCommunityMapping",
 *      type="object",
 *      @SWG\Property(property="response", type="object"),
 *  )
 *
 *  @SWG\Definition(
 *      definition="gen_apiReturn_comm",
 *      @SWG\Property(property="data", ref="#/definitions/genCommunityMapping"),
 *      @SWG\Property(property="success", type="boolean"),
 *  )
 *
 *  @SWG\Get(path="/community/{community_portal_name}/dataservices/{category}", summary="get a community category resource", tags={"Community"},
 *      @SWG\Parameter(name="community_portal_name", description="community portal name", in="path", required=true, type="string"),
 *      @SWG\Parameter(name="category", description="category name", in="path", required=true, type="string"),
 *      @SWG\Parameter(name="q", description="query", in="query", required=false, type="string"),
 *      @SWG\Response(response="default", ref="#/definitions/gen_apiReturn_comm"),
 *  )
 *
 **/
$app->get($AP."/community/{portal_name}/dataservices/{category}", function(Request $request, $portal_name, $category) use($app) {
    require_once __DIR__ . "/nifservices_alt.php";
    return genericCommunitySearch($app, $request, aR($portal_name, "s"), aR($category, "s"), NULL);
});

/**
 *  @SWG\Get( path="/community/{community_portal_name}/dataservices/{category}/{subcategory}", summary="get a community category resource", tags={"Community"},
 *      @SWG\Parameter(name="community_portal_name", description="community portal name", in="path", required=true, type="string"),
 *      @SWG\Parameter(name="category", description="category name", in="path", required=true, type="string"),
 *      @SWG\Parameter(name="subcategory", description="subcategory name", in="path", required=true, type="string"),
 *      @SWG\Parameter(name="q", description="query", in="query", required=false, type="string"),
 *      @SWG\Response(response="default", ref="#/definitions/gen_apiReturn_comm"),
 *  )
 *
 **/
$app->get($AP."/community/{portal_name}/dataservices/{category}/{subcategory}", function(Request $request, $portal_name, $category, $subcategory) use($app) {
    require_once __DIR__ . "/nifservices_alt.php";
    return genericCommunitySearch($app, $request, aR($portal_name, "s"), aR($category, "s"), aR($subcategory, "s"));
});

/******************************************************************************************************************************************************************************************************/

function genericCommunitySearch($app, $request, $portal_name, $category, $subcategory) {
    $accept_header = isset($_SERVER["HTTP_ACCEPT"]) && $_SERVER["HTTP_ACCEPT"] == "application/json" ? "json" : "xml";
    $nif_response = communityCategorySearch($app["config.user"], $app["config.api_key"], $portal_name, $category, $subcategory, $_SERVER["QUERY_STRING"], $accept_header);

    if(is_null($nif_response)) {
        $response = new Response("bad request", 400);
        return $response;
    }

    $response = new Response($nif_response["body"], $nif_response["http_code"]);
    $response->headers->set("Content-Type", "application/" . $accept_header);
    return $response;
}

?>
