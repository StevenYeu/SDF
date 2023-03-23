<?php

function searchByViewID($user, $api_key, $viewid, $query, $page, $per_page, $sort, $column, $filters) {
    if(!\APIPermissionActions\checkAction("search-elastic", $api_key, $user)) return APIReturnData::quick403();
    $search_manager = ElasticRRIDManager::managerByViewID($viewid);
    if(!$search_manager) {
        return APIReturnData::quick400("invalid viewid");
    }

    if(!$page) {
        $page = 1;
    }
    if(!$per_page || $per_page < 0) {
        $per_page = 20;
    } elseif($per_page > 1000) {
        $per_page = 1000;
    }

    $get_options = Array(
        "filter" => $filters,
        "sort" => $sort,
        "column" => $column,
    );
    $search_options = ElasticRRIDManager::searchOptionsFromGet($get_options);
    $results = $search_manager->search($query, $per_page, $page, $search_options);

    $return = Array(
        "count" => $results->totalCount(),
        "results" => Array(),
        "facets" => $results->facets(),
    );
    $fields = $search_manager->fields();
    foreach($results as $res) {
        $record = Array();
        foreach($fields as $field) {
            $record[$field->name] = $res->getField($field->name);
        }
        $record["v_uuid"] = $res->getRRIDField("uuid");
        $return["results"][] = $record;
    }

    return APIReturnData::build($return, true);
}

?>
