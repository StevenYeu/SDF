<?php

$lab = $data["lab"];
$community = $data["community"];

if(!$lab || !$community) {
    return;
}

?>

<div id="add-to-dataset-app" ng-controller="addToDatasetController as ctrl">
    <div class="row margin-bottom-20">
        <div class="col-md-9">
            <h2>Add Data to Dataset </h2>
            
            <h4>{{ ctrl.selected_dataset.name }}</h4>
                
                <p>Data can be added to datasets by uploading a CSV file of <strong>new</strong> data. Please do not include already uploaded data. Also, this file should have the same field headers as the original dataset. </p>
                <p>You can <a tooltip="Download an empty template to add data to" target="_self" ng-href="/php/datasetfields-csv-template.php?templateid={{ctrl.selected_dataset.template.id}}">download the dataset template <img src="/images/csv-file-format-extension.png" height="24px"></a> to get started. When you're ready, please select a CSV file below:</p>

        </div>
    </div>
    <div ng-show="ctrl.lab_datasets != null && ctrl.lab_datasets.length > 0">
        <div class="col-md-12">
            <div ng-show="ctrl.mode == 'pick-csv'">
               <div class="col-md-12">
                    <h4>Select a CSV file  <img src="/images/csv-file-format-extension.png" height="24px"></h4>
                    <div>
                        <input type="file" id="browse-add-to-data" class="hidden" custom-on-change="ctrl.dataFileSelect" />
                        <label for="browse-add-to-data" style="cursor: pointer;" class="lab-button lab-small-button">Select File</label>
                        <br>
                        <label style="display: inline;" ng-show="ctrl.filename">
                            Selected file: {{ctrl.filename | limitTo:30}}{{ctrl.filename.length > 30 ? '&hellip;' : ''}}
                        </label>
                        <label ng-show="ctrl.goodFile" class="fa fa-check-circle" style="color: #72C02C"></label>
                        <label class="text-danger">{{ ctrl.errors.file }}</label>
                    </div>
                </div>
            </div>
            <div ng-show="ctrl.mode == 'preview'">
                <a ng-click="ctrl.changeMode('pick-csv')" href="javascript:void(0)"><span class="lab-button lab-small-button">Go back</span></a>

                <div ng-show="ctrl.selected_dataset != null" style="overflow-x:scroll">
                    <div class="well">
                        <upload-data-component
                            uploadable="ctrl.uploadable"
                            dataset="ctrl.selected_dataset"
                            portal-name="ctrl.portalName"
                            labid="ctrl.labid"
                        />
                    </div>
                    <div ng-show="ctrl.loading_single_dataset" class="element-overlay">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
