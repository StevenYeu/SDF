<?php

function getTermCommunity($user, $api_key, $tid, $cid){
    $dbObj = new DbObj();
    $tc = new TermCommunity($dbObj);
    $tc->getByTidCid($tid, $cid);

    return $tc->forPrint();
}

?>
