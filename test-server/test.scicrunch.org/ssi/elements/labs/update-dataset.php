<?php

$lab = $data["lab"];
$community = $data["community"];

if(!$lab || !$community) {
    return;
}

$dataset = Dataset::loadBy(Array("id"), Array($_GET['datasetid']));
$subject = DatasetField::getByAnnotation($dataset->template(), "subject")[0];

?>
<?php 

/*
$subject_field = DatasetField::getByAnnotation($this->template(), "subject")[0];
*/ ?>

<input type="hidden" id="subject_field_name" value="<?php echo $subject->name; ?>">
<input type="hidden" id="dataset_id" value="<?php echo $_GET['datasetid']; ?>">
<div id="update-dataset-app" ng-controller="updateDatasetController as ctrl">
    <div class="row margin-bottom-20">
        <div class="col-md-6">
            <h2>Update Dataset</h2>
        </div>
        <div class="col-md-5">
            <div class="container">
                <div class="progresss">
                    <div class="progresss-track"></div>
                    <div id="step0" class="progresss-step">Select Update Method</div>
                    <div id="step1" class="progresss-step">Select File</div>
                    <div id="step2" class="progresss-step">Preview Data</div>
                    <div id="step3" class="progresss-step">Done!</div>
                </div>
            </div>
            <script id="rendered-js">
                let step = 'step0';
                const step0 = document.getElementById('step0');
                const step1 = document.getElementById('step1');
                const step2 = document.getElementById('step2');
                const step3 = document.getElementById('step3');
                
                step0.classList.add("is-active");

                function next() {
                    if (step === 'step0') {
                        step = 'step1';
                        step0.classList.remove("is-active");
                        step0.classList.add("is-complete");
                        step1.classList.add("is-active");
                    } else if (step === 'step1') {
                        step = 'step2';
                        step1.classList.remove("is-active");
                        step1.classList.add("is-complete");
                        step2.classList.add("is-active");
                    } else if (step === 'step2') {
                        step = 'complete';
                        step2.classList.remove("is-active");
                        step2.classList.add("is-complete");
                        step3.classList.add("is-complete");
                    }
                }
            </script>
        </div>
    </div>

    <div>
        <div class="col-md-12">
            <div ng-show="ctrl.mode == 'lab-confirmed'"><!-- lab-confirmed -->
               <div class="col-md-12">
                    <autogen-template-component
                            type="dataset-update"
                            dataset="ctrl.selected_dataset"
                            portal-name="ctrl.portalName"
                            labid="ctrl.labid"
                        />

                </div>
            </div>
        </div>
    </div>
</div>
