<?php

function termSearch($user, $api_key, $searchTerm){
    $searchTerm = trim($searchTerm);

    $dbObj = new DbObj();
    $term = new Term($dbObj);

    // DEPRECATED
    $return = null;
    if (!isset($searchTerm) || sizeof($searchTerm) == 0 || $searchTerm === '*') {
        $return = $term->getTermList();
    } else {
    $return = $term->searchTerm($searchTerm);
    }

    // getTermList should never be used. Server can no longer handle it.
    // If you need all terms, just pull directly from the SQL database.
    // $return = $term->searchTerm($searchTerm);

    return $return;
}



?>
