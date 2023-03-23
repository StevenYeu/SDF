<?php

function addResource($user, $api_key, $columns, $resource_type_id, $cid){
    if(!\APIPermissionActions\checkAction("add-resource", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    // get the resource type object
    $resource_type = new Resource_Type();
    $resource_type->getByID($resource_type_id);
    if(!$resource_type->id) $resource_type->getByID(1); // default to primary resource type

    // get the community
    $community = new Community();
    $community->getByID($cid);
    if(!$community->portalName) $community->getByID(0); // default to scicrunch community

    // get the other columns submitted by the user
    $vars = Array();
    foreach($columns as $ckey => $cval) {
        $splits = explode("-", $ckey);
        if(count($splits) > 1) $vars[str_replace("_", " ", $splits[0])][1] = $cval;
        else $vars[str_replace("_", " ", $ckey)][0] = $cval;
    }

    // get missing columns belonging to type
    $resource_fields_holder = new Resource_Fields();
    if($resource_type->id != 0) {
        $fields = $resource_fields_holder->getPage2($community->id, $resource_type->id);
        foreach($fields as $field) {
            if(!isset($vars[$field->name])) $vars[$field->name] = Array("", "");
        }
    }

    // make sure all fields are present
    if(!$resource_type->checkValidFields($vars)) return APIReturnData::quick400("missing or bad column fields");

    // create the resource
    $varsR = Array();
    $varsR["uid"] = $cuser->id;
    $varsR["type"] = $resource_type->name;
    $varsR["typeID"] = $resource_type->id;
    $varsR["cid"] = $community->id;
    $resource = new Resource();
    $resource->create($varsR);
    $resource->insertDB();

    // init resource columns array with original id and resource id
    $vars["original_id"] = Array($resource->original_id, NULL);
    $vars["rid"] = Array($resource->rid, NULL);
    
    $resource->columns = $vars;
    $resource->insertColumns2();

    // set the community relationship if available
    $community->addSubmittedBy($resource, $user, $api_key);

    return APIReturnData::build($resource->rid, true, 201);
}

?>
