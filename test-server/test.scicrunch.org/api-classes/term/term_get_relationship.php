<?php

function getTermRelationship($user, $api_key, $id){
    $dbObj = new DbObj();

    $tr = new TermRelationship($dbObj);
    $tr->getById($id);

    //clean up data before return
    $return_values = Array();
    foreach(TermRelationship::$properties as $name){
        $return_values[$name] = $tr->$name;
    }

    return $return_values;
}

?>
