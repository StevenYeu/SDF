<?php

function getTermById($user, $api_key, $term_id){
    $dbObj = new DbObj();
    $term = new Term($dbObj);
    $term->getById($term_id);

    $term->getExistingIds();
    $term->getSynonyms();
    $term->getSuperclasses();
    $term->getOntologies();
    if ($term->type == 'annotation') {
        $term->getAnnotationType();
    }

    return DbObj::printableTerm($term);
}

?>
