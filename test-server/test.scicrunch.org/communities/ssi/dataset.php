<?php

$submission = CommunityDataset::loadBy(Array("cid", "datasetid"), Array($community->id, $vars["id"]));
$is_community_moderator = isset($_SESSION["user"]) && $_SESSION["user"]->levels[$community->id] >= 2;
if(!is_null($submission) && ($submission->curated !== CommunityDataset::CURATED_STATUS_PENDING || $is_community_moderator)) {
    $dataset = Dataset::loadBy(Array("id"), Array($submission->datasetid));
}

?>

<?php echo \helper\htmlElement("special-ilx", Array("ilx" => $GLOBALS["config"]["dataset-config"]["term"]["ilx"])) ?>
<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/module-resource-directives.js"></script>
<script src="/js/module-datasets.js"></script>

<?php if($is_community_moderator): ?>
    <div class="container">
        <?php if($submission->curated === CommunityDataset::CURATED_STATUS_APPROVED): ?>
            <div class="row">
                <span style="color: green">This dataset has been approved and is visible to anyone that can access this community.</span>
            </div>
        <?php endif ?>
        <?php if($submission->curated === CommunityDataset::CURATED_STATUS_REJECTED): ?>
            <div class="row">
                <span style="color: red">This dataset has been rejected and will not be visible to non-moderators of this community.</span>
            </div>
        <?php endif ?>
        <div class="row">
            <?php if($submission->curated !== CommunityDataset::CURATED_STATUS_APPROVED): ?>
                <a target="_self" href="/forms/community-forms/dataset-curate.php?portal_name=<?php echo $community->portalName ?>&datasetid=<?php echo $dataset->id ?>&status=<?php echo CommunityDataset::CURATED_STATUS_APPROVED ?>"><button class="btn btn-success">Approve dataset</button></a>
            <?php endif ?>
        <?php if($submission->curated !== CommunityDataset::CURATED_STATUS_APPROVEDINTERNAL): ?>
            <a target="_self" href="/forms/community-forms/dataset-curate.php?portal_name=<?php echo $community->portalName ?>&datasetid=<?php echo $dataset->id ?>&status=<?php echo CommunityDataset::CURATED_STATUS_APPROVED ?>"><button class="btn btn-success">Approve dataset</button></a>
        <?php endif ?>
            <?php if($submission->curated !== CommunityDataset::CURATED_STATUS_REJECTED): ?>
                <a target="_self" href="/forms/community-forms/dataset-curate.php?portal_name=<?php echo $community->portalName ?>&datasetid=<?php echo $dataset->id ?>&status=<?php echo CommunityDataset::CURATED_STATUS_REJECTED ?>"><button class="btn btn-danger">Reject dataset</button></a>
            <?php endif ?>
        </div>
    </div>
    <hr/>
<?php endif ?>

<?php if(is_null($submission)): ?>
    <div class="container" style="margin-top: 20px">
        <p>The dataset you requested could not be found.</p>
    </div>
<?php else: ?>
    <?php echo \helper\htmlElement("dataset", Array("dataset" => $dataset, "community" => $community)); ?>
<?php endif ?>
