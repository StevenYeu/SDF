<?php

function termLookup($user, $api_key, $label){
    $dbObj = new DbObj();
    $term = new Term($dbObj);
    $term->getByLabel($label);

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
