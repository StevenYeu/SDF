<?php
require_once __DIR__ . "/../../classes/classes.php";
session_start();

require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/datasets.php";

if(!isset($_SESSION["user"]) || !isset($_GET["datasetid"]) || !isset($_GET["status"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

$user = $_SESSION["user"];
$datasetid = \helper\aR($_GET["datasetid"], "i");
$status = \helper\aR($_GET["status"], "s");

changeLabStatus($user, NULL, $datasetid, $status);

header("location: " . $_SERVER["HTTP_REFERER"]);

?>

