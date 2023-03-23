<?php

require_once __DIR__ . "/../../classes/classes.php";
session_start();

require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/labs.php";

if(!isset($_SESSION["user"]) || !isset($_POST["labid"]) || !isset($_POST["uid"]) || !isset($_POST["review"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

$user = $_SESSION["user"];
$labid = \helper\aR($_POST["labid"], "i");
$uid = \helper\aR($_POST["uid"], "i");
$review = (int) \helper\aR($_POST["review"], "i");

reviewUserLab($user, NULL, $labid, $uid, $review);

header("location: " . $_SERVER["HTTP_REFERER"]);

?>
