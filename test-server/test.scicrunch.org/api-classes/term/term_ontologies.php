<?php

function getTermOntologies($user, $api_key){
    $dbObj = new DbObj();
    $to = new TermOntology($dbObj);
    $ontologies = $to->getDB();
    return $ontologies;
}

?>
