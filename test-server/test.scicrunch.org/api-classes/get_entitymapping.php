<?php

function getEntityMapping($user, $api_key, $source, $table, $column, $value){
    if(is_null($table) && is_null($column)){
        if(is_null($source)) $ents = EntityMapping::loadArrayBy(Array("value"), Array($value));
        else $ents = EntityMapping::loadArrayBy(Array("source", "value"), Array($source, $value));
    }else{
        $ents = EntityMapping::loadArrayBy(Array("source", "table_name", "col", "value"), Array($source, $table, $column, $value));
    }

    return APIReturnData::build($ents, true);
}

?>
