<?php

$lab = $data["lab"];
$community = $data["community"];
$user = $data["user"];

if(!$lab || !$community || !$user) {
    return;
}

$lab_datasets = Dataset::loadByCommunityAndUser($community, $user, 200, 0);

?>
<div>
    <div class="row">
        <div class="col-md-6">
                <!--<h1><a class="lab-link" href="<?php echo $community->fullURL() ?>/lab?labid=<?php echo $lab->id ?>"><?php echo $lab->name ?></a> datasets</h1>-->
                <h2>Open Data Commons Datasets</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <?php if(!empty($lab_datasets)): ?>
                <?php foreach($lab_datasets as $dataset): ?>
                    <div class="row">
                        <div class="col-md-8"><h4>
                            <a href="<?php echo $community->fullURL() ?>/lab/view-dataset?labid=<?php echo $lab->id ?>&datasetid=<?php echo $dataset->id ?>">
                                <?php echo $dataset->name ?>
                            </a></h4>
                                <p><?php echo $dataset->description ?></p>

                        </div>
                        <div class="col-md-4">
                            <dl class="dl-horizontal">
                                <dt>Submitted by</dt>
                                <dd><?php echo $dataset->user()->getFullName() ?></dd>
                                <dt>Lab</dt>
                                <dd><?php echo $dataset->lab()->name ?></dd>
                                <dt>Status</dt>
                                <dd><span style="color:<?php echo $ld->labStatusColor(); ?>"><?php echo $ld->labStatusPretty(); ?></span></dd>
                                <dt>Size</dt>
                                <dd>
                                    <?php echo $dataset->record_count ?> records
                                    /
                                    <?php echo $dataset->template()->nfields() ?> fields
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <hr/>
                <?php endforeach ?>
            <?php else: ?>
                <h4>
                    No datasets have been made available to the Open Data Commons yet.
                </h4>
            <?php endif ?>
        </div>
    </div>
</div>
