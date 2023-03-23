<?php
$community = $data["community"];
$status = $data["status"];
$page = $data["page"];
$per_page = $data["per_page"];
$offset = $data["offset"];
$is_community_moderator = $data["is_community_moderator"];
$show_pagination = $data["show_pagination"];

$cxn = new Connection();
$cxn->connect();
if($is_community_moderator) {
    if(in_array($status, CommunityDataset::$CURATED_STATUSES)) {
        $submissions = CommunityDataset::loadArrayBy(Array("cid", "curated"), Array($community->id, $status), false, $per_page, false, $offset);
        $count = $cxn->select("community_datasets", Array("count(*)"), "is", Array($community->id, $status), "where cid = ? and curated = ?")[0]["count(*)"];
    } else {
        $submissions = CommunityDataset::loadArrayBy(Array("cid"), Array($community->id), false, $per_page, false, $offset);
        $count = $cxn->select("community_datasets", Array("count(*)"), "i", Array($community->id), "where cid=?")[0]["count(*)"];
    }
} else {
    $submissions = CommunityDataset::loadArrayBy(Array("cid", "curated"), Array($community->id, CommunityDataset::CURATED_STATUS_APPROVED), false, $per_page, false, $offset);
    $count = $cxn->select("community_datasets", Array("count(*)"), "is", Array($community->id, CommunityDataset::CURATED_STATUS_APPROVED), "where cid=? and curated=?")[0]["count(*)"];
}
$cxn->close();

$datasets = Array();
foreach($submissions as $sm) {
    $datasets[$sm->datasetid] = Dataset::loadBy(Array("id"), Array($sm->datasetid));
}

?>

<div class=row">
    <?php foreach($submissions as $i => $sm): ?>
        <?php $dataset = $datasets[$sm->datasetid]; ?>
        <?php if($i != 0): ?><hr/><?php endif ?>
        <div class="inner-results">
            <a href="/<?php echo $community->portalName ?>/dataset?id=<?php echo $dataset->id ?>">
                <h3>
                    <i class="fa fa-table"></i>
                    <?php echo $dataset->long_name ? $dataset->long_name : $dataset->name ?>
                </h3>
            </a>
            <?php if($is_community_moderator): ?>
                <p>
                    <?php if($sm->curated === CommunityDataset::CURATED_STATUS_PENDING): ?>Pending <i class="fa fa-minus-circle" style="color: orange"></i>
                    <?php elseif($sm->curated === CommunityDataset::CURATED_STATUS_REJECTED): ?>Rejected <i class="fa fa-times-circle" style="color: red"></i>
                    <?php elseif($sm->curated === CommunityDataset::CURATED_STATUS_APPROVED): ?>Approved <i class="fa fa-check-circle" style="color: green"></i>
                    <?php endif ?>
                </p>
            <?php endif ?>
            <p>
                Lab Name: <?php echo $dataset->lab()->name; ?> |
                Submitted by: <?php $use = new User(); $use->getByID($dataset->uid); echo $use->getFullName(); ?>
            </p>
            <p>Description: <?php echo $dataset->description ?></p>
        </div>
        <hr/>
    <?php endforeach ?>
    <?php if(empty($submissions)): ?>
        This community does not have any public datasets yet.  You can create your own datasets by joining a lab and submitting your data to this community.
    <?php endif ?>
</div>
<?php if(!empty($submissions) && $show_pagination): ?>
    <?php echo \helper\htmlElement("pagination", Array(
        "count" => $count,
        "per_page" => $per_page,
        "current_page" => $page,
        "params" => "",
        "page_location" => "query",
        "query_param_name" => "page",
        "base_url" => "/" . $community->portalName . "/datasets"
    )); ?>
<?php endif ?>
