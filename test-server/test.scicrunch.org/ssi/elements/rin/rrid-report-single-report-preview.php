<?php

$community = $data["community"];
$user = $data["user"];
$report_id = $data["report-id"];

$report = RRIDReport::loadBy(Array("id", "uid"), Array($report_id, $user->id));
$report_url = $community->fullURL() . "/rin/rrid-report/" . $report->id;

?>
<?php ob_start(); ?>
<div class="profile container content">
    <div class="row">
        <div class="col-md-12">
            <a href="<?php echo $report_url ?>"><button class="btn btn-primary">Edit information</button></a>
            <a href="javascript:void(0)" class="btn btn-primary simple-toggle" modal=".snapshot-rrid-report"><i class="fa fa-camera"></i> Save PDF report (Step 5)</a>
            <br><strong>Please note that for NIH grant applications, the plan should be no more than one page</strong>. You can copy and paste the information and edit the information in a word file.
            <?php echo \helper\htmlElement("rrid-report", Array("report" => $report)); ?>
            <a href="javascript:void(0)" class="btn btn-primary simple-toggle" modal=".snapshot-rrid-report"><i class="fa fa-camera"></i> Save PDF report (Step 5)</a>
        </div>
    </div>
</div>
<?php $report_html = ob_get_clean(); ?>

<?php

$report_data = Array(
    "title" => "Authentication Report Preview",
    "breadcrumbs" => Array(
        Array("text" => "Home", "url" => $community->fullURL()),
        Array("text" => "Authentication Reports", "url" => $community->fullURL() . "/rin/rrid-report"),
        Array("text" => "Report Dashboard", "url" => $community->fullURL() . "/rin/rrid-report/overview"),
        Array("text" => $report->name, "url" => $community->fullURL() . "/rin/rrid-report/" . $report->id),
        Array("text" => "Preview", "active" => true),
    ),
    "html-body" => $report_html,
);
echo \helper\htmlElement("rin-style-page", $report_data);

?>

<!-- MODAL: CREATE SNAPSHOT -->
<div class="snapshot-rrid-report html-modal back-hide no-padding">
    <div class="close dark less-right">X</div>
    <div>
        <header><h2>Create a PDF for this report</h2></header>
        <p>
            Would you like to create a permanent snapshot of this report?
            This will create a read-only copy of all the data in its current state, which cannot be deleted.
            <!--Users are limited to creating five snapshots per day across all authentication reports.-->
        </p>
        <hr/>
        <a href="/forms/rrid-report-forms/snapshot.php?cid=<?php echo $community->id ?>&id=<?php echo $report->id ?>"><button class="btn btn-success">Create</button></a>
        <button class="btn btn-default close-btn">Cancel</button>
    </div>
</div>
<!-- /MODAl: CREATE SNAPSHOT -->
