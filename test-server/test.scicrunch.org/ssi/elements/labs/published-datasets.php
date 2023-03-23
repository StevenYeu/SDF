<?php

require_once __DIR__ . "/../../../api-classes/labs.php";

$lab = $data["lab"];
$user = $data["user"];
$community = $data["community"];

if(!$lab || !$user || !$community) {
    return;
}

$lab_templates = $lab->templates();

$lab_datasets_opt = getLabDatasets($user, NULL, $lab->id);
if($lab_datasets_opt->success) {
    $lab_datasets = $lab_datasets_opt->data;
} else {
    $lab_datasets = Array();
}

$lab_members = LabMembership::loadArrayBy(Array("labid"), Array($lab->id));

$pending_members_count = 0;
foreach($lab_members as $lm) {
    if($lm->level == 0) {
        $pending_members_count += 1;
    }
}
?>

<div class="row">
    <div class="col-md-12">
        <h1>My Datasets</h1>
        <?php if($lab->broadcast_message): ?>
            <div class="alert alert-success">
                <?php echo $lab->broadcast_message ?>
            </div>
        <?php endif ?>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
 <div class="row">
            <div class="col-md-12">
                <div class="panel panel-default">
                    <div class="panel-body scroll-height-200">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Dataset</th>
                                    <th>Records</th>
                                    <th>Fields</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            <?php foreach($lab_datasets as $ld): ?>
                                <?php if($ld->uid == $user->id): ?>
                                    <tr>
                                        <td width="60%"><a target="_self" class="lab-link" href="<?php echo $community->fullURL() ?>/lab/dataset?labid=<?php echo $lab->id ?>&datasetid=<?php echo $ld->id ?>">
                                                    <?php echo $ld->name ?>
                                                </a></td>
                                        <td><?php echo number_format($ld->record_count) ?> records</td>
                                        <td><?php echo number_format($ld->template()->nFields()) ?> fields 
                                                <?php $unmapped_count = $ld->template()->defaultILXCount(); 
                                                     if($unmapped_count > 0): 
                                                        echo '(<span class="text-danger">' . number_format($unmapped_count) . ' unmapped fields</span>)';
                                                    endif;
                                                ?>
                                        </td>
                                        <td><i class="fa fa-exclamation-triangle"></i></td>
                                    </tr>
                                <?php endif ?>
                            <?php endforeach ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
