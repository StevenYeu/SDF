<?php

$snapshot = $data["snapshot"];
$report_url = $data["report_url"];

if(!is_null($snapshot) && !$snapshot->accessible($_SESSION["user"])) {
    $snapshot = NULL;
}

?>

<div>
    <?php if($snapshot): ?>
        <div class="row">
            <div class="col-md-12">
                <?php if($report_url): ?>
                    <a href="<?php echo $report_url ?>"><button class="btn btn-primary">Go back to report</button></a>
                <?php endif ?>
                <?php if($snapshot->pdfExists()): ?><a href="/php/rrid-report-pdf.php?id=<?php echo $snapshot->id ?>"><button class="btn btn-primary"><i class="fa fa-file-o"></i> Download PDF</button></a><?php endif ?>
                <strong>Please note that for NIH grant applications, the plan should be no more than one page</strong>. You can copy and paste the information and edit the information in a word file. 
            </div>
        </div>
        <div class="row">
            <div class="col-md-12">
                <?php echo $snapshot->data ?>
            </div>
        </div>
    <?php else: ?>
        <div class="container">
            <div class="row">
                <h4>We were unable to find this report snapshot.</h4>
            </div>
        </div>
    <?php endif ?>
</div>
