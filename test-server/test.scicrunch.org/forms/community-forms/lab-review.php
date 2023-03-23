<?php

require_once __DIR__ . "/../../classes/classes.php";
session_start();

require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/labs.php";

if(!isset($_SESSION["user"]) || !isset($_GET["labid"]) || !isset($_GET["review"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

$user = $_SESSION["user"];
$labid = \helper\aR($_GET["labid"], "i");
$review = \helper\aR($_GET["review"], "s");

reviewLab($user, NULL, $labid, $review);

header("location: " . $_SERVER["HTTP_REFERER"]);

?>
