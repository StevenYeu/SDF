<?php

function getAllResourceRelationshipTypes(){
    $types = ResourceRelationshipString::loadArrayBy(Array(), Array());
    return APIReturnData::build($types, true);
}

?>
