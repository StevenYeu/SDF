<?php

require_once __DIR__ . "/../../classes/classes.php";
\helper\scicrunch_session_start();
require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/rrid-report.php";

if(!isset($_SESSION["user"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

$name = \helper\aR($_POST["name"], "s");
$description = \helper\aR($_POST["description"], "s");
$report_id = $_POST["report-id"];

updateReportNameDescription($_SESSION["user"], NULL, $report_id, $name, $description);
header("location: " . $_SERVER["HTTP_REFERER"]);
exit;

?>
