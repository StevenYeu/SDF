<?php
require_once "term_by_id.php";

function getTermByCurie($user, $api_key, $curie){
    $dbObj = new DbObj();
    $term = new Term($dbObj);
    $tid = $term->getTidByCurie($curie);
    $term = getTermById($user, $api_key, $tid);
    return $term;
}

?>
