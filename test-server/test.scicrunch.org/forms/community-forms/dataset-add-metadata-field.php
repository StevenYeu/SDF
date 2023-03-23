<?php

require_once __DIR__ . "/../../classes/classes.php";
session_start();

require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/datasets.php";

if(!isset($_SESSION["user"]) || !isset($_POST["cid"]) || !isset($_POST["name"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

$user = $_SESSION["user"];
$cid = \helper\aR($_POST["cid"], "i");
$name = \helper\aR($_POST["name"], "s");

addMetadataField($user, NULL, $cid, $name);

header("location: " . $_SERVER["HTTP_REFERER"] . "#datasets");

?>
