<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

function APIAddDeleteRelationship($app, $request, $user, $api_key, $action, $rid){
    $rid = aR($rid, "s");
    $id1 = aR($request->request->get("id1"), "s");
    $id2 = aR($request->request->get("id2"), "s");
    $type = aR($request->request->get("type"), "s");
    $relationship = aR($request->request->get("relationship"), "s");
    if(is_null($id1) || is_null($id2) || is_null($type) || is_null($relationship)) return $app->json("id1 and id2 and type and relationship are required", 400);

    return appReturn($app, addDeleteResourceRelationship($user, $api_key, $action, $rid, $id1, $id2, $type, $relationship));
}

function voteMarkMention($app, $action, $rid, $mentionid, $rating){
    $rid = aR($rid, "s");
    $mentionid = aR($mentionid, "s");
    $rating = aR($rating, "s");

    return appReturn($app, markVoteResourceMention($app["config.user"], $app["config.api_key"], $action, $rid, $mentionid, $rating));
}

/**
 *
 *  SWG\Get( path="/resource/owner/{rid}", summary="Get all owners of a resource", tags={"Resources"},
 *      SWG\Parameter( name="rid", description="ID of the resource", in="path", required=false, type="string" ),
 *      SWG\Parameter( name="key", description="curator", in="query", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->get($AP."/resource/owner/{rid}", function(Request $request, $rid) use($app) { // done
    require_once __DIR__."/resource_owners.php";
    $rid = aR($rid, "s");

    return appReturn($app, resourceOwners($app["config.user"], $app["config.api_key"], "get", $rid), false, true);
});

//Swagger definitions
/**
 *  @SWG\Definition(
 *      definition="resourceMapping1",
 *      type="object",
 *      @SWG\Property(property="results", type="array",
 *          @SWG\Items(
 *              type="string"
 *          ),
 *      ),
 *  )
 *
 *  @SWG\Definition(
 *      definition="resourceMapping2",
 *      type="object",
 *      @SWG\Property(property="results", type="array",
 *          @SWG\Items(
 *              type="object"
 *          ),
 *      ),
 *  )
 *
 *  @SWG\Definition(
 *      definition="rsc_edit",
 *      type="object",
 *      @SWG\Property(property="rid", type="string"),
 *      @SWG\Property(property="fields", type="string"),
 *  )
 *
 *  @SWG\Definition(
 *      definition="rsc_add_del",
 *      type="object",
 *      @SWG\Property(property="rid", type="string"),
 *      @SWG\Property(property="id1", type="string"),
 *      @SWG\Property(property="id2", type="string"),
 *      @SWG\Property(property="type", type="string"),
 *      @SWG\Property(property="relationship", type="string"),
 *      @SWG\Property(property="key", type="string"),
 *  )
 *
 *  @SWG\Definition(
 *      definition="vote",
 *      type="object",
 *      @SWG\Property(property="vote", type="string"),
 *      @SWG\Property(property="rid", type="string"),
 *      @SWG\Property(property="mentionid", type="string"),
 *      @SWG\Property(property="key", type="string"),
 *  )
 *
 *  @SWG\Definition(
 *      definition="mention_add",
 *      type="object",
 *      @SWG\Property(property="rid", type="string"),
 *      @SWG\Property(property="mentionid", type="string"),
 *      @SWG\Property(property="key", type="string"),
 *  )
 *
 *  @SWG\Definition(
 *      definition="field_add",
 *      type="object",
 *      @SWG\Property(property="columns", type="string"),
 *      @SWG\Property(property="resource_type", type="integer"),
 *      @SWG\Property(property="cid", type="integer"),
 *      @SWG\Property(property="key", type="string"),
 *  )
 *
 *  @SWG\Definition(
 *      definition="ret_str_array",
 *      type="object",
 *      @SWG\Property(property="data", ref="#/definitions/resourceMapping1"),
 *      @SWG\Property(property="success", type="boolean"),
 *  )
 *
 *  @SWG\Definition(
 *      definition="ret_int",
 *      type="object",
 *      @SWG\Property(property="data", type="integer"),
 *      @SWG\Property(property="success", type="boolean"),
 *  )
 *
 *  @SWG\Definition(
 *      definition="ret_obj_array",
 *      type="object",
 *      @SWG\Property(property="data", ref="#/definitions/resourceMapping2"),
 *      @SWG\Property(property="success", type="boolean"),
 *  )
 *
 *  @SWG\Definition(
 *      definition="ret_edit",
 *      type="object",
 *      @SWG\Property(property="data", ref="#/definitions/rsc_edit"),
 *      @SWG\Property(property="success", type="boolean"),
 *  )
 *
 *  @SWG\Definition(
 *      definition="ret_vote",
 *      type="object",
 *      @SWG\Property(property="data", ref="#/definitions/vote"),
 *      @SWG\Property(property="success", type="boolean"),
 *  )
 *
 *  @SWG\Definition(
 *      definition="ret_add_or_del",
 *      type="object",
 *      @SWG\Property(property="data", ref="#/definitions/rsc_add_del"),
 *      @SWG\Property(property="success", type="boolean"),
 *  )
 *
 *  @SWG\Definition(
 *      definition="ret_mention_add",
 *      type="object",
 *      @SWG\Property(property="data", ref="#/definitions/mention_add"),
 *      @SWG\Property(property="success", type="boolean"),
 *  )
 *
 *  @SWG\Definition(
 *      definition="ret_field_add",
 *      type="object",
 *      @SWG\Property(property="data", ref="#/definitions/field_add"),
 *      @SWG\Property(property="success", type="boolean"),
 *  )
 */

/**  @SWG\Get( path="/resource/fields/autocomplete", tags={"Resources"},
 *      @SWG\Parameter( name="field", description="Field of the resource", in="query", required=true, type="string" ),
 *      @SWG\Parameter( name="value", description="Value of the Resource", in="query", required=true, type="string" ),
 *      @SWG\Response(response="default", ref="#/definitions/ret_str_array"),
 *  )
 **/
$app->get($AP."/resource/fields/autocomplete", function(Request $request) use($app) {   // done
    require_once __DIR__."/get_resource_fields_autocomplete.php";
    $key = aR($request->query->get("field"), "s");
    $val = aR($request->query->get("value"), "s");
    if(is_null($key) || is_null($val)) return $app->json("fields and value are required", 400);
    $max = 20;
    return appReturn($app, resourceFieldAutocomplete($app["config.user"], $app["config.api_key"], $key, $val, $max));
});

/**
 *  @SWG\Get( path="/resource/rel/types", summary="Get all relationship types", tags={"Resources"},
 *      @SWG\Response(response="default", ref="#/definitions/ret_str_array"),
 *  )
 **/
$app->get($AP."/resource/rel/types", function(Request $request) use($app) { // done
    require_once __DIR__."/get_resource_relationship_types.php";
    return appReturn($app, getAllResourceRelationshipTypes(), false, true);
});

/**
 *  @SWG\Get( path="/resource/rel/view/{rid}", summary="Get all relationships for a resource", tags={"Resources"},
 *      @SWG\Parameter( name="rid", description="ID of the resource", in="path", required=false, type="string" ),
 *      @SWG\Parameter( name="count", description="relationship count", in="query", required=false, type="integer" ),
 *      @SWG\Parameter( name="offset", description="the offset", in="query", required=false, type="integer" ),
 *      @SWG\Parameter( name="canon_only", description="canon_only", in="query", required=false, type="integer" ),
 *      @SWG\Response(response="default", ref="#/definitions/ret_str_array"),
 *  )
 **/
$app->get($AP."/resource/rel/view/{rid}", function(Request $request, $rid) use($app) {  // done
    require_once __DIR__."/get_all_resource_relationships.php";
    $rid = aR($rid, "s");
    $count = aR($request->query->get("count"), "i");
    $offset = aR($request->query->get("offset"), "i");
    $canon_only = is_null(ar($request->query->get("canon_only"), "i")) ? false : true;

    return appReturn($app, getAllResourceRelationships($app["config.user"], $app["config.api_key"], $rid, $count, $offset, $canon_only), false, true);
});

$app->get($AP."/resource/rel/view/{rid}/bytype", function(Request $request, $rid) use($app) {
    require_once __DIR__."/get_all_resource_relationships.php";
    $type = $request->query->get("type");

    return appReturn($app, getByTypeResourceRelationships($app["config.user"], $app["config.api_key"], $rid, $type), false, true);
});

/**
 *  @SWG\Get( path="/resource/rel/count/{rid}", summary="Get the count of all relationships for a resource", tags={"Resources"},
 *      @SWG\Parameter( name="rid", description="ID of the resource", in="path", required=false, type="string" ),
 *      @SWG\Parameter( name="canon_only", description="canon_only", in="query", required=false, type="integer" ),
 *      @SWG\Response(response="default", ref="#/definitions/ret_int"),
 *  )
 **/
$app->get($AP."/resource/rel/count/{rid}", function(Request $request, $rid) use($app) {  // done
    require_once __DIR__."/get_all_resource_relationships.php";
    $rid = aR($rid, "s");
    $canon_only = is_null(ar($request->query->get("canon_only"), "i")) ? false : true;

    return appReturn($app, getResourceRelationshipsCount($app["config.user"], $app["config.api_key"], $rid, $canon_only), false, false);
});

/**
 *  @SWG\Get( path="/resource/fields/view/{rid}", summary="Get single resource", tags={"Resources"},
 *      @SWG\Parameter( name="rid", description="ID of the resource", in="path", required=false, type="string" ),
 *      @SWG\Parameter( name="version", description="resource version", in="query", required=false, type="integer" ),
 *      @SWG\Response(response="default", ref="#/definitions/ret_obj_array"),
 *  )
 **/
$app->get($AP."/resource/fields/view/{rid}", function(Request $request, $rid) use($app) {   // done
    require_once __DIR__."/get_resource_fields.php";
    $rid = aR($rid, "s");
    $version = aR($request->query->get("version"), "i");

    return appReturn($app, getResourceFields($app["config.user"], $app["config.api_key"], $rid, $version));
});

/**
 *  @SWG\Post( path="/resource/fields/edit/{rid}", summary="Update resource fields", tags={"Resources"},
 *      @SWG\Parameter( name="rid", description="ID of the resource", in="path", required=false, type="string" ),
 *      @SWG\Parameter( name="fields", description="json object where each key is the field and each value is the field being changed.", in="formData", required=false, type="string" ),
 *      @SWG\Response(response="default", ref="#/definitions/ret_edit"),
 *  )
 **/
$app->post($AP."/resource/fields/edit/{rid}", function(Request $request, $rid) use($app) {  // done
    require_once __DIR__."/edit_resource_fields.php";
    $rid = aR($rid, "s");

    $args = $request->request->get("fields");

    return appReturn($app, editResourceFields($app["config.user"], $app["config.api_key"], $rid, $args));
});

/**
 *  @SWG\Post( path="/resource/rel/add/{rid}", summary="Add new relationship", tags={"Resources"},
 *      @SWG\Parameter( name="rid", description="scicrunch id", in="path", required=true, type="string" ),
 *      @SWG\Parameter( name="id1", description="left side of relationship string", in="formData", required=true, type="string" ),
 *      @SWG\Parameter( name="id2", description="right side of relationship string", in="formData", required=true, type="string" ),
 *      @SWG\Parameter( name="type", description="type of id that is not rid (rel)", in="formData", required=true, type="string" ),
 *      @SWG\Parameter( name="relationship", description="name of relationship", in="formData", required=true, type="string" ),
 *      @SWG\Parameter( name="key", description="user", in="formData", required=true, type="string" ),
 *      @SWG\Response(response="default", ref="#/definitions/ret_add_or_del"),
 *  )
 **/
$app->post($AP."/resource/rel/add/{rid}", function(Request $request, $rid) use($app) {  // done
    require_once __DIR__."/add_delete_resource_relationship.php";
    return APIAddDeleteRelationship($app, $request, $app["config.user"], $app["config.api_key"], "add", $rid);
});

/**
 *  @SWG\Post( path="/resource/rel/del/{rid}", summary="delete relationship", tags={"Resources"},
 *      @SWG\Parameter( name="rid", description="resource ID", in="path", required=false, type="string" ),
 *      @SWG\Parameter( name="id1", description="left side of relationship string", in="formData", required=true, type="string" ),
 *      @SWG\Parameter( name="id2", description="right side of relationship string", in="formData", required=true, type="string" ),
 *      @SWG\Parameter( name="type", description="type of id that is not rid (rel)", in="formData", required=true, type="string" ),
 *      @SWG\Parameter( name="relationship", description="name of relationship", in="formData", required=true, type="string" ),
 *      @SWG\Parameter( name="key", description="user", in="formData", required=true, type="string" ),
 *      @SWG\Response(response="default", ref="#/definitions/ret_add_or_del"),
 *  )
 **/
$app->post($AP."/resource/rel/del/{rid}", function(Request $request, $rid) use($app) {  // done
    require_once __DIR__."/add_delete_resource_relationship.php";
    return APIAddDeleteRelationship($app, $request, $app["config.user"], $app["config.api_key"], "del", $rid);
});

/**
 *  SWG\Post( path="/resource/owner/{rid}/add", summary="Add new resource owner", tags={"Resources"},
 *      SWG\Parameter( name="rid", description="resource ID", in="path", required=false, type="string" ),
 *      SWG\Parameter( name="uid", description="user ID of owner", in="formData", required=true, type="integer" ),
 *      SWG\Parameter( name="key", description="curator", in="formData", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->post($AP."/resource/owner/{rid}/add", function(Request $request, $rid) use($app) {    // done
    require_once __DIR__."/resource_owners.php";
    $rid = aR($rid, "s");
    $uid = aR($request->request->get("uid"), "i");
    if(is_null($uid)) return $app->json("uid is required", 400);
    return appReturn($app, resourceOwners($app["config.user"], $app["config.api_key"], "add", $rid, $uid), true);
});

/**
 *  SWG\Post( path="/resource/owner/{rid}/del", summary="Delete resource owner", tags={"Resources"},
 *      SWG\Parameter( name="rid", description="resource ID", in="path", required=false, type="string" ),
 *      SWG\Parameter( name="uid", description="user ID of owner", in="formData", required=true, type="integer" ),
 *      SWG\Parameter( name="key", description="curator", in="formData", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->post($AP."/resource/owner/{rid}/del", function(Request $request, $rid) use($app) {    // done
    require_once __DIR__."/resource_owners.php";
    $rid = aR($rid, "s");
    $uid = aR($request->request->get("uid"), "i");
    if(is_null($uid)) return $app->json("uid is required", 400);
    return appReturn($app, resourceOwners($app["config.user"], $app["config.api_key"], "del", $rid, $uid));
});

/**
 *  SWG\Get( path="/resource/owner/{rid}/check", summary="Checks if user owns resource", tags={"Resources"},
 *      SWG\Parameter( name="rid", description="resource ID", in="path", required=false, type="string" ),
 *      SWG\Parameter( name="key", description="", in="query", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->get($AP."/resource/owner/{rid}/check", function(Request $request, $rid) use($app) {   // done
    require_once __DIR__."/resource_owners.php";
    $rid = aR($rid, "s");
    return appReturn($app, resourceOwners($app["config.user"], $app["config.api_key"], "check", $rid));
});

/**
 *  @SWG\Get( path="/resource/mention/view/{rid}", summary="Gets all mentions for a resource", tags={"Resources"},
 *      @SWG\Parameter( name="rid", description="resource ID", in="path", required=false, type="string" ),
 *      @SWG\Parameter( name="count", description="Counts the number of mentions", in="query", required=false, type="integer" ),
 *      @SWG\Parameter( name="offset", description="offset", in="query", required=false, type="integer" ),
 *      @SWG\Parameter( name="orderby", description="orderby fields", in="query", required=false, type="string" ),
 *      @SWG\Response(response="default", ref="#/definitions/ret_obj_array"),
 *  )
 **/
$app->get($AP."/resource/mention/view/{rid}", function(Request $request, $rid) use($app) {  // done
    require_once __DIR__."/get_all_resource_mentions.php";
    $rid = aR($rid, "s");
    $count = aR($request->query->get("count"), "i");
    $offset = aR($request->query->get("offset"), "i");
    $orderby = aR($request->query->get("orderby"), "s");
    $confidence = aR($request->query->get("confidence"), "s");

    return appReturn($app, getAllResourceMentions($app["config.user"], $app["config.api_key"], $rid, $count, $offset, $orderby, $confidence));
});

/**
 *  @SWG\Get( path="/resource/mention/count/{rid}", summary="Get count of all resource mentions", tags={"Resources"},
 *      @SWG\Parameter( name="rid", description="resource ID", in="path", required=false, type="string" ),
 *      @SWG\Response(response="default", ref="#/definitions/ret_int"),
 *  )
 **/
$app->get($AP."/resource/mention/count/{rid}", function(Request $request, $rid) use($app) { // done
    $confidence = aR($request->query->get("confidence"), "s");
    require_once __DIR__."/get_count_resource_mentions.php";
    $rid = aR($rid, "s");

    return appReturn($app, getCountResourceMentions($app["config.user"], $app["config.api_key"], $rid, $confidence));
});

/**
 *  SWG\Post( path="/resource/fields/curate/{rid}", summary="Curate a resource", tags={"Resources"},
 *      SWG\Parameter( name="rid", description="resource ID", in="path", required=false, type="string" ),
 *      SWG\Parameter( name="version", description="Resource version to curate", in="formData", required=true, type="integer" ),
 *      SWG\Parameter( name="status", description="Resource status", in="formData", required=true, type="string" ),
 *      SWG\Parameter( name="key", description="curator", in="formData", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->post($AP."/resource/fields/curate/{rid}", function(Request $request, $rid) use($app) {    // done
    require_once __DIR__."/curate_resource.php";
    $rid = aR($rid, "s");
    $version = aR($request->request->get("version"), "i");
    $status = aR($request->request->get("status"), "s");
    if(is_null($version) || is_null($status)) return $app->json("version and status are required", 400);

    return appReturn($app, curateResource($app["config.user"], $app["config.api_key"], $rid, $version, $status));
});

$app->post($AP."/resource/fields/reject-all/{rid}", function(Request $request, $rid) use($app) {
    require_once __DIR__."/curate_resource.php";
    $rid = aR($rid, "s");

    return appReturn($app, rejectResource($app["config.user"], $app["config.api_key"], $rid));
});

/**
 *  @SWG\Get( path="/resource/mention/bymention/{mentionid}", summary="Get all resources that have this mention", tags={"Resources"},
 *      @SWG\Parameter( name="mentionid", description="mention ID", in="path", required=false, type="string" ),
 *      @SWG\Parameter( name="rating", description="mention rating", in="query", required=false, type="string" ),
 *      @SWG\Parameter( name="not_rating", description="mention not_rating", in="query", required=false, type="string" ),
 *      @SWG\Response(response="default", ref="#/definitions/ret_str_array"),
 *  )
 **/
$app->get($AP."/resource/mention/bymention/{mentionid}", function(Request $request, $mentionid) use($app) { // done
    require_once __DIR__."/get_resource_bymention.php";
    $mentionid = aR($mentionid, "s");
    $rating = ar($request->query->get("rating"), "s");
    $not_rating = aR($request->query->get("not_rating"), "s");

    return appReturn($app, getResourceByMention($app["config.user"], $app["config.api_key"], $mentionid, $rating, $not_rating));
})->assert("mentionid", "[\w\-\._/:]+");

/**
 *  @SWG\Get( path="/resource/versions/all/{rid}", summary="Get all versions of resources", tags={"Resources"},
 *      @SWG\Parameter( name="rid", description="ID of resource", in="path", required=false, type="string" ),
 *      @SWG\Response(response="default", ref="#/definitions/ret_obj_array"),
 *  )
 **/
$app->get($AP."/resource/versions/all/{rid}", function(Request $request, $rid) use($app) {  // done
    require_once __DIR__."/get_resource_versions.php";
    $rid = aR($rid, "s");

    return appReturn($app, getResourceVersions($app["config.user"], $app["config.api_key"], $rid));
});

/**
 *  @SWG\Get( path="/resource/versions/diff/{rid}", summary="Get difference between two resource versions", tags={"Resources"},
 *      @SWG\Parameter( name="rid", description="ID of resource", in="path", required=false, type="string" ),
 *      @SWG\Parameter( name="1", description="version of the first resource", in="query", required=true, type="integer" ),
 *      @SWG\Parameter( name="2", description="version of the second resource", in="query", required=true, type="integer" ),
 *      @SWG\Response(response="default", ref="#/definitions/ret_obj_array"),
 *  )
 **/
$app->get($AP."/resource/versions/diff/{rid}", function(Request $request, $rid) use($app) { // done
    require_once __DIR__."/get_resource_versions_diff.php";
    $rid = aR($rid, "s");
    $version1 = aR($request->query->get("1"), "i");
    $version2 = aR($request->query->get("2"), "i");
    if(is_null($version1) || is_null($version2)) return $app->json("1 and 2 are required", 400);

    return appReturn($app, getResourceVersionDiff($app["config.user"], $app["config.api_key"], $rid, $version1, $version2));
});

/**
 *  SWG\Post( path="/resource/mention/mark/{rid}/{mentionid}", summary="Mark a mention as good or bad", tags={"Resources"},
 *      SWG\Parameter( name="mark", description="Mark for the mention.  Can either be good or bad.", in="formData", required=true, type="string" ),
 *      SWG\Parameter( name="rid", description="Scicrunch ID", in="path", required=true, type="string" ),
 *      SWG\Parameter( name="mentionid", description="The pmid or doi", in="path", required=true, type="string" ),
 *      SWG\Parameter( name="key", description="curator", in="formData", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->post($AP."/resource/mention/mark/{rid}/{mentionid}", function(Request $request, $rid, $mentionid) use($app) { // done
    require_once __DIR__."/mark_vote_resource_mention.php";
    $rating = aR($request->request->get("mark"), "s");
    return voteMarkMention($app, "mark", $rid, $mentionid, $rating);
})->assert("mentionid", "[\w\-\._/:]+");

/**
 *  @SWG\Post( path="/resource/mention/vote/{rid}/{mentionid}", summary="Vote a mention as good or bad", tags={"Resources"},
 *      @SWG\Parameter( name="vote", description="Vote for the mention.  Can either be good or bad.", in="formData", required=true, type="string" ),
 *      @SWG\Parameter( name="rid", description="Scicrunch ID", in="path", required=true, type="string" ),
 *      @SWG\Parameter( name="mentionid", description="The pmid or doi", in="path", required=true, type="string" ),
 *      @SWG\Parameter( name="key", description="curator", in="formData", required=true, type="string" ),
 *      @SWG\Response(response="default", ref="#/definitions/ret_vote"),
 *  )
 **/
$app->post($AP."/resource/mention/vote/{rid}/{mentionid}", function(Request $request, $rid, $mentionid) use($app) { // done
    require_once __DIR__."/mark_vote_resource_mention.php";
    $rating = aR($request->request->get("vote"), "s");
    return voteMarkMention($app, "vote", $rid, $mentionid, $rating);
})->assert("mentionid", "[\w\-\._/:]+");

/**
 *  @SWG\Post( path="/resource/mention/add/{rid}", summary="Add a new mention", tags={"Resources"},
 *      @SWG\Parameter( name="rid", description="Resource ID of mention", in="path", required=false, type="string" ),
 *      @SWG\Parameter( name="mentionid", description="ID of mention", in="formData", required=false, type="string" ),
 *      @SWG\Parameter( name="key", description="user, curator, resource-mentions", in="formData", required=true, type="string" ),
 *      @SWG\Response(response="default", ref="#/definitions/ret_mention_add"),
 *  )
 **/
$app->post($AP."/resource/mention/add/{rid}", function(Request $request, $rid) use($app) {
    require_once __DIR__."/add_resource_mention.php";
    $rid = aR($rid, "s");
    $mentionid = aR($request->request->get("mentionid"), "s");
    $rdw = is_null($request->request->get("rdw")) ? false : true;
    $confidence = !is_null($request->request->get("confidence")) ? aR($request->request->get("confidence"), "f") : 1.0;
    $snippet = aR($request->request->get("snippet"), "s");
    $input_source = $request->request->get("input_source") ? $request->request->get("input_source") : ResourceMention::SOURCE_USER;

    return appReturn($app, addResourceMention($app["config.user"], $app["config.api_key"], $rid, $mentionid, $input_source, $confidence, $snippet), true);
});

$app->post($AP."/resource/mention/updatesnippet/{rid}/{mentionid}", function(Request $request, $rid, $mentionid) use($app) {
    require_once __DIR__."/update_resource_mention_snippet.php";
    $mentionid = aR($mentionid, "s");
    $rid = aR($rid, "s");
    $snippet = aR($request->request->get("snippet"), "s");
    if(is_null($snippet)) $snippet = "";
    return appReturn($app, updateResourceMentionSnippet($app["config.user"], $app["config.api_key"], $rid, $mentionid, $snippet));
})->assert("mentionid", "[\w\-\._/:]+");

$app->post($AP."/resource/mention/updatesource/{rid}/{mentionid}", function(Request $request, $rid, $mentionid) use($app) {
    require_once __DIR__."/update_resource_mention_source.php";
    $source = aR($request->request->get("source"), "s");
    return appReturn($app, updateResourceMentionSource($app["config.user"], $app["config.api_key"], aR($rid, "s"), aR($mentionid, "s"), $source));
})->assert("mentionid", "[\w\-\._/:]+");

/**
 *  @SWG\Get( path="/resource/mention/view/{rid}/{mentionid}", summary="Single mention for a resource", tags={"Resources"},
 *      @SWG\Parameter( name="rid", description="Resource ID of mention", in="path", required=false, type="string" ),
 *      @SWG\Parameter( name="mentionid", description="ID of mention", in="path", required=false, type="string" ),
 *      @SWG\Response(response="default", ref="#/definitions/ret_str_array"),
 *  )
 **/
$app->get($AP."/resource/mention/view/{rid}/{mentionid}", function(Request $request, $rid, $mentionid) use($app) {  // done
    require_once __DIR__."/get_single_resource_mention.php";
    $rid = aR($rid, "s");
    $mentionid = aR($mentionid, "s");

    return appReturn($app, getSingleResourceMention($app["config.user"], $app["config.api_key"], $rid, $mentionid));
})->assert("mentionid", "[\w\-\._/:]+");

/**
 *  SWG\Get( path="/resource/pendingowner/{rid}/check", summary="Check if pending owner", tags={"Resources"},
 *      SWG\Parameter( name="rid", description="Resource ID", in="path", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->get($AP."/resource/pendingowner/{rid}/check", function(Request $request, $rid) use($app) {
    require_once __DIR__."/resource_pendingowners.php";
    $rid = aR($rid, "s");
    return appReturn($app, checkPendingOwner($app["config.user"], $app["config.api_key"], $rid));
});

/**
 *  SWG\Post( path="/resource/pendingowner/{rid}/add", summary="Add self as pending owner", tags={"Resources"},
 *      SWG\Parameter( name="rid", description="Resource ID", in="path", required=true, type="string" ),
 *      SWG\Parameter( name="text", description="Description text", in="formData", required=false, type="string" ),
 *      SWG\Parameter( name="key", description="curator", in="formData", required=true, type="string" ),
 *      SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->post($AP."/resource/pendingowner/{rid}/add", function(Request $request, $rid) use($app) {
    require_once __DIR__."/resource_pendingowners.php";
    $rid = aR($rid, "s");
    $text = aR($request->request->get("text"), "s");
    return appReturn($app, addPendingOwner($app["config.user"], $app["config.api_key"], $rid, $text), true);
});

$app->post($AP."/resource/pendingowner/{rid}/review", function(Request $request, $rid) use($app) {
    require_once __DIR__."/resource_pendingowners.php";
    $rid = aR($rid, "s");
    $uid = aR($request->request->get("uid"), "i");
    $status = aR($request->request->get("status"), "s");
    if(is_null($status) || is_null($uid)) return $app->json("uid and status are required", 400);
    $result = reviewPendingOwner($app["config.user"], $app["config.api_key"], $rid, $uid, $status);
    $is_obj = !is_null($result->data);
    return appReturn($app, $result, $is_obj);
});

$app->get($AP."/resource/pendingowner", function(Request $request) use($app) {
    require_once __DIR__."/resource_pendingowners.php";
    $count = aR($request->query->get("count"), "i");
    $offset = aR($request->query->get("offset"), "i");
    if(is_null($count)) $count = 20;
    if(is_null($offset)) $offset = 0;
    return appReturn($app, getAllPendingOwners($app["config.user"], $app["config.api_key"], $count, $offset), false, true);
});

$app->get($AP."/resource/pendingowner/count", function(Request $request) use($app) {
    require_once __DIR__."/resource_pendingowners.php";
    return appReturn($app, getAllPendingOwnersCount($app["config.user"], $app["config.api_key"]));
});

/**
 *  @SWG\Post( path="/resource/fields/add", summary="Add a resource", tags={"Resources"},
 *      @SWG\Parameter( name="columns", description="The resource fields, in JSON format", in="formData", required=true, type="string" ),
 *      @SWG\Parameter( name="resource_type", description="The type ID of the resource", in="formData", required=false, type="integer" ),
 *      @SWG\Parameter( name="cid", description="The community ID that is submitting the resource", in="formData", required=false, type="integer" ),
 *      @SWG\Parameter( name="key", description="user", in="formData", required=true, type="string" ),
 *      @SWG\Response(response="default", ref="#/definitions/ret_field_add"),
 *  )
 **/
$app->post($AP."/resource/fields/add", function(Request $request) use($app) {
    require_once __DIR__."/add_resource.php";
    $columns = $request->request->get("columns");
    $resource_type = $request->request->get("resource_type");
    $cid = $request->request->get("cid");

    return appReturn($app, addResource($app["config.user"], $app["config.api_key"], $columns, $resource_type, $cid));
});

/**
 *  @SWG\Get( path="/resource/fields/types", summary="Get all the resource types", tags={"Resources"},
 *      @SWG\Response(response="default", ref="#/definitions/ret_obj_array"),
 *  )
 **/
$app->get($AP."/resource/fields/types", function(Request $request) use($app) {
    require_once __DIR__."/get_resource_types.php";
    return appReturn($app, getResourceTypes($app["config.user"], $app["config.api_key"]), false);
});

$app->get($AP."/resource/fields/types/{id}", function(Request $request, $id) use($app) {
    require_once __DIR__."/get_resource_types.php";
    return appReturn($app, getSingleResourceType($app["config.user"], $app["config.api_key"], $id), false);
});

$app->post($AP."/resource/type/{rid}", function (Request $request, $rid) use($app) {
    require_once __DIR__."/change_resource_type.php";
    return appReturn($app, changeResourceType($app["config.user"], $app["config.api_key"],
        $rid, $request->request->get("typeID")), false);
});

$app->get($AP."/resource/fields/additional-types", function(Request $request) use($app) {
    require_once __DIR__."/get-additional-resource-types.php";
    return appReturn($app, getAdditionalResourceTypes($app["config.user"], $app["config.api_key"]), false);
});

?>
