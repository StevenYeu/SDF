<?php

require_once __DIR__."/get_resource_fields.php";

function editResourceFields($user, $api_key, $scrid, $new_args){
    if(!\APIPermissionActions\checkAction("resource-edit", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);
    $rid = \helper\getIDFromRID($scrid);
    $resource = new Resource();
    $resource->getByID($rid);
    if(!$resource->id) return APIReturnData::build(NULL, false, 400, "resource not found");

    $fields = getTypeFields($resource);

    $raw_args = getArgs($fields, $new_args, $cuser, $resource);

    if($raw_args["validation"] !== true) return APIReturnData::build(NULL, false, 400, $raw_args["validation"]);
    $args = $raw_args["vars"];

    $ver = Array();
    $ver['uid'] = $cuser->id;

    $resource->updateColumns($ver, $args);

    return APIReturnData::build(true, true);
}

function getArgs($fields, $new_args, $user, $resource){
    $vars = Array();
    foreach($new_args as $key => $value){

        if($key=='index_status'){
            //$status = filter_var($value,FILTER_SANITIZE_STRING);
            continue;
        } else {
            $splits = explode('-', $key);
            if (count($splits) > 1) {
                $vars[str_replace('_', ' ', $splits[0])][1] = $value;
            } else {
                if(is_array($value)) {
                    $vars[str_replace('_', ' ', $key)][0] = implode(",", $value);
                } else {
                    $vars[str_replace('_', ' ', $key)][0] = $value;
                }
            }
        }
    }
    $validation = Resource_Fields::validate($vars, $fields, $user, $resource);  // this function mutates $vars
    return Array(
        "vars" => $vars,
        "validation" => $validation
    );
}

?>
