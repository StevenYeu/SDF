<?php

function termExists($user, $api_key, $label, $uid){
    $dbObj = new DbObj();
    return Term::getByLabelUid($dbObj, $label, $uid);
}

?>