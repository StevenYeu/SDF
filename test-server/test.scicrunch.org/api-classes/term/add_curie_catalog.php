<?php

function addToCurieCatalog($user, $api_key, $fields){
    $dbObj = new DbObj();
    if(!\APIPermissionActions\getUser($api_key, $user)) return "not allowed";

    $cc = new CurieCatalog($dbObj);
    //make sure not duplicate
    $cc->getByUserPrefix($user->id, $prefix);
    if ($cc->id > 0) {
        return array('duplicate'=>'Curie catalog entry (' . $cc->prefix . ') already exists','id'=>$cc->id);
        exit;
    }

    $cc->uid = $user->id;
    foreach ($fields as $key => $value){
        if ($key == 'uid') { continue; }

        $cc->$key = $value;
    }
    $cc->insertDB();

    $return_values = array();
    foreach(CurieCatalog::$properties as $name){
        $return_values[$name] = $cc->$name;
    }

    return $return_values;
}

?>
