<?php

function getMappingLogs($user, $api_key, $tmid){
    $dbObj = new DbObj();
    $tml = new TermMappingLogs($dbObj);
    return $tml->getByTermMappingId($tmid);
}

?>
