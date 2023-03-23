<?php

$docroot = "..";
require_once $docroot . "/classes/classes.php";
require_once $docroot . "/api-classes/update_subscription.php";

$cxn = new Connection();
$cxn->connect();
$saved_ids = $cxn->select("saved_searches", Array("id"), "", Array(), "");
$cxn->close();

if(count($saved_ids) === 0) exit;
foreach($saved_ids as $si) {
    $saved_search = new Saved();
    /* saved searches dont work for dkNET 3.0 yet, skip for now */
    $community = new Community();
    $community->getByID($saved_search->cid);
    if($community->rinStyle()) {
        continue;
    }
    $saved_search->getByID($si["id"]);
    if(!$saved_search->id) throw new Exception("invalid saved_searches ID");

    $sub = Subscription::loadBy(Array("type", "fid"), Array($saved_search->nifServicesType(), $saved_search->id));
    if(is_null($sub)) {
        $user = getUser($saved_search->uid);
        if(!$user->id) continue;
        $result = updateSubscription($user, NULL, "subscribe", $saved_search->nifServicesType(), $saved_search->id);
        if($result->success === false) throw new Exception("could not create missing subscription");
        $sub = $result->data;
    }

    $sub->searchNewData();
}

/******************************************************************************************************************************************************************************************************/

function getUser($uid) {
    $user = new User();
    $user->getByID($uid);
    return $user;
}

?>
