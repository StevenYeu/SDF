<?php

function getTermAnnotations($user, $api_key, $tid){
    $dbObj = new DbObj();

    $term = new Term($dbObj);
    $term->getById($tid);
    $term->getAnnotations();

    return $term->annotations;
}

?>
