<?php

require_once __DIR__ . "/../classes/classes.php";
\helper\scicrunch_session_start();

$snapshot_id = $_GET["id"];
$snapshot = RRIDReportFreeze::loadBy(Array("id"), Array($snapshot_id));

if(is_null($snapshot) || !$snapshot->pdfExists() || !$snapshot->accessible($_SESSION["user"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

$filename = $snapshot->pdfFilename();
$report = $snapshot->report();
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="authentication-report-' . $snapshot->version() . '.pdf');
header('Content-Length: ' . filesize($filename));
readfile($filename);

?>
