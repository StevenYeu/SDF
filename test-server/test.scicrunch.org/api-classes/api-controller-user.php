<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

/**
 *  SWG\Get( path="/user/autocomplete", summary="Autocomplete users, returns name and guid.  Will also return email if user is a curator.",
 *      SWG\Parameter( name="name", description="name of user", in="query", required=true, type="string" )
 *  )
 **/
$app->get($AP."/user/autocomplete", function(Request $request) use($app) {  // done
    require_once __DIR__."/get_user_autocomplete.php";
    $name = aR($request->query->get("name"), "s");
    if(is_null($name)) return $app->json("name is required", 400);
    return appReturn($app, userAutocomplete($app["config.user"], $app["config.api_key"], $name));
});

/**
 *  SWG\Get( path="/user/info", summary="Return current user information")
 **/
$app->get($AP."/user/info", function(Request $request) use($app) {  // done
    require_once __DIR__."/user_info.php";
    $no_datasets = $request->query->get("no-datasets") ? true : false;
    return appReturn($app, userInfo($app["config.user"], $app["config.api_key"], NULL, $no_datasets));
});

/**
 *  SWG\Get( path="/user/info/lookup", summary="Look up another user's info",
 *      SWG\Parameter( name="uid", description="ID of the user", in="query", required=true, type="uid" )
 *  )
 **/
$app->get($AP."/user/info/lookup", function(Request $request) use($app) {   // done
    require_once __DIR__."/user_info.php";
    $lookup = $request->query->get("uid");
    if(is_null($lookup)) return $app->json("uid not provided", 400);
    return appReturn($app, userInfo($app["config.user"], $app["config.api_key"], $lookup));
});


?>
