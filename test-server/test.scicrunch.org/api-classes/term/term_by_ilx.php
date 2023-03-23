<?php

function getTermByIlx($user, $api_key, $ilx){
    $dbObj = new DbObj();
    $term = new Term($dbObj);
    $term->getByIlx($ilx);

    // Create curie for downstream logic
    $linearray = explode('_', $ilx);
    $curie = strtoupper($linearray[0]) . ':' . $linearray[1];
    $term->curie = $curie;

    $term->getExistingIds();
    $term->getSynonyms();
    $term->getSuperclasses();
    $term->getAnnotations();
    $term->getRelationships();
    $term->getOntologies();
    if ($term->type == 'annotation') {
        $term->getAnnotationType();
    }

    return DbObj::printableTerm($term);
}

?>
