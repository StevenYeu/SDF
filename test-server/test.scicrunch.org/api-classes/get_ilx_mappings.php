<?php

function getMappingsByCurie($user, $api_key, $curie) {
    if(!\APIPermissionActions\checkAction("get-term-mappings", $api_key, $user)) {
        return APIReturnData::quick403();
    }

    $mapping = TermMappingDBO::loadBy(
        Array("existing_id", "concept_id"),
        Array($curie, $curie),
        Array("or-all" => true)
    );

    if(is_null($mapping)) {
        return APIReturnData::build(Array(), true);
    }

    $all_mappings = TermMappingDBO::loadArrayBy(Array("tid"), Array($mapping->tid));

    return APIReturnData::build($all_mappings, true);
}

function getMappingsByValue($user, $api_key, $value) {
    if(!\APIPermissionActions\checkAction("get-term-mappings", $api_key, $user)) {
        return APIReturnData::quick403();
    }

    $mapping = TermMappingDBO::loadBy(Array("matched_value"), Array($value));

    if(is_null($mapping)) {
        return APIReturnData::build(Array(), true);
    }

    $all_mappings = TermMappingDBO::loadArrayBy(Array("tid"), Array($mapping->tid));

    return APIReturnData::build($all_mappings, true);
}

?>
