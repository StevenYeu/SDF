<?php

function getAllTypes($user, $api_key) {
    if(!\APIPermissionActions\checkAction("get-scicrunch-data", $api_key, $user)) return APIReturnData::quick403();
    $names = array_keys(\helper\queryableClasses());
    return APIReturnData::build($names, true);
}

function getSingleType($user, $api_key, $type, $query, $options) {
    if(!\APIPermissionActions\checkAction("get-scicrunch-data", $api_key, $user)) return APIReturnData::quick403();
    $qc = \helper\queryableClasses();
    if(!isset($qc[$type])) return APIReturnData::quick400("type unavailable");
    $classname = $qc[$type]["classname"];
    $field_names = $classname::getFields();

    $query_fields = Array();
    $query_values = Array();
    $numeric = is_numeric($query);
    if($query) {
        foreach($field_names as $fn) {
            if(!$numeric && $classname::getFields($fn)["type"] !== "s") {
                continue;
            }
            $query_fields[] = $fn;
            $query_values[] = $query;
        }
        $options["fuzzy"] = true;
        $options["or-all"] = true;
    }

    $results = $classname::loadArrayBy($query_fields, $query_values, $options);
    $results_count = $classname::getCount($query_fields, $query_values, $options);

    $results_array = Array();
    foreach($results as $r) {
        $t = Array();
        foreach($field_names as $fn) {
            $t[] = Array("name" => $fn, "value" => $r->display($fn));
        }
        $results_array[] = $t;
    }

    return APIReturnData::build(Array(
        "count" => $results_count,
        "results" => $results_array,
    ), true);
}

?>
