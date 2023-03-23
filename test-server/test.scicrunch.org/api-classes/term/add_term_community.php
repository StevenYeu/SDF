<?php

function addTermCommunity($user, $api_key, $tid, $fields){
    $dbObj = new DbObj();
    if(!\APIPermissionActions\getUser($api_key, $user)) return "not allowed";

    foreach(TermCommunity::$required as $req){
        if(!isset($fields[$req]) || $fields[$req] == NULL || $fields[$req] == ''){
            return "missing required field: " . $req;
        }
    }

    $tc = new TermCommunity($dbObj);
    $tc->tid = $tid;
    $tc->cid = $fields['cid'];
    $tc->uid_suggested = $user->id;
    $tc->status = 'suggested';
    $tc->scicrunch_maintainer = 1;

    $tc->insertDB();

    return $tc->forPrint();
}

?>
