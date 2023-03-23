<?php

$docroot = "..";
require_once $docroot . "/classes/classes.php";

$cxn = new Connection();
$cxn->connect();
$report_item_ids = $cxn->select("rrid_report_item", Array("id"), "", Array(), "");
$cxn->close();

foreach($report_item_ids as $rii) {
    $report_item = RRIDReportItem::loadBy(Array("id"), Array($rii["id"]));
    if(is_null($report_item)) continue;
    $report_item->checkForNewData();
}

?>
