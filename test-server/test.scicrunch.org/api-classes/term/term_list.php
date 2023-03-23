<?php

function getTermList($user, $api_key, $type){
    $dbObj = new DbObj();
    $term = new Term($dbObj);

    if ($type == "term" || $type == "relationship" || $type == "annotation" || $type == "cde" || $type == "TermSet"){
       return $term->getTermListByType($type);
    }

    return $term->getTermList();
}

?>
