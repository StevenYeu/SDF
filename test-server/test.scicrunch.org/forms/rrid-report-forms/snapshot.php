<?php

require_once "../../classes/classes.php";
\helper\scicrunch_session_start();
require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/rrid-report.php";

if(!isset($_SESSION["user"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

$report_id = \helper\aR($_GET["id"], "i");
$community_id = $_GET["cid"];

$community = new Community();
$community->getByID($community_id);

$snapshot = createSnapshot($_SESSION["user"], NULL, $report_id);

if($snapshot->success) {
    header("location: " . Community::fullURLStatic($community) . "/rin/rrid-report/" . $report_id . "/snapshot?id=" . $snapshot->data->id);
} else {
    header("location:" . $_SERVER["HTTP_REFERER"]);
}

?>
