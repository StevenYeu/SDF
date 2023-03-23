<?php

require_once __DIR__ . "/../../classes/classes.php";
session_start();

require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/datasets.php";


if(!isset($_SESSION["user"]) || !isset($_GET["cid"]) || !isset($_GET["name"]) || !isset($_GET["direction"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

$user = $_SESSION["user"];
$cid = \helper\aR($_GET["cid"], "i");
$name = \helper\ar($_GET["name"], "s");
$direction = \helper\aR($_GET["direction"], "s");

moveMetadataField($user, NULL, $cid, $name, $direction);

header("location: " . $_SERVER["HTTP_REFERER"] . "#datasets");

?>
