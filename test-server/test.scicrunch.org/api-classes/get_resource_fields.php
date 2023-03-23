<?php

function getResourceFields($user, $api_key, $scrid, $version){
    $cuser = \APIPermissionActions\getUser($api_key, $user);
    $rid = \helper\getIDFromRID($scrid);
    $resource_info = getResourceInfo($rid, $version);
    $resource = $resource_info['resource'];
    $version = $resource_info['version'];
    $status = $resource_info['status'];
    $image_src = is_null($resource->image) ? NULL : "/upload/resource-images/" . $resource->image;

    if(!$resource->id) return APIReturnData::build(NULL, false, 400, "resource not found");
    $fields = getTypeFields($resource);
    $jfields = jsonifyFields($fields, $resource->columns);
    $return_values = Array(
        "fields" => $jfields,
        "version" => $version,
        "curation_status" => $status,
        "last_curated_version" => $resource->getLastCuratedVersionNum(),
        "scicrunch_id" => $resource->rid,
        "original_id" => $resource->original_id,
        "image_src" => $image_src,
        "uuid" => $resource->uuid,
        "typeID" => $resource->typeID,
    );
    if(!is_null($cuser) && $cuser->role > 0) $return_values["submitter_email"] = $resource->submitterEmail();
    return APIReturnData::build($return_values, true);
}

function getResourceInfo($rid, $version){
    $resource = new Resource();
    $resource->getByID($rid);
    if(is_null($version)) $version = $resource->getLatestVersionNum();
    $resource->getVersionColumns($version);
    $version_info = $resource->getVersionInfo($version);
    return Array("resource" => $resource, "version" => $version, "status" => $version_info['status']);
}

function getTypeFields($resource){
    $type = new Resource_Type();
    $type->getByID($resource->typeID);
    $holder = new Resource_Fields();
    $fields = $holder->getByType($type->id, $resource->cid);
    return $fields;
}

function jsonifyFields($fields, $columns){
    $jfields = Array();
    foreach($fields as $field){
        $value = $columns[$field->name] != "" ? \helper\decodeUTF8($columns[$field->name]) : NULL;
        if($field->type == "resource-types") {
            $value = explode(",", $value);
        }
        $jfield = Array();
        $jfield['field'] = $field->name;
        $jfield['required'] = $field->required == 1 ? true : false;
        $type = explode("__", $field->type);
        $jfield['type'] = $type[0];
        $jfield['max_number'] = count($type) > 1 ? $type[1] : '1';
        $jfield['value'] = $value;
        $jfield['position'] = $field->position;
        $jfield['display'] = $field->display;
        $jfield['alt'] = $field->alt;
        $jfields[] = $jfield;
    }
    return $jfields;
}

?>
