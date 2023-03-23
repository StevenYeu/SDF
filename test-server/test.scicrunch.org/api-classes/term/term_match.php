<?php

function termMatch($user, $api_key, $label){
    $terms = getTermMatches($label);

    $return_values = Array();
    foreach ($terms as $term) {
        $return_values[] = DbObj::printableTerm($term);
    }

    return $return_values;
}

function getTermMatches($label){
    $dbObj = new DbObj();
    $terms = array();

    $array = Term::getMatches($dbObj, $label);

    for ($i=0; $i < count($array); $i++) {
        $term = new Term($dbObj);
        $term->getById($array[$i]['id']);

//         $term->getExistingIds();
//         $term->getSynonyms();
//         $term->getSuperclasses();

         $terms[] = $term;
    }

    return $terms;
}

?>
