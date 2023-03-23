<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

/**
 *  SWG\Get( path="/rrid/alternate/view/{rrid}", summary="Get all active and inactive id maps for an rrid", tags={"RRIDs"},
 *      SWG\Parameter( name="rrid", description="resource relationship ID", in="path", required=false, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->get($AP."/rrid/alternate/view/{rrid}", function(Request $request, $rrid) use($app) {   // done
    require_once __DIR__."/get_rrid_alternate_ids.php";
    $rrid = aR($rrid, "s");
    return appReturn($app, getRRIDAlternateIDs($app["config.user"], $app["config.api_key"], $rrid, 100, 0), false, true);
});

/**
 *  SWG\Post( path="/rrid/alternate/{action}/{rid}", summary="Set an existing id map as active or inactive", tags={"RRIDs"},
 *      SWG\Parameter( name="action", description="add or active", in="path", required=false, type="string" ),
 *      SWG\Parameter( name="rrid", description="ID of resource", in="path", required=false, type="string" ),
 *      SWG\Parameter( name="altid", description="alternate ID", in="formData", required=true, type="string" ),
 *      SWG\Parameter( name="active", description="active fields", in="formData", required=true, type="string" ),
 *      SWG\Parameter( name="key", description="curator, moderator, rrid", in="formData", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->post($AP."/rrid/alternate/{action}/{rrid}", function(Request $request, $action, $rrid) use($app) {    // done
    require_once __DIR__."/update_rrid_alternate_ids.php";
    $action = aR($action, "s");
    $rrid = aR($rrid, "s");
    $altid = aR($request->request->get("altid"), "s");
    if(is_null($altid)) return $app->json("altid is required", 400);
    if($action === "active"){
        $active = aR($request->request->get("active"), "s");
        if(is_null($active)) return $app->json("active is required", 400);
    }else{
        $active = NULL;
    }

    return appReturn($app, updateRRIDAlternateIDs($app["config.user"], $app["config.api_key"], $rrid, $action, $altid, $active), true);
});

/**
 *  SWG\Get( path="/rrid/alternate/browse", summary="browse all rrid mappings", tags={"RRIDs"},
 *      SWG\Parameter( name="count", description="", in="query", required=false, type="string" ),
 *      SWG\Parameter( name="offset", description="", in="query", required=false, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->get($AP."/rrid/alternate/browse", function(Request $request) use ($app) {
    require_once __DIR__."/get_rrid_alternate_ids.php";
    $count = aR($request->query->get("count"), "i");
    if(is_null($count)) $count = 20;
    $offset = aR($request->query->get("offset"), "i");
    if(is_null($offset)) $offset = 0;

    return appReturn($app, getRRIDAlternateIDs($app["config.user"], $app["config.api_key"], NULL, $count, $offset), false, true);
});

?>
