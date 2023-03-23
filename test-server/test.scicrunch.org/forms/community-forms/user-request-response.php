<?php

require_once "../../classes/classes.php";
\helper\scicrunch_session_start();

$status = \helper\aR($_GET["status"], "s");

if(isset($_GET["idstring"])) {
    $owner_verify_string = \helper\aR($_GET["idstring"], "s");
    $result = CommunityAccessRequest::verifyString($owner_verify_string, $status);

    if($result["message"] == "expired string") {
        header("location: " . "/" . $result["community"]->portalName . "/about/join-request-response-expired");
        exit;
    }
    header("location: " . "/" . $result["community"]->portalName . "/about/join-request-response");
} else {
    if(!isset($_SESSION["user"])) header("location: " . $_SERVER["HTTP_REFERER"]);
    $user = $_SESSION["user"];
    $request_id = \helper\aR($_GET["id"], "i");
    $request = CommunityAccessRequest::loadBy(Array("id"), Array($request_id));

    $community = new Community();
    $community->getByID($request->cid);
    if(!$community->id || $community->uid !== $user->id) header("location: " . $_SERVER["HTTP_REFERER"]);

    if(is_null($request)) header("location: " . $_SERVER["HTTP_REFERER"]);

    if($status == CommunityAccessRequest::STATUS_APPROVED) {
        $request->approve();
    } elseif($status == CommunityAccessRequest::STATUS_REJECTED) {
        $request->reject();
    } elseif($status == CommunityAccessRequest::STATUS_UNDER_REVIEW) {
        $request->under_review();
    }
    header("location: " . $_SERVER["HTTP_REFERER"]);
}

?>
