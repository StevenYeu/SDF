<?php

$lab = $data["lab"];
$community = $data["community"];

if(!$lab || !$community) {
    return;
}

if (!isset($_GET['datasetid']))
    return;
$dataset_id = $_GET['datasetid'];

?>
        <script src="/js/module-dataset-view.js"></script>

<div class="hidden" id="dataset-id"><?php echo $_GET['datasetid']; ?></div>
<div id="dataset-view-app" ng-controller="AssociatedfilesController as ctrl">
    <div class="row">
        <div class="col-md-8"><h2>Upload Dataset Dictionary and Methodology - <a target="_self" class="lab-link" href="<?php echo $community->fullURL() ?>/lab?labid=<?php echo $lab->id ?>"><?php echo $lab->name ?></a></h2>
                    <p>Having a data dictionary and methodology increases the interpretability and usability of your dataset.</p>
            <p>You can also upload a data dictionary and methodology document in the dataset page later.</p>
            </div>

        <div class="col-md-3"></div>
        <div class="col-md-1"></div>
    </div>
    <div class="row margin-bottom-20">
        <div class="col-md-3">
            <div class="form-group">
            
                <label>Data Dictionary <span data-name="csv-info.html" class="help-tooltip-btn help-tooltip-text"></span>File (.csv)</label>
                <br>
                <label for="fileChoose_dictionary" style="cursor: pointer;" class="lab-button lab-small-button">Select .csv File</label>
                    <input id="fileChoose_dictionary" class="hidden" type="file" custom-on-change="dictionaryFileSelect" />
                </label>

                <label style="display: inline;" ng-show="filename23">
                    Selected file: {{filename23 | limitTo:30}}{{filename23.length > 30 ? '&hellip;' : ''}}
                </label>
                <label style="display: inline;" ng-show="response">{{ response }}</label>
                
                <label ng-show="$ctrl.goodFile" class="fa fa-check-circle" style="color: #72C02C"></label>
                <label class="text-danger">{{ $ctrl.errors.file }}</label>
                <label class="text-info" ng-show="$ctrl.errors.file">
                    Field headers cannot be repeated and cannot begin with an underscore.
                </label>
                <label ng-show="$ctrl.errors.file_blank_headers_count > 0" class="text-warning">
                    Skipped {{ $ctrl.errors.file_blank_headers_count }} blank header columns.
                </label>
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                <label>Methodology <span data-name="csv-info.html" class="help-tooltip-btn help-tooltip-text"></span> File (.doc or .pdf)</label>
                <br>
                <input type="file" id="fileChoose_methodology" class="hidden" custom-on-change="methodologyFileSelect" />
                <label for="fileChoose_methodology" style="cursor: pointer;" class="lab-button lab-small-button">Select .doc or .pdf File</label>
                <br>
                <label style="display: inline;" ng-show="$ctrl.filename">
                    Selected file: {{$ctrl.filename | limitTo:30}}{{$ctrl.filename.length > 30 ? '&hellip;' : ''}}
                </label>
                <label ng-show="$ctrl.goodFile" class="fa fa-check-circle" style="color: #72C02C"></label>
                <label class="text-danger">{{ $ctrl.errors.file }}</label>
                <label class="text-info" ng-show="$ctrl.errors.file">
                    Field headers cannot be repeated and cannot begin with an underscore.
                </label>
                <label ng-show="$ctrl.errors.file_blank_headers_count > 0" class="text-warning">
                    Skipped {{ $ctrl.errors.file_blank_headers_count }} blank header columns.
                </label>
            </div>
        </div>
        <div class="col-md-6 well"><h2>Tutorial Info here ... </h2></div>
    </div>
</div>
