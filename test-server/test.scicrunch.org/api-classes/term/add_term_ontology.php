<?php

function addToTermOntology($user, $api_key, $url){
    $dbObj = new DbObj();
    if(!\APIPermissionActions\getUser($api_key, $user)) return "not allowed";

    $to = new TermOntology($dbObj);
    //make sure not duplicate
    $to->getByUrl($url);
    if ($to->id > 0) {
        return array('duplicate'=>'Term ontology entry (' . $to->url . ') already exists','id'=>$to->id);
        exit;
    }

    $to->url = $url;
    $to->insertDB();

    $return_values = array();
    foreach(TermOntology::$properties as $n){
        $return_values[$n] = $to->$n;
    }

    return $return_values;
}

?>
