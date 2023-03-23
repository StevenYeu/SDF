<?php

require_once __DIR__ . "/../../classes/classes.php";
\helper\scicrunch_session_start();
require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/rrid-report.php";

if(!isset($_SESSION["user"]) || !isset($_POST["rrid-report-id"]) || !isset($_POST["type"]) || !isset($_POST["subtype"]) || !isset($_POST["rrid"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

addReportItem($_SESSION["user"], NULL, \helper\aR($_POST["rrid-report-id"], "i"), \helper\aR($_POST["type"], "s"), \helper\aR($_POST["subtype"], "s"), \helper\aR($_POST["rrid"], "s"));

header("location: " . $_SERVER["HTTP_REFERER"]);

?>
