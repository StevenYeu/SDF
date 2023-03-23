<?php

function getCurieCatalog($user, $api_key){
    $dbObj = new DbObj();
    $cc = new CurieCatalog($dbObj);
    $catalog = $cc->getDB();
    return $catalog;
}

?>
