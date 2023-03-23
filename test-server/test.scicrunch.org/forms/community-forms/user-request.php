<?php

require_once "../../classes/classes.php";
\helper\scicrunch_session_start();

$cid = $_POST["cid"];

/* if no user, return */
if(!isset($_SESSION["user"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}
$user = $_SESSION["user"];

/* if cannot find community, return */
$community = new Community();
$community->getByID($cid);
if(!$community->id) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

$message = $_POST["message"];
$message = \helper\sanitizeHTMLString($message);


$new_request = CommunityAccessRequest::createNewObj($user, $community, $message);
header("location: /" . $community->portalName . "/about/join-request-confirm");

?>
