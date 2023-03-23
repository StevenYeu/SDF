<?php

    $user = $data["user"];
    $uuid = $data["uuid"];
    $community = $data["community"];
    $view = $data["view"];

    $is_rrid_report_view = RRIDReportItem::isRRIDReportView($view);
    if($is_rrid_report_view) {
        $rrid_reports = RRIDReport::getUserReports($user);
        $rrid_data = $data["rrid-data"];
    }

    $in_use = Collection::checkInCollection($uuid, $user);

?>

<style>
    .in-collection {
        color: green;
    }
</style>

<span>

</span>
