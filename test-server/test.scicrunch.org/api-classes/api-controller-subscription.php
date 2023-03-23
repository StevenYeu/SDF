<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

function subscribe($app, $action, $type, $identifier, $cid){
    $type = aR($type, "s");
    $identifier = aR($identifier, "s");

    $sub = updateSubscription($app["config.user"], $app["config.api_key"], $action, $type, $identifier, $cid);
    if($action !== "unsubscribe") return appReturn($app, $sub, true);
    else return appReturn($app, $sub);
}

/**
 * updateSubscriptionNotifications
 * convenience function for updating subscriptions
 *
 * @param \Silex\Application
 * @param Request
 * @param string
 * @param string
 * @param string subscribe/unsubscribe to web/email notifications
 * @return Response
 */
function updateSubscriptionNotifications($app, $request, $type, $identifier, $action){
    require_once __DIR__."/update_subscription.php";
    $type = aR($type, "s");
    $identifier = aR($identifier, "s");
    $result = updateSubscription($app["config.user"], $app["config.api_key"], $action, $type, $identifier);
    return appReturn($app, $result, true);
}

/**
 *  SWG\Get( path="/subscription/user", summary="gets the list of subscriptions for a user",
 *      SWG\Parameter( name="key", description="user", in="formData", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->get($AP."/subscription/user", function(Request $request) use($app) {  // done
    require_once __DIR__."/get_user_subscription.php";
    return appReturn($app, getUserSubscriptions($app["config.user"], $app["config.api_key"]), false, true);
});

/**
 *  SWG\Post( path="/subscription/subscribe/{type}/{identifier}", summary="Subscribe to an updatable service",
 *      SWG\Parameter( name="type", description="Type of subscription, allowed types are 'resource-mention' and 'saved-search'", in="path", required=true, type="string" ),
 *      SWG\Parameter( name="identifier", description="The unique identifier for this resource being subscribed to", in="path", required=true, type="string" ),
 *      SWG\Parameter( name="key", description="user", in="formData", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->post($AP."/subscription/subscribe/{type}/{identifier}", function(Request $request, $type, $identifier) use($app) {
    require_once __DIR__."/update_subscription.php";
    $cid = aR($request->request->get("cid"), "i");
    return subscribe($app, "subscribe", $type, $identifier, $cid);
});

/**
 *  SWG\Post( path="/subscription/unsubscribe/{type}/{identifier}", summary="Unsubscribe to an updatable service",
 *      SWG\Parameter( name="type", description="Type of subscription, allowed types are 'resource-mention' and 'saved-search'", in="path", required=true, type="string" ),
 *      SWG\Parameter( name="identifier", description="The unique identifier for this resource being subscribed to", in="path", required=true, type="string" ),
 *      SWG\Parameter( name="key", description="user", in="formData", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->post($AP."/subscription/unsubscribe/{type}/{identifier}", function(Request $request, $type, $identifier) use($app) {
    require_once __DIR__."/update_subscription.php";
    return subscribe($app, "unsubscribe", $type, $identifier);
});

/**
 *  SWG\Post( path="/subscription/subscribe/{type}/{identifier}/email", summary="Subscribe to email updates",
 *      SWG\Parameter( name="type", description="Type of subscription, allowed types are 'resource-mention' and 'saved-search'", in="path", required=true, type="string" ),
 *      SWG\Parameter( name="identifier", description="The unique identifier for this resource being subscribed to", in="path", required=true, type="string" ),
 *      SWG\Parameter( name="key", description="user", in="formData", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->post($AP."/subscription/subscribe/{type}/{identifier}/email", function(Request $request, $type, $identifier) use($app) {
    return updateSubscriptionNotifications($app, $request, $type, $identifier, "subscribe-email");
});

/**
 *  SWG\Post( path="/subscription/unsubscribe/{type}/{identifier}/email", summary="Unsubscribe to email updates",
 *      SWG\Parameter( name="type", description="Type of subscription, allowed types are 'resource-mention' and 'saved-search'", in="path", required=true, type="string" ),
 *      SWG\Parameter( name="identifier", description="The unique identifier for this resource being subscribed to", in="path", required=true, type="string" ),
 *      SWG\Parameter( name="key", description="user", in="formData", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->post($AP."/subscription/unsubscribe/{type}/{identifier}/email", function(Request $request, $type, $identifier) use($app) {
    return updateSubscriptionNotifications($app, $request, $type, $identifier, "unsubscribe-email");
});

/**
 *  SWG\Post( path="/subscription/subscribe/{type}/{identifier}/web", summary="Subscribe to SciCrunch notifications",
 *      SWG\Parameter( name="type", description="Type of subscription, allowed types are 'resource-mention' and 'saved-search'", in="path", required=true, type="string" ),
 *      SWG\Parameter( name="identifier", description="The unique identifier for this resource being subscribed to", in="path", required=true, type="string" ),
 *      SWG\Parameter( name="key", description="user", in="formData", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->post($AP."/subscription/subscribe/{type}/{identifier}/web", function(Request $request, $type, $identifier) use($app) {
    return updateSubscriptionNotifications($app, $request, $type, $identifier, "subscribe-scicrunch");
});

/**
 *  SWG\Post( path="/subscription/unsubscribe/{type}/{identifier}/web", summary="Unsubscribe to SciCrunch notifications",
 *      SWG\Parameter( name="type", description="Type of subscription, allowed types are 'resource-mention' and 'saved-search'", in="path", required=true, type="string" ),
 *      SWG\Parameter( name="identifier", description="The unique identifier for this resource being subscribed to", in="path", required=true, type="string" ),
 *      SWG\Parameter( name="key", description="user", in="formData", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->post($AP."/subscription/unsubscribe/{type}/{identifier}/web", function(Request $request, $type, $identifier) use($app) {
    return updateSubscriptionNotifications($app, $request, $type, $identifier, "unsubscribe-scicrunch");
});

/**
 *  SWG\Get( path="/subscription/newdata/{id}/{source}", summary="get the json data for a subscription",
 *      SWG\Parameter( name="id", description="ID of the subscription", in="path", required=true, type="string" ),
 *      SWG\Parameter( name="source", description="Either email or web", in="path", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->get($AP."/subscription/newdata/{id}/{source}", function(Request $request, $id, $source) use($app) {
    require_once __DIR__."/get_user_subscription.php";
    $id = aR($id, "i");
    $source = aR($source, "s");
    return appReturn($app, getSubscriptionData($app["config.user"], $app["config.api_key"], $id, $source));
});

?>
