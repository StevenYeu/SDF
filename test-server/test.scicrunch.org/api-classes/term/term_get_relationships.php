<?php

function getTermRelationships($user, $api_key, $tid){
    $dbObj = new DbObj();

    $term = new Term($dbObj);
    $term->getById($tid);
    $term->getRelationships();

    return $term->relationships;
}

?>
