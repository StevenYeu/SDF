<?php

require_once __DIR__ . "/../../classes/classes.php";
session_start();

require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/labs.php";

if(!isset($_SESSION["user"]) || !isset($_POST["portal_name"]) || !isset($_POST["private_description"]) || !isset($_POST["public_description"]) || !isset($_POST["name"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

$user = $_SESSION["user"];
$portal_name = \helper\aR($_POST["portal_name"], "s");
$name = \helper\aR($_POST["name"], "s");
$private_description = \helper\aR($_POST["private_description"], "s");
$public_description = \helper\aR($_POST["public_description"], "s");

$api_return = createLab($user, NULL, $portal_name, $name, $private_description, $public_description);

if($api_return->success) {
    $lab = $api_return->data;
    $community = new Community();
    $community->getByID($lab->cid);
    header("location: /" . $community->portalName . "/lab?labid=" . $lab->id . "&submitted");
} else {
    $referer = explode("?", $_SERVER["HTTP_REFERER"])[0];
    header("location: " . $_SERVER["HTTP_REFERER"] . "?error");
}

?>
