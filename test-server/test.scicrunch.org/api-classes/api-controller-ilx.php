<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 *  SWG\Post( path="/ilx/add", summary="ilx post function", tags={"ILX and term"},
 *      SWG\Parameter( name="term", description="term for new identifier", in="formData", required=true, type="string" ),
 *      SWG\Parameter( name="defining_url", description="url of identifier", in="formData", required=false, type="string" ),
 *      SWG\Parameter( name="note", description="note for identifier", in="formData", required=false, type="string" ),
 *      SWG\Parameter( name="fragment", description="The ILX identifier for this term.  If not given, one will automatically be generated", in="formData", required=false, type="string" ),
 *      SWG\Parameter( name="key", description="ilx, curator", in="formData", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->post($AP."/ilx/add", function(Request $request) use($app) {   // done
    require_once __DIR__."/ilx_add.php";
    $term = aR($request->request->get("term"), "s");
    $defining_url = aR($request->request->get("defining_url"), "s");
    $note = $request->request->get("note");
    $fragment = $request->request->get("fragment");
    if(is_null($term)) return $app->json("term is required", 400);

    return appReturn($app, ilxAdd($app["config.user"], $app["config.api_key"], $term, $defining_url, $note, $fragment), true);
});

/**
 *  SWG\Post( path="/ilx/update/{ilx_id}", summary="Update an identifier", tags={"ILX and term"},
 *      SWG\Parameter( name="ilx_id", description="Interlex ID", in="path", required=false, type="string" ),
 *      SWG\Parameter( name="term", description="Interlex term", in="formData", required=false, type="string" ),
 *      SWG\Parameter( name="defining_url", description="Interlex defining url", in="formData", required=false, type="string" ),
 *      SWG\Parameter( name="note", description="Interlex additional notes", in="formData", required=false, type="string" ),
 *      SWG\Parameter( name="key", description="ilx, curator", in="formData", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->post($AP."/ilx/update/{ilx_id}", function(Request $request, $ilx_id) use($app) {  // done
    require_once __DIR__.'/ilx_update.php';
    $ilx_id = aR($ilx_id, "s");
    $term = aR($request->request->get("term"), "s");
    $defining_url = aR($request->request->get("defining_url"), "s");
    $note = aR($request->request->get("note"), "s");

    return appReturn($app, ilxUpdate($app["config.user"], $app["config.api_key"], $ilx_id, $term, $defining_url, $note), true);
});

/**
 *
 *  @SWG\Definition(
 *      definition="ilxSearchMapping",
 *      type="object",
 *      @SWG\Property(property="id", type="string"), 
 *      @SWG\Property(property="orig_id", type="string"), 
 *      @SWG\Property(property="uid", type="string"), 
 *      @SWG\Property(property="orig_cid", type="string"), 
 *      @SWG\Property(property="cid", type="string"), 
 *      @SWG\Property(property="ilx", type="string"), 
 *      @SWG\Property(property="label", type="string"), 
 *      @SWG\Property(property="type", type="string"), 
 *      @SWG\Property(property="definition", type="string"), 
 *      @SWG\Property(property="comment", type="string"), 
 *      @SWG\Property(property="version", type="string"), 
 *      @SWG\Property(property="status", type="string"), 
 *      @SWG\Property(property="display_superclass", type="string"), 
 *      @SWG\Property(property="orig_time", type="string"), 
 *      @SWG\Property(property="time", type="string"), 
 *      @SWG\Property(property="synonyms", type="array", 
 *          @SWG\Items(
 *              type="object"
 *          ),
 *      ),
 *      @SWG\Property(property="superclasses", type="array",
 *          @SWG\Items(
 *              type="object"
 *          ),
 *      ),
 *      @SWG\Property(property="existing_ids", type="array",
 *          @SWG\Items(
 *              type="object"
 *          ),
 *      ),
 *      @SWG\Property(property="relationships", type="array",
 *          @SWG\Items(
 *              type="object"
 *          ),
 *      ),
 *      @SWG\Property(property="mappings", type="array",
 *          @SWG\Items(
 *              type="object"
 *          ),
 *      ),
 *      @SWG\Property(property="annotations", type="array",
 *          @SWG\Items(
 *              type="object"
 *          ),
 *      ),
 *      @SWG\Property(property="annotation_type", type="array",
 *          @SWG\Items(
 *              type="object"
 *          ),
 *      ),
 *      @SWG\Property(property="ontologies", type="array",
 *          @SWG\Items(
 *              type="object"
 *          ),
 *      ),
 *  )
 *
 *  @SWG\Definition(
 *      definition="apiReturn_ilx",
 *      type="object",
 *      @SWG\Property(property="data", ref="#/definitions/ilxSearchMapping"),
 *      @SWG\Property(property="success", type="boolean"),
 *  )
 *
 *  @SWG\Get( path="/ilx/search/identifier/{identifier}", summary="Search on an identifier", tags={"ILX and term"},
 *      @SWG\Parameter( name="identifier", description="Identifier being searched", in="path", required=true, type="string"),
 *      @SWG\Response(response="default", ref="#/definitions/apiReturn_ilx"),
 *  )
 **/
$app->get($AP."/ilx/search/identifier/{identifier}", function(Request $request, $identifier) use($app, $AP) {
    $subRequest = Request::create($AP."/term/ilx/".$identifier);
    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
});

/**
 *  @SWG\Get( path="/ilx/search/term/{term}", summary="Search on a term", tags={"ILX and term"},
 *      @SWG\Parameter( name="term", description="Term being searched", in="path", required=true, type="string" ),
 *      @SWG\Response(response="default", ref="#/definitions/apiReturn_ilx"),
 *  )
 **/
$app->get($AP."/ilx/search/term/{term}", function(Request $request, $term) use($app, $AP) {    // done
    $subRequest = Request::create($AP."/term/lookup/".$term);
    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
});

/**
 *  @SWG\Get( path="/ilx/search/curie/{curie}", summary="Search on a curie", tags={"ILX and term"},
 *      @SWG\Parameter( name="curie", description="Curie being searched", in="path", required=true, type="string" ),
 *      @SWG\Response(response="default", ref="#/definitions/apiReturn_ilx"),
 *  )
 */
$app->get($AP."/ilx/search/curie/{curie}", function(Request $request, $curie) use($app, $AP) {
    $subRequest = Request::create($AP."/term/curie/".$curie);
    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
});

$app->post($AP."/ilx/mappings", function(Request $request) use($app, $AP) {
    $subRequest = Request::create($AP."/term/mappings");
    return $app->handle($subRequest, HttpKernelInterface::SUB_REQUEST, false);
});

$app->get($AP."/ilx/mappings/curie/{curie}", function(Request $request, $curie) use($app) {
    require_once __DIR__ . "/get_ilx_mappings.php";
    return appReturn($app, getMappingsByCurie($app["config.user"], $app["config.api_key"], $curie), false, true);
});

$app->get($AP."/ilx/mappings/value/{value}", function(Request $request, $value) use($app) {
    require_once __DIR__ . "/get_ilx_mappings.php";
    return appReturn($app, getMappingsByValue($app["config.user"], $app["config.api_key"], $value), false, true);
});

$app->get($AP."/ilx/community/terms/{portal_name}", function(Request $request, $portal_name) use($app) {
    require_once __DIR__."/term/get_community_terms.php";
    $results = getCommunityTermsByName($app["config.user"], $app["config.api_key"], $portal_name);
    return appReturn($app, $results, false);
});


?>
