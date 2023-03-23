<?php

function getTermAnnotation($user, $api_key, $id){
    $dbObj = new DbObj();

    $ta = new TermAnnotation($dbObj);
    $ta->getById($id);

    $return_values = Array();
    foreach(TermAnnotation::$properties as $name){
        $return_values[$name] = $ta->$name;
    }

    return $return_values;
}

?>
