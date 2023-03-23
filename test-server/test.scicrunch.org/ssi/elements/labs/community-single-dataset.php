<?php

$community = $data["community"];
$user = $data["user"];
$datasetid = $data["datasetid"];
$lab = $data["lab"];

if(!$community || !$datasetid || !$user) {
    return;
}

$dataset = Dataset::loadBy(Array("id"), Array($datasetid));
$flags = DatasetFlags::loadByDatasetAndUser($dataset, $user);

$visible = false;
if(!is_null($dataset)) {
    if($dataset->isVisible($user)) {
        $visible = true;
    }
}

if (!$visible) {
    echo "<h4>We could not find this dataset or it is not yet publicly available</h4>\n";
    return;
}
?>

<div id="single-dataset-app" ng-controller="singleDatasetController as ctrl" ng-cloak>
    <div ng-hide="ctrl.initial_load">
        <div class="row margin-bottom-20">
            <div class="col-md-8">
                <h2>{{ ctrl.dataset.name }} - <a target="_self" class="lab-link" href="<?php echo $community->fullURL() ?>/lab?labid=<?php echo $dataset->lab()->id ?>"><?php echo $dataset->lab()->name; ?></a></h2>
                <p>{{ ctrl.dataset.description }}</p>
                <p>Owner: {{ ctrl.dataset.owner }}, <?php echo $dataset->lab()->name; ?></p>
                <p>Last modified: {{ ctrl.dataset.last_updated_time * 1000 | date:'yyyy-MM-dd'}}</p>
            </div>
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-body">
                        <table class="table table-borderless">
                            <tbody>
                                <tr>
                                    <td><strong>Status</strong></td>
                                    <td>{{ ctrl.dataset.lab_status_pretty }}</td>
                                </tr>
                                <tr>
                                    <td><strong>Size</strong></td>
                                    <td>{{ ctrl.dataset.total_records_count }} records / {{ ctrl.dataset.template.fields.length }} fields</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row margin-bottom-20">
            <div class="row margin-bottom-10">
                <div class="col-md-8">
                    <strong>Downloads: </strong> 
                    <div class="btn-group">
                        <div style="cursor:pointer" class="btn btn-primary dropdown-toggle" data-toggle="dropdown">
                            Download options <span class="caret"></span>
                        </div>
                        <ul class="dropdown-menu">
                            <li>
                                <a ng-click="ctrl.csv_options.ilx = !ctrl.csv_options.ilx" href="javascript:void(0)">
                                    Include CDE ILX identifiers <span ng-show="ctrl.csv_options.ilx"><i class="fa fa-check"></i></span>
                                </a>
                            </li>
                        </ul>
                        &nbsp;
                        <a ng-href="{{ ctrl.downloadCSVUrl() }}" target="_self">
                            <div class="btn btn-primary">
                                <i class="fa fa-download"></i> Download CSV
                            </div>
                        </a>
                    </div>                
                    <button ng-click="ctrl.DownloadAssociatedFile(ctrl.dictionary)" class="btn btn-primary" ng-disabled="!ctrl.dictionary"><i class="fa fa-download"></i> Data Dictionary</button>
                    <button ng-click="ctrl.DownloadAssociatedFile(ctrl.methodology)" class="btn btn-primary" ng-disabled="!ctrl.methodology"><i class="fa fa-download"></i> Methodology</button>
                </div>
            </div>
        </div>


<!--        <div ng-show="ctrl.dataset.can_edit"> -->
            <div class="col-md-12">
                <div class="row">
                    <div class="col-md-4">
                        <input ng-model="ctrl.query" size="30"/>
                        <button class="btn btn-success" ng-click="ctrl.searchQuery()">Search</button>
                    </div>
                    <div class="col-md-8">
                        <h4>{{ ctrl.dataset.data.count }} results found<span ng-show="ctrl.previous_search"> for the search '{{ ctrl.previous_search }}'</span></h4>
                    </div>
                </div>

                <div class="col-md-12" ng-hide="ctrl.searching">
                    <div id="dataset-table-scroll-top" style="overflow-x: scroll; overflow-y: hidden; height: 20px">
                        <div>&nbsp;</div>
                    </div>
                    <div id="dataset-table-wrapper" style="overflow-x: scroll; padding-top: 20px; padding-bottom: 20px">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th ng-repeat="field in ctrl.dataset.template.fields" ng-click="ctrl.selectField(field)" style="cursor: pointer">
                                        {{ field.name }}
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="row in ctrl.dataset.data.records">
                                    <td ng-repeat="field in ctrl.dataset.template.fields">
                                        {{ row[field.name] }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                     <ul uib-pagination
                        total-items="ctrl.total_count"
                        items-per-page="ctrl.per_page"
                        ng-model="ctrl.page"
                        ng-change="ctrl.changeDataPage()"
                        max-size="5"
                        boundary-links="true"
                    ></ul>
                </div>
            </div>
        </div>
    </div>
</div>

