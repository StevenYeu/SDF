<?php

function getTermVersion($user, $api_key, $tid, $version){
    $tv = getVersionInfo($tid, $version);
    return $tv;
}

function getVersionInfo($tid, $version){
    $dbObj = new DbObj();
    $tv = new TermVersion($dbObj);
    $tvInfo = $tv->getByTidVersion($tid, $version);

    $arr = json_decode($tvInfo['term_info']);
    foreach ($arr->superclasses as $a){
        $term = new Term($dbObj);
        $term->getById($a->id);
        $a->ilx = $term->ilx;
        $a->label = $term->label;
        $a->type = $term->type;
    }
    //print_r($arr);
    $tvInfo['term_info'] = json_encode($arr);

    return $tvInfo;
}

?>
