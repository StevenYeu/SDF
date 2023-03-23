<?php

function getResourceTypes($user, $api_key) {
    if(!\APIPermissionActions\checkAction("get-resource-types", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    if(is_null($cuser)) {
        $visible_communities = Array(0 => true);
    } else {
        $visible_communities = $cuser->visibleCommunities();
    }

    $rt_holder = new Resource_Type();
    $resource_types = $rt_holder->getAll();

    $rf_holder = new Resource_Fields();

    $return_types = Array();
    foreach($resource_types as $rt) {
        if(!isset($visible_communities[$rt->cid])) continue;
        $return_type = Array();
        $return_type["name"] = $rt->name;
        $return_type["cid"] = $rt->cid;
        $return_type["id"] = $rt->id;

        $fields = $rf_holder->getByType($rt->id, $rt->cid);
        $return_fields = Array();
        foreach($fields as $field) {
            $return_field = Array();
            $return_field["name"] = $field->name;
            $return_field["required"] = $field->required;
            $return_field["type"] = $field->type;
            $return_field["display"] = $field->display;
            $return_field["alt"] = $field->alt;
            $return_fields[] = $return_field;
        }
        $return_type["fields"] = $return_fields;
        $return_types[] = $return_type;
    }

    return APIReturnData::build($return_types, true);
}

function getSingleResourceType($user, $api_key, $id) {
    if(!\APIPermissionActions\checkAction("get-resource-types", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    if(is_null($cuser)) {
        $visible_communities = Array(0 => true);
    } else {
        $visible_communities = $cuser->visibleCommunities();
    }
    
    $rt = new Resource_Type();
    $rt->getByID($id);

    if(!isset($visible_communities[$rt->cid])) return APIReturnData::quick403();

    $return_type = Array();
    $return_type["name"] = $rt->name;
    $return_type["cid"] = $rt->cid;
    $return_type["id"] = $rt->id;

    $rf_holder = new Resource_Fields();
    $fields = $rf_holder->getByType($rt->id, $rt->cid);
    $return_fields = Array();
    foreach($fields as $field) {
        $return_field = Array();
        $return_field["name"] = $field->name;
        $return_field["required"] = $field->required;
        $return_field["type"] = $field->type;
        $return_field["display"] = $field->display;
        $return_field["alt"] = $field->alt;
        $return_fields[] = $return_field;
    }
    $return_type["fields"] = $return_fields;

    return APIReturnData::build($return_type, true);
}

?>
