<?php

function ilxSearch($user, $api_key, $search_type, $query){
    if($search_type === "identifier") return APIReturnData::build(IlxIdentifier::loadBy(Array("fragment"), Array($query)), true);
    elseif($search_type === "term") return APIReturnData::build(IlxIdentifier::loadArrayBy(Array("term"), Array($query), true), true);
}

?>
