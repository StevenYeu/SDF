<?php

function getAllResourceRelationships($user, $api_key, $scrid, $count=NULL, $offset=NULL, $canon_only=false, $array_formatted=false){
    if(is_null($count)) $count = MAXINT;
    if(is_null($offset)) $offset = 0;

    $results = ResourceRelationship::loadByID($scrid, $offset, $count, $canon_only);

    return APIReturnData::build($results, true);
}

function getByTypeResourceRelationships($user, $api_key, $rid, $type) {
    $relationship_type = ResourceRelationshipString::loadBy(Array("forward", "reverse"), Array($type, $type), Array("or-all" => true));
    if(is_null($relationship_type)) {
        return APIReturnData::quick400("could not find relationship type");
    }

    if($relationship_type->forward == $type) {
        $id_string = "id1";
    } else {
        $id_string = "id2";
    }

    $relationships = ResourceRelationship::loadArrayBy(Array("reltype_id", $id_string), Array($relationship_type->id, $rid));

    return APIReturnData::build($relationships, true);
}

function getResourceRelationshipsCount($user, $api_key, $scrid, $canon_only=false) {
    $relCount = ResourceRelationship::getCountByID($scrid, $canon_only);
    return APIReturnData::build($relCount, true);
}

?>
