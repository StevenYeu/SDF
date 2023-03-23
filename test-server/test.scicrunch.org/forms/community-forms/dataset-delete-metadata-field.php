<?php

require_once __DIR__ . "/../../classes/classes.php";
session_start();

require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/datasets.php";

if(!isset($_SESSION["user"]) || !isset($_GET["cid"]) || !isset($_GET["name"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
};

$user = $_SESSION["user"];
$cid = \helper\aR($_GET["cid"], "i");
$name = \helper\aR($_GET["name"], "i");

deleteMetadataField($user, NULL, $cid, $name);

header("location: " . $_SERVER["HTTP_REFERER"] . "#datasets");

?>
