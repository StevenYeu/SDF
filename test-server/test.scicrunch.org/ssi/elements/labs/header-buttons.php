<?php

$lab = $data["lab"];
$community = $data["community"];

if(!$lab || !$community) {
    return;
}

?>

<div class="no-gutter">

    <div class="pull-right">
        <div class="col-md-12">
            <a target="_self" href="<?php echo $community->fullURL() ?>/lab?labid=<?php echo $lab->id ?>">
                <button type="button" class="btn lab-button lab-medium-button">
                    <span class="lab-btn-label"><i class="fa fa-user" aria-hidden="true"></i></span>Lab Home
                </button>
            </a>

            <a target="_self" href="<?php echo $community->fullURL() ?>/lab/add-data?labid=<?php echo $lab->id ?>">
                <button type="button" class="btn lab-button lab-medium-button">
                    <span class="lab-btn-label"><i class="fa fa-plus" aria-hidden="true"></i></span>Add Data
                </button>
            </a>

            <a target="_self" href="<?php echo $community->fullURL() ?>/lab/all-datasets?labid=<?php echo $lab->id ?>">
                <button type="button" class="btn lab-button lab-medium-button" >
                    <span class="lab-btn-label"><i class="fa fa-bar-chart" aria-hidden="true"></i></span>Explore Data
                </button>
            </a>

            <a target="_new" href="<?php echo $community->fullURL() ?>/about/community-lab-info">
                <button type="button" class="btn lab-button lab-medium-button">
                    <span class="lab-btn-label"><i class="fa fa-lightbulb-o" aria-hidden="true"></i></span>Learn More
                </button>
            </a>
        </div>
    </div>

</div>
