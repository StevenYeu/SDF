<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

/**
 *
 * @SWG\Definition(
 *   definition="entityMapping",
 *   type="object",
 *   @SWG\Property(property="source", type="string"),
 *   @SWG\Property(property="table_name", type="string"),
 *   @SWG\Property(property="col", type="string"),
 *   @SWG\Property(property="value", type="string"),
 *   @SWG\Property(property="identifier", type="string"),
 *   @SWG\Property(property="external_id", type="string"),
 *   @SWG\Property(property="relation", type="string"),
 *   @SWG\Property(property="match_substring", type="integer"),
 *   @SWG\Property(property="curation_status", type="string"),
 *   @SWG\Property(property="timestamp", type="integer"),
 *   @SWG\Property(property="status", type="string"),
 * )
 *
 * @SWG\Definition(
 *   definition="apiReturn_array_entityMapping",
 *   type="object",
 *   @SWG\Property(property="data", type="array", @SWG\Items(ref="#/definitions/entityMapping")),
 *   @SWG\Property(property="success", type="boolean"),
 * )
 *
 * @SWG\Definition(
 *   definition="apiReturn_entityMapping",
 *   type="object",
 *   @SWG\Property(property="data", ref="#/definitions/entityMapping"),
 *   @SWG\Property(property="success", type="boolean"),
 * )
 *
 */


/**
 *  @SWG\Get( path="/entitymapping/bysource/{source}/{value}", summary="Get an entity mapping by source and value", tags={"Entity Mapping"},
 *      @SWG\Parameter(name="source", description="The ID of the source", in="path", required=true, type="string" ),
 *      @SWG\Parameter(name="value", description="value being mapped to", in="path", required=true, type="string" ),
 *      @SWG\Parameter(name="key", description="user", in="query", required=true, type="string" ),
 *      @SWG\Response(response="200", ref="#/definitions/apiReturn_array_entityMapping"),
 *      @SWG\Response(response="default", ref="#/definitions/errorObject"),
 *  )
 **/
$app->get($AP."/entitymapping/bysource/{source}/{value}", function(Request $request, $source, $value) use($app) {
    require_once __DIR__."/get_entitymapping.php";
    $source = aR($source, "s");
    $value = aR($value, "s");

    return appReturn($app, getEntityMapping($app["config.user"], $app["config.api_key"], $source, NULL, NULL, $value), false, true);
});

/**
 *  @SWG\Get( path="/entitymapping/bycolumn/{source}/{table}/{column}/{value}", summary="Get an entity mapping by source", tags={"Entity Mapping"},
 *      @SWG\Parameter(name="source", description="The ID of the source", in="path", required=true, type="string" ),
 *      @SWG\Parameter(name="table", description="The table in the source", in="path", required=true, type="string" ),
 *      @SWG\Parameter(name="column", description="The column in the table", in="path", required=true, type="string" ),
 *      @SWG\Parameter(name="value", description="value being mapped to", in="path", required=true, type="string" ),
 *      @SWG\Response(response="200", ref="#/definitions/apiReturn_array_entityMapping"),
 *      @SWG\Response(response="default", ref="#/definitions/errorObject"),
 *  )
 **/
$app->get($AP."/entitymapping/bycolumn/{source}/{table}/{column}/{value}", function(Request $request, $source, $table, $column, $value) use ($app) {
    require_once __DIR__."/get_entitymapping.php";
    $source = aR($source, "s");
    $table = aR($table, "s");
    $column = aR($column, "s");
    $value = aR($value, "s");

    return appReturn($app, getEntityMapping($app["config.user"], $app["config.api_key"], $source, $table, $column, $value), false, true);
});

/**
 *  @SWG\Post( path="/entitymapping/add/{source}/{table}/{column}/{value}", summary="Add an entity mapping", tags={"Entity Mapping"},
 *      @SWG\Parameter(name="source", description="The ID of the source", in="path", required=true, type="string" ),
 *      @SWG\Parameter(name="table", description="The table in the source", in="path", required=true, type="string" ),
 *      @SWG\Parameter(name="column", description="The column in the table", in="path", required=true, type="string" ),
 *      @SWG\Parameter(name="value", description="value being mapped to", in="path", required=true, type="string" ),
 *      @SWG\Parameter(name="identifier", description="", in="formData", required=true, type="string" ),
 *      @SWG\Parameter(name="external_id", description="", in="formData", required=false, type="string" ),
 *      @SWG\Parameter(name="relation", description="", in="formData", required=false, type="string" ),
 *      @SWG\Parameter(name="match_substring", description="", in="formData", required=false, type="boolean" ),
 *      @SWG\Parameter(name="status", description="", in="formData", required=false, type="string" ),
 *      @SWG\Parameter(name="key", description="user", in="formData", required=true, type="string" ),
 *      @SWG\Response(response="201", ref="#/definitions/apiReturn_entityMapping"),
 *      @SWG\Response(response="default", ref="#/definitions/errorObject"),
 *  )
 **/


$app->post($AP."/entitymapping/add/{source}/{table}/{column}/{value}", function(Request $request, $source, $table, $column, $value) use ($app) {
    require_once __DIR__."/add_entitymapping.php";
    $source = aR($source, "s");
    $table = aR($table, "s");
    $column = aR($column, "s");
    $value = aR($value, "s");
    $identifier = aR($request->request->get("identifier"), "s");
    if(is_null($identifier)) return $app->json("identifier required", 400);
    $external_id = aR($request->request->get("external_id"), "s");
    $relation = aR($request->request->get("relation"), "s");
    $match_substring = aR($request->request->get("match_substring"), "i");
    $status = aR($request->request->get("status"), "s");

    return appReturn($app, addEntityMapping($app["config.user"], $app["config.api_key"], $source, $table, $column, $value, $identifier, $external_id, $relation, $match_substring, $status), true);
});

/**
 *  @SWG\Post( path="/entitymapping/update/{source}/{table}/{column}/{value}/{identifier}", summary="Update an entity mapping", tags={"Entity Mapping"},
 *      @SWG\Parameter(name="source", description="The ID of the source", in="path", required=true, type="string" ),
 *      @SWG\Parameter(name="table", description="The table in the source", in="path", required=true, type="string" ),
 *      @SWG\Parameter(name="column", description="The column in the table", in="path", required=true, type="string" ),
 *      @SWG\Parameter(name="value", description="value being mapped to", in="path", required=true, type="string" ),
 *      @SWG\Parameter(name="identifier", description="", in="path", required=true, type="string" ),
 *      @SWG\Parameter(name="identifier", description="", in="formData", required=false, type="string" ),
 *      @SWG\Parameter(name="relation", description="", in="formData", required=false, type="string" ),
 *      @SWG\Parameter(name="match_substring", description="", in="formData", required=false, type="boolean" ),
 *      @SWG\Parameter(name="status", description="", in="formData", required=false, type="string" ),
 *      @SWG\Parameter(name="external_id", description="", in="formData", required=false, type="string" ),
 *      @SWG\Parameter(name="key", description="user", in="formData", required=true, type="string" ),
 *      @SWG\Response(response="200", ref="#/definitions/apiReturn_entityMapping"),
 *      @SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->post($AP."/entitymapping/update/{source}/{table}/{column}/{value}/{identifier}", function(Request $request, $source, $table, $column, $value, $identifier) use ($app) {
    require_once __DIR__."/update_entitymapping.php";
    $source = aR($source, "s");
    $table = aR($table, "s");
    $column = aR($column, "s");
    $value = aR($value, "s");
    $identifier = aR($identifier, "s");

    $updates = Array();
    $updates['relation'] = aR($request->request->get("relation"), "s");
    $updates['match_substring'] = aR($request->request->get("match_substring"), "i");
    $updates['status'] = aR($request->request->get("status"), "s");
    $updates['identifier'] = aR($request->request->get("identifier"), "s");
    $updates['external_id'] = aR($request->request->get("external_id"), "s");

    return appReturn($app, updateEntityMapping($app["config.user"], $app["config.api_key"], $source, $table, $column, $value, $identifier, $updates), true);
});

/**
 *  SWG\Post( path="/entitymapping/curate/{source}/{table}/{column}/{value}/{identifier}", tags={"Entity Mapping"},
 *      SWG\Parameter(name="source", description="The ID of the source", in="path", required=true, type="string" ),
 *      SWG\Parameter(name="table", description="The table in the source", in="path", required=true, type="string" ),
 *      SWG\Parameter(name="column", description="The column in the table", in="path", required=true, type="string" ),
 *      SWG\Parameter(name="value", description="value being mapped to", in="path", required=true, type="string" ),
 *      SWG\Parameter(name="identifier", description="", in="path", required=true, type="string" ),
 *      SWG\Parameter(name="curation_status", description="", in="formData", required=true, type="string" ),
 *      SWG\Parameter(name="key", description="curator", in="formData", required=true, type="string" ),
 *      SWG\Response(response="200", ref="#/definitions/apiReturn_entityMapping"),
 *      SWG\Response(response="default", ref="#/definitions/errorObject"),
 *  )
 **/
$app->post($AP."/entitymapping/curate/{source}/{table}/{column}/{value}/{identifier}", function(Request $request, $source, $table, $column, $value, $identifier) use ($app) {
    require_once __DIR__."/curate_entitymapping.php";
    $source = aR($source, "s");
    $table = aR($table, "s");
    $column = aR($column, "s");
    $value = aR($value, "s");
    $identifier = aR($identifier, "s");

    $curation_status = aR($request->request->get("curation_status"), "s");
    if(is_null($curation_status)) return $app->json("curation_status is required", 400);

    return appReturn($app, curateEntityMapping($app["config.user"], $app["config.api_key"], $source, $table, $column, $value, $identifier, $curation_status), true);
});

/**
 *  @SWG\Get( path="/entitymapping/byvaluelist", summary="Get a list of entities by value separated by a delimiter", tags={"Entity Mapping"},
 *      @SWG\Parameter( name="list", description="list of values separated by a delimiter", in="query", required=true, type="string" ),
 *      @SWG\Parameter( name="delimiter", description="", in="query", required=true, type="string" ),
 *      @SWG\Response(response="200", ref="#/definitions/apiReturn_array_entityMapping"),
 *      @SWG\Response(response="default", ref="#/definitions/errorObject"),
 *  )
 *
 **/
$app->get($AP."/entitymapping/byvaluelist", function(Request $request) use ($app) {
    require_once __DIR__."/get_entitymapping.php";
    $value_list = $request->query->get("list");
    $delim = $request->query->get("delimiter");
    $values = explode($delim, $value_list);
    $return_list = Array();
    foreach($values as $val){
        $found_ents = getEntityMapping($app["config.user"], $app["config.api_key"], NULL, NULL, NULL, $val);
        if($found_ents->success){
            foreach($found_ents->data as $fe){
                $return_list[] = $fe->arrayForm();
            }
        }
    }
    return $app->json($return_list, 200);
});

/**
 *  @SWG\Post(path="/entitymapping/add", summary="Add an entity mapping", tags={"Entity Mapping"},
 *      @SWG\Parameter(name="source", description="The ID of the source", in="formData", required=true, type="string"),
 *      @SWG\Parameter(name="table", description="The table in the source", in="formData", required=true, type="string"),
 *      @SWG\Parameter(name="column", description="The column in the table", in="formData", required=true, type="string"),
 *      @SWG\Parameter(name="value", description="The value being mapped to", in="formData", required=true, type="string"),
 *      @SWG\Parameter(name="identifier", description="", in="formData", required=true, type="string"),
 *      @SWG\Parameter(name="external_id", description="The table in the source", in="formData", required=false, type="string"),
 *      @SWG\Parameter(name="relation", description="", in="formData", required=false, type="string"),
 *      @SWG\Parameter(name="match_substring", description="", in="formData", required=false, type="boolean"),
 *      @SWG\Parameter(name="status", description="", in="formData", required=false, type="string"),
 *      @SWG\Parameter(name="key", description="", in="formData", required=false, type="string"),
 *      @SWG\Response(response="201", ref="#/definitions/apiReturn_entityMapping"),
 *      @SWG\Response(response="default", ref="#/definitions/errorObject"),
 *  )
 **/
$app->post($AP."/entitymapping/add", function(Request $request) use ($app) {
    require_once __DIR__."/add_entitymapping.php";
    $source = aR($request->request->get("source"), "s");
    $table = aR($request->request->get("table"), "s");
    $column = aR($request->request->get("column"), "s");
    $value = aR($request->request->get("value"), "s");
    $identifier = aR($request->request->get("identifier"), "s");
    if(is_null($identifier) || is_null($source) || is_null($table) || is_null($column) || is_null($value)) return $app->json("identifier, source, table, column and value required", 400);
    $external_id = aR($request->request->get("external_id"), "s");
    $relation = aR($request->request->get("relation"), "s");
    $match_substring = aR($request->request->get("match_substring"), "i");
    $status = aR($request->request->get("status"), "s");

    return appReturn($app, addEntityMapping($app["config.user"], $app["config.api_key"], $source, $table, $column, $value, $identifier, $external_id, $relation, $match_substring, $status), true);
});

/**
 *  @SWG\Get( path="/entitymapping/list", summary="Get a list of entity mapping sources", tags={"Entity Mapping"},
 *      @SWG\Response(response="200", ref="#/definitions/apiReturn_array_string"),
 *      @SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->get($AP."/entitymapping/list", function(Request $request) use($app) {
    require_once __DIR__."/get_entitymapping_list.php";
    return appReturn($app, getEntityMappingSourcesList($app["config.user"], $app["config.api_key"]));
});

/**
 *  @SWG\Get( path="/entitymapping/list/{source}", tags={"Entity Mapping"},
 *      @SWG\Parameter( name="source", description="The ID of the source", in="path", required=true, type="string" ),
 *      @SWG\Response(response="200", ref="#/definitions/apiReturn_array_string"),
 *      @SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->get($AP."/entitymapping/list/{source}", function(Request $request, $source) use($app) {
    require_once __DIR__."/get_entitymapping_list.php";
    return appReturn($app, getEntityMappingTableList($app["config.user"], $app["config.api_key"], $source));
});

/**
 *  @SWG\Get( path="/entitymapping/list/{source}/{table}", tags={"Entity Mapping"},
 *      @SWG\Parameter( name="source", description="The ID of the source", in="path", required=true, type="string" ),
 *      @SWG\Parameter( name="table", description="The table in the source", in="path", required=true, type="string" ),
 *      @SWG\Response(response="200", ref="#/definitions/apiReturn_array_string"),
 *      @SWG\Response(response="default", ref="#/definitions/object"),
 *  )
 **/
$app->get($AP."/entitymapping/list/{source}/{table}", function(Request $request, $source, $table) use($app) {
    require_once __DIR__."/get_entitymapping_list.php";
    return appReturn($app, getEntityMappingColumnList($app["config.user"], $app["config.api_key"], $source, $table));
});

?>
