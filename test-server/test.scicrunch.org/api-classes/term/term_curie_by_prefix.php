<?php

function getTermCurieByPrefix($user, $api_key, $prefix){
    $dbObj = new DbObj();

    $cc = CurieCatalog::getByPrefix($dbObj, $prefix);
    return $cc;
}

?>