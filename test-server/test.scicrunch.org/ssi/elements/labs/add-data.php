<?php

$lab = $data["lab"];
$community = $data["community"];

if(!$lab || !$community) {
    return;
}

?>

<div id="add-data-app" ng-controller="addDataController as ctrl">
    <div class="row margin-bottom-20">
        <div class="col-md-6">
            <h2>Add data - <a class="lab-link" target="_self" href="<?php echo $community->fullURL() ?>/lab?labid=<?php echo $lab->id ?>"><?php echo $lab->name ?></a></h2>
        </div>
        <div class="col-md-6">
            <?php echo \helper\htmlElement("labs/header-buttons", Array("lab" => $lab, "community" => $community)) ?>
        </div>
    </div>
    <div>
        <div class="col-md-9">
            <div class="margin-bottom-20">
                <h3>Overview of dataset process</h3>
                <h4>
                    The <?php echo $community->portalName ?> portal allows you to store,
                    manage and explore datasets uploaded by you, your lab,
                    and datasets shared by the broader community.
                    Each dataset is defined by a template which contains information about
                    the data fields and alignment of these data fields with common data
                    elements allowing for potential integration with other datasets.
                    This portal will guide you through the process.
                </h4>
                <h4>
                    <a target="_blank" href="https://github.com/SciCrunch/python-datasets-interface">
                        <div class="lab-button lab-small-button"><i class="fa fa-github"></i></div>
                    </a>
                    You may also use our Python library for creating and editing datasets
                </h4>
                <h4>
                    <a target="_blank" href="https://scicrunch.org/odc-sci/about/help?#odc-help-add-dataset">
                        <div class="lab-button lab-small-button"><i class="fa fa-question-circle"></i></div>
                    </a>
                    Visit our help materials page for more information on how to add data to the Open Data Commons.
                </h4>
            </div>
            <div class="col-md-4">
                <div class="text-center">
                    <a href="<?php echo $community->fullURL() ?>/lab/create-dataset?labid=<?php echo $lab->id ?>">
                        <div class="lab-button lab-big-button">
                            <span>Create new dataset</span>
                        </div>
                    </a>
                    <h4 class="margin-top-20">
                        Create a new dataset by uploading a spreadsheet,
                        adding data via a pre-defined template or by adding data to a template
                        you create.
                    </h4>
                </div>
            </div>
            <div class="col-md-1"></div>
            <div class="col-md-4">
                <div class="text-center">
                    <a href="<?php echo $community->fullURL() ?>/lab/add-to-dataset?labid=<?php echo $lab->id ?>">
                        <div class="lab-button lab-big-button">
                            <span>Add to existing dataset</span>
                        </div>
                    </a>
                    <h4 class="margin-top-20">
                        Add data to an existing dataset by uploading a spreadsheet.
                    </h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <dataset-workflow lab="ctrl.lab" workflow="ctrl.workflow" />
        </div>
    </div>
</div>
