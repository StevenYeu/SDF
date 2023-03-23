<?php 

require_once __DIR__ . "/../../../api-classes/labs.php";

$lab = $data["lab"];
$community = $data["community"];
$user = $data["user"];
$user_labs = $data['user_labs'];
$main_lab = $data['main_lab'];

$dashboard_url = $community->fullURL() . "/community-labs/dashboard";
if ($lab->id)
    $dashboard_url .= "?labid=" . $lab->id;
else {
    if ($main_lab) {
        $lab = $main_lab;
    }
}

/*
$community_labs = Lab::loadArrayBy(Array("cid", "curated"), Array($community->id, Lab::CURATED_STATUS_APPROVED));
$user_labs = Array();
$nonuser_labs = Array();

foreach($community_labs as $cl) {
    if($cl->isMember($user)) {
        $user_labs[] = $cl;
    }
}    

if ($lab->id) {
    $lab_datasets_opt = getLabDatasets($user, NULL, $lab->id);
    if($lab_datasets_opt->success) {
        $lab_datasets = $lab_datasets_opt->data;
    } else {
        $lab_datasets = Array();
    }

    $my_dataset_ids = Array();
    foreach ($lab_datasets as $ld) {
        $my_dataset_ids[] = $ld->id;
    }
}
*/
?>
		
<div id="main-content" class="main">
    <div>
        <div class="row">
            <div class="col-md-12">
                <ul id="odc_breadcrumb" class="breadcrumb">
                <?php echo $data['crumb']; ?>
                </ul>
            </div>
        </div>
        
        <!-- Content goes here -->
            <!-- main content -->
