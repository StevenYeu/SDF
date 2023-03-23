<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

/**
 * updatePermission
 * convenience class for updating permissions
 *
 * @param APIKey
 * @param User
 * @param string api key string
 * @param string action on the key
 * @param string the type of permission
 * @param string data for the permission
 */
function updatePermission($app, $user, $api_key, $key_val, $action, $permission_type, $permission_data){
    $result = APIPermissionUpdate($user, $api_key, $key_val, $action, $permission_type, $permission_data);
    if($action === "add" && $result->status_code === 200) $status = 201;
    else $status = $result->status_code;
    if($result->success){
        return $app->json($result->data, $status);
    }else{
        return $app->json($result->status_msg, $status);
    }
}

/**
 *  SWG\Post( path="/key/add", summary="Get new API key",
 *      SWG\Parameter( name="uid", description="User id number", in="form", required=true, type="integer" ),
 *      SWG\Parameter( name="project_name", description="Name of project", in="form", required=false, type="string" ),
 *      SWG\Parameter( name="description", description="description of API key", in="form", required=false, type="string" ),
 *      SWG\Parameter( name="key", description="api-moderator", in="form", required=true, type="string" ),
 *  )
 **/
$app->post($AP."/key/add", function(Request $request) use($app) {   // done
    require_once __DIR__."/api_key_update.php";
    $uid = aR($request->request->get("uid"), "i");
    $description = aR($request->request->get("description"), "s");
    $project_name = aR($request->request->get("project_name"), "s");
    if(is_null($uid)) return $app->json("uid is required", 400);

    return appReturn($app, addAPIKey($app["config.user"], $app["config.api_key"], $uid, $description, $project_name), true);
});

/**
 *  SWG\Post( path="/key/enable", summary="Enables API Key.  Cannot enable itself",
 *      SWG\Parameter( name="keyval", description="value of the API key", in="form", required=true, type="string" )
 *  )
 **/
$app->post($AP."/key/enable", function(Request $request) use($app) {    // done
    require_once __DIR__."/api_key_update.php";
    $keyval = aR($request->request->get("keyval"), "s");
    if(is_null($keyval)) return $app->json("keyval is required", 400);

    return appReturn($app, enableDisableAPIKey($app["config.user"], $app["config.api_key"], $keyval, "enable"), true);
});

/**
 *  SWG\Post( path="/key/disable", summary="Disables API key.  Can disable itself",
 *      SWG\Parameter( name="keyval", description="Value of the API key", in="form", required=true, type="string" )
 *  )
 **/
$app->post($AP."/key/disable", function(Request $request) use($app) {   // done
    require_once __DIR__."/api_key_update.php";
    $keyval = aR($request->request->get("keyval"), "s");
    if(is_null($keyval)) return $app->json("keyval is required", 400);

    return appReturn($app, enableDisableAPIKey($app["config.user"], $app["config.api_key"], $keyval, "disable"), true);
});

/**
 *  SWG\Post( path="/key/update", summary="Change project-name or description for API key",
 *      SWG\Parameter( name="keyval", description="Value of the API key", in="form", required=true, type="string" ),
 *      SWG\Parameter( name="description", description="description of the API key", in="form", required=false, type="string" ),
 *      SWG\Parameter( name="project_name", description="project name for API key", in="form", required=false, type="string" )
 *  )
 **/
$app->post($AP."/key/update", function(Request $request) use($app) {    // done
    require_once __DIR__."/api_key_update.php";
    $keyval = aR($request->request->get("keyval"), "s");
    $project_name = aR($request->request->get("project_name"), "s");
    $description = aR($request->request->get("description"), "s");
    if(is_null($keyval)) return $app->json("keyval is required", 400);

    return appReturn($app, keyUpdate($app["config.user"], $app["config.api_key"], $keyval, $description, $project_name), true);
});

/**
 *  SWG\Get( path="/key/lookup", summary="lookup another user",
 *      SWG\Parameter( name="uid", description="ID of the user", in="query", required=true, type="integer" )
 *  )
 **/
$app->get($AP."/key/lookup", function(Request $request) use($app) { // done
    require_once __DIR__."/get_api_key.php";
    $uid = aR($request->query->get("uid"), "i");
    if(is_null($uid)) return $app->json("uid is required", 400);

    return appReturn($app, getAPIKey($app["config.user"], $app["config.api_key"], $uid), false, true);
});

/**
 *  SWG\Post( path="/key/permission/add", summary="Add permission for a key",
 *      SWG\Parameter( name="keyval", description="Key value to give permission", in="form", required=true, type="string" ),
 *      SWG\Parameter( name="permission_type", description="Indicates type of permission", in="form", required=true, type="string" ),
 *      SWG\Parameter( name="permission_data", description="Data for permission", in="form", required=false, type="string" )
 *  )
 **/
$app->post($AP."/key/permission/add", function(Request $request) use($app) {    // done
    require_once __DIR__."/api_permission_update.php";
    $key_val = aR($request->request->get("keyval"), "s");
    $permission_type = aR($request->request->get("permission_type"), "s");
    $permission_data = aR($request->request->get("permission_data"), "s");
    if(is_null($key_val) || is_null($permission_type)) return $app->json("keyval and permission_type are required", 400);

    return updatePermission($app, $app["config.user"], $app["config.api_key"], $key_val, "add", $permission_type, $permission_data);
});

/**
 *  SWG\Post( path="/key/permission/enable", summary="Enable permission for a key",
 *      SWG\Parameter( name="keyval", description="Key value to give permission", in="form", required=true, type="string" ),
 *      SWG\Parameter( name="permission_type", description="Indicates type of permission", in="form", required=true, type="string" )
 *  )
 **/
$app->post($AP."/key/permission/enable", function(Request $request) use($app) { // done
    require_once __DIR__."/api_permission_update.php";
    $key_val = aR($request->request->get("keyval"), "s");
    $permission_type = aR($request->request->get("permission_type"), "s");
    if(is_null($key_val) || is_null($permission_type)) return $app->json("keyval and permission_type are required", 400);

    return updatePermission($app, $app["config.user"], $app["config.api_key"], $key_val, "enable", $permission_type, NULL);
});

/**
 *  SWG\Post( path="/key/permission/disable", summary="Disable permission for a key",
 *      SWG\Parameter( name="keyval", description="Key value to give permission", in="form", required=true, type="string" ),
 *      SWG\Parameter( name="permission_type", description="Indicates type of permission", in="form", required=true, type="string" )
 *  )
 **/
$app->post($AP."/key/permission/disable", function(Request $request) use($app) {    // done
    require_once __DIR__."/api_permission_update.php";
    $key_val = aR($request->request->get("keyval"), "s");
    $permission_type = aR($request->request->get("permission_type"), "s");
    if(is_null($key_val) || is_null($permission_type)) return $app->json("keyval and permission_type are required", 400);

    return updatePermission($app, $app["config.user"], $app["config.api_key"], $key_val, "disable", $permission_type, NULL);
});

/**
 *  SWG\Get( path="/key/permission/types", summary="Get all permission types")
 **/
$app->get($AP."/key/permission/types", function(Request $request) use($app) {   // done
    require_once __DIR__."/get_api_permission_types.php";
    return appReturn($app, getAPIPermissionTypes());
});

/**
 *  SWG\Post( path="/key/permission/data/update", summary="Change the permission data for a key",
 *      SWG\Parameter( name="keyval", description="value of the key to set permission", in="form", required=true, type="string" ),
 *      SWG\Parameter( name="permission_type", description="type of permission", in="form", required=true, type="string" ),
 *      SWG\Parameter( name="permission_data", description="Data of the permission.  If not given, then data will be set to blank", in="form", required=true, type="string" )
 *  )
 **/
$app->post($AP."/key/permission/data/update", function(Request $request) use($app) {    // done
    require_once __DIR__."/api_permission_update.php";
    $key_val = aR($request->request->get("keyval"), "s");
    $permission_type = aR($request->request->get("permission_type"), "s");
    $permission_data = aR($request->request->get("permission_data"), "s");
    if(is_null($key_val) || is_null($permission_type) || is_null($permission_data)) return $app->json("keyval and permission_type and permission_data are required", 400);

    return updatePermission($app, $app["config.user"], $app["config.api_key"], $key_val, "dataupdate", $permission_type, $permission_data);
});

?>
