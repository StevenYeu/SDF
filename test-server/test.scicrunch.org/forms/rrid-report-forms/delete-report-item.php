<?php

require_once "../../classes/classes.php";
\helper\scicrunch_session_start();
require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/rrid-report.php";

if(!isset($_SESSION["user"]) || !isset($_GET["itemid"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

$itemid = \helper\aR($_GET["itemid"], "i");
$report_item = RRIDReportItem::loadBy(Array("id"), Array($itemid));
deleteReportItem($_SESSION["user"], NULL, $report_item->rrid_report_id, $report_item->uuid, $report_item->type, NULL, true);

header("location: " . $_SERVER["HTTP_REFERER"]);
exit;

?>
