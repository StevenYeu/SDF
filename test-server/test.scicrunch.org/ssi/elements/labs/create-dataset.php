<?php

$lab = $data["lab"];
$community = $data["community"];

if(!$lab || !$community) {
    return;
}

$user_labs = $data['user_labs'];
?>

<div id="create-dataset-app" ng-controller="createDatasetController as ctrl">
    <div class="row">
        <div class="col-md-6"><h2>Upload new dataset - <a target="_self" class="lab-link" href="<?php echo $community->fullURL() ?>/lab?labid=<?php echo $lab->id ?>"><?php echo $lab->name ?></a></h2></div>
        <div class="col-md-5">

<div class="container">
<div class="progresss">
<div class="progresss-track"></div>
<div id="step1" class="progresss-step">
Check Lab
</div>
<div id="step2" class="progresss-step">
Upload Data
</div>
<div id="step3" class="progresss-step">
Preview
</div>
<div id="step4" class="progresss-step">
Done!
</div>
</div>
</div>
<script id="rendered-js">
let step = 'step1';

const step1 = document.getElementById('step1');
const step2 = document.getElementById('step2');
const step3 = document.getElementById('step3');
const step4 = document.getElementById('step4');

function next() {
  if (step === 'step1') {
    step = 'step2';
    step1.classList.remove("is-active");
    step1.classList.add("is-complete");
    step2.classList.add("is-active");

  } else if (step === 'step2') {
    step = 'step3';
    step2.classList.remove("is-active");
    step2.classList.add("is-complete");
    step3.classList.add("is-active");

  } else if (step === 'step3') {
    step = 'step4';
    step3.classList.remove("is-active");
    step3.classList.add("is-complete");
    step4.classList.add("is-active");

  } else if (step === 'step4d') {
    step = 'complete';
    step4.classList.remove("is-active");
    step4.classList.add("is-complete");

  } else if (step === 'complete') {
    step = 'step1';
    step4.classList.remove("is-complete");
    step3.classList.remove("is-complete");
    step2.classList.remove("is-complete");
    step1.classList.remove("is-complete");
    step1.classList.add("is-active");
  }
}
//# sourceURL=pen.js
    </script>
            </div>
        <div class="col-md-1"></div>
    </div>
    <div class="row margin-bottom-20" id="upload-help-buttons">
            <div class="col-md-12">
        <p>
            <a data-toggle="tooltip" title="Data Upload Tutorial" target="_blank" href="<?php echo $data['community']->fullURL(); ?>/about/tutorials#upload" style="color: #428bca; background-color: white; font-size: 1.4em"><i class="fa fa-question-circle"></i></a>
            Visit the <a tooltip="Help" target="_blank" href="<?php echo $data['community']->fullURL(); ?>/about/tutorials#upload">Data Upload Tutorial</a> for step-by-step instructions on how to add data to the Open Data Commons.
        </p>
        <p>
            <a data-toggle="tooltip" title="Coming soon" href="#" style="color: #428bca; background-color: white; font-size: 1.4em"><i class="fa fa-question-circle"></i></a>
            Alternative option: Use Python API to upload datasets.
        </p>
        </div>
    </div>
    <div class="row margin-bottom-20">
        <div class="col-md-12">
            <div ng-show="ctrl.mode == 'choose-action'">
                    <p>Your data will be uploaded to your current lab: <strong><?php echo $lab->name; ?></strong>.</p>

                     <div class="dropdown">
                      <p>If you want to upload to a different lab, you can  
                          <span class="dropbtn"><a href="#">Switch Labs</a></span>
                          <div class="dropdown-content" style="margin-left: 300px;">
                              <?php foreach($user_labs as $switchlab) {
                                if ($switchlab->id != $_GET['labid'])
                                    echo '<span ng-click="ctrl.switchLab(' . $switchlab->id . ')"><a href="#">' . $switchlab->name . '</a></span>';
                            } ?>
                          </div>
                        </div>                                    
                        .</p>

                        I'm in the right lab.
                        <a href="javascript:void(0)">
                            <div class="btn btn-success" ng-click="ctrl.changeMode('lab-confirmed')" onClick="next(); return false;">
                                <span>Next</span>
                            </div>
                        </a>
            </div>
            <div ng-show="ctrl.mode == 'lab-confirmed'">
                    <autogen-template-component
                    type="dataset"
                    portal-name="<?php echo $community->portalName ?>"
                    labid="<?php echo $lab->id ?>"
                />
            </div>
        </div>
    </div>
</div>
