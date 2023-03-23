<?php

function getAnnotationTermList($user, $api_key){
    $dbObj = new DbObj();

    return Term::getAnnotationTermList($dbObj);
}

?>
