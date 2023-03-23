<?php

require_once __DIR__ . "/../../classes/classes.php";
\helper\scicrunch_session_start();
require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/rrid-report.php";

if(!isset($_SESSION["user"]) || !isset($_POST["rrid-report-id"]) || !isset($_POST["type"]) || !isset($_POST["subtype"]) || !isset($_POST["uuid"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

deleteReportItem($_SESSION["user"], NULL, \helper\aR($_POST["rrid-report-id"], "i"), \helper\aR($_POST["uuid"], "s"), \helper\aR($_POST["type"], "s"), \helper\aR($_POST["subtype"], "s"), false);

header("location: " . $_SERVER["HTTP_REFERER"]);

?>
