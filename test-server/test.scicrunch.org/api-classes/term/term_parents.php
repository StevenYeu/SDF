<?php

function getTermParents($user, $api_key, $term_id){
    $dbObj = new DbObj();

    $term = new Term($dbObj);
    $term->getById($term_id);
    $term->getAncestors();

    return ($term->ancestors);
}

?>
