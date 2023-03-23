<?php

require_once __DIR__ . "/../../classes/classes.php";
session_start();

require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/datasets.php";

if(!isset($_SESSION["user"]) || !isset($_GET["template-id"]) || !isset($_GET["cid"]) || !isset($_GET["action"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

$user = $_SESSION["user"];
$template_id = $_GET["template-id"];
$cid = $_GET["cid"];
$action = $_GET["action"];

if($action === "add") {
    addDatasetTemplateToCommunity($user, NULL, $template_id, $cid);
} elseif($action === "remove") {
    removeDatasetTemplateFromCommunity($user, NULL, $template_id, $cid);
}

header("location: " . $_SERVER["HTTP_REFERER"]);
exit;

?>
