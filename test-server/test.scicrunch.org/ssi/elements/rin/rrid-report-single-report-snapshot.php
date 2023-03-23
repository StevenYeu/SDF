<?php

$community = $data["community"];
$user = $data["user"];
$report_id = $data["report-id"];
$snapshot_id = $data["snapshot-id"];

$snapshot = RRIDReportFreeze::loadBy(Array("id", "rrid_report_id"), Array($snapshot_id, $report_id));

if(!is_null($snapshot)) {
    $report = $snapshot->report();
    $report_name = $report->name;
}

if(!$snapshot->accessible($user)) {
    $snapshot = NULL;
}

$report_url = $community->fullURL() . "/rin/rrid-report/" . $report->id;

?>

<?php ob_start(); ?>
    <?php echo \helper\htmlElement("rrid-report-snapshot", Array("snapshot" => $snapshot, "report_url" => $report_url)) ?>
<?php $report_html = ob_get_clean(); ?>

<?php

$report_data = Array(
    "title" => "Authentication Report",
    "breadcrumbs" => Array(
        Array("text" => "Home", "url" => $community->fullURL()),
        Array("text" => "Authentication Reports", "url" => $community->fullURL() . "/rin/rrid-report"),
        Array("text" => "Report Dashboard", "url" => $community->fullURL() . "/rin/rrid-report/overview"),
        Array("text" => $report->name, "url" => $community->fullURL() . "/rin/rrid-report/" . $report->id),
        Array("text" => "PDF Report", "active" => true),
    ),
    "html-body" => $report_html,
);

echo \helper\htmlElement("rin-style-page", $report_data);

?>
