<?php

require_once __DIR__ . "/../classes/classes.php";
\helper\scicrunch_session_start();

$key_val = $_GET["key"];

$key = ActionKey::loadByKey($key_val);

if(is_null($key)) {
    exitRedirect();
}

if($key->type == "email-unsubscribe") {

    $user = new User();
    $user->getByID($key->fkey);
    if(!$user->id) {
        exitRedirect();
    }
    $user->unsubscribeAllEmail();
    ActionKey::deleteObj($key);
    exitRedirect("email-unsubscribe");

} elseif($key->type == "lab-create") {

    if(!isset($_GET["approve"])) {
        exitRedirect();
    }
    $approve = $_GET["approve"] == "true" ? true : false;
    $lab = Lab::loadBy(Array("id"), Array($key->fkey));
    if(is_null($lab) || $lab->curated != Lab::CURATED_STATUS_PENDING) {
        exitRedirect();
    }
    if($approve) {
        $review = Lab::CURATED_STATUS_APPROVED;
    } else {
        $review = Lab::CURATED_STATUS_REJECTED;
    }
    $lab->approveLab($review);
    ActionKey::deleteObj($key);
    exitRedirect("lab-create");

} elseif($key->type == "lab-join") {

    if(!isset($_GET["approve"])) {
        exitRedirect();
    }
    $approve = $_GET["approve"] == "true" ? true : false;
    $lab_membership = LabMembership::loadBy(Array("id"), Array($key->fkey));
    if(is_null($lab_membership) || $lab_membership->level != 0) {
        exitRedirect();
    }
    if($approve) {
        $lab_membership->approve();
    } else {
        $lab_membership->reject();
    }
    ActionKey::deleteObj($key);
    exitRedirect("lab-join");

}

function exitRedirect($type = "", $extra_args = NULL) {
    if($type !== "") {
        $type = "?actiontype=" . $type;
        if(!is_null($extra_args)) {
            $targs = Array();
            foreach($extra_args as $key => $val) {
                $targs[] = $key . "=" . $val;
            }
            $type .= "&" . implode("&", $targs);
        }
    }
    header("location: " . PROTOCOL . "://" . FQDN . "/scicrunch/about/keyaction" . $type);
    exit;
}
?>
