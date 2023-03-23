<?php
require_once __DIR__ . "/../../classes/classes.php";
session_start();

require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/labs.php";

if(!isset($_SESSION["user"]) || !isset($_GET["labid"]) || !isset($_GET["name"]) || !isset($_GET["description"])) {
        header("location: " . $_SERVER["HTTP_REFERER"]);
            exit;
}

$user = $_SESSION["user"];
$labid = $_GET["labid"];
$name = $_GET["name"];
$description = $_GET["description"];

editLabInfo($user, NULL, $labid, $name, $description);

header("location: " . $_SERVER["HTTP_REFERER"]);
?>
