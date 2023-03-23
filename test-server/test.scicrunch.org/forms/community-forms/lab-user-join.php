<?php

require_once __DIR__ . "/../../classes/classes.php";
session_start();

require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/labs.php";

if(!isset($_SESSION["user"]) || !isset($_GET["request"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

$user = $_SESSION["user"];
$labid = \helper\aR($_GET["request"], "i");

joinLab($user, NULL, $labid);

$go_location = $_SERVER["HTTP_REFERER"];

// send back "message=sent" so that referer page can show alert message.
// assuming came from "list" page. If not, then need to add JS to other page
if (isset($_GET['labid']))
    $go_location = $_SERVER["HTTP_REFERER"] . "&message=sent";
else
    $go_location = $_SERVER["HTTP_REFERER"] . "?message=sent";

header("location: " . $go_location);

?>
