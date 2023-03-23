<?php

$snapshot_id = $_GET["snapshot"];
$snapshot = RRIDReportFreeze::loadBy(Array("id"), Array($snapshot_id));

?>

<div>
    <?php echo \helper\htmlElement("rrid-report-snapshot", Array("snapshot" => $snapshot)) ?>
</div>
