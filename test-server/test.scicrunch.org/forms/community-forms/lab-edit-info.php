<?php
require_once __DIR__ . "/../../classes/classes.php";
session_start();

require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/labs.php";

if(!isset($_SESSION["user"]) || !isset($_GET["labid"]) || !isset($_GET["name"]) || !isset($_GET["private_description"]) || !isset($_GET["public_description"])) {
        header("location: " . $_SERVER["HTTP_REFERER"]);
            exit;
}

$user = $_SESSION["user"];
$labid = \helper\aR($_GET["labid"], "i");
$name = \helper\aR($_GET["name"], "s");
$private_description = \helper\aR($_GET["private_description"], "s");
$public_description = \helper\aR($_GET["public_description"], "s");

editLabInfo($user, NULL, $labid, $name, $private_description, $public_description);

header("location: " . $_SERVER["HTTP_REFERER"]);
?>
