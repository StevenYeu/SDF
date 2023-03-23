<?php

function getEntityMappingSourcesList($user, $api_key){
    $sources = EntityMapping::getAllSources();
    return APIReturnData::build($sources, true);
}

function getEntityMappingTableList($user, $api_key, $source){
    $tables = EntityMapping::getAllTables($source);
    return APIReturnData::build($tables, true);
}

function getEntityMappingColumnList($user, $api_key, $source, $table){
    $columns = EntityMapping::getAllColumns($source, $table);
    return APIReturnData::build($columns, true);
}

?>
