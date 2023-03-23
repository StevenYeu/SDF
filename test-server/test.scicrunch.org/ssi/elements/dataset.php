<?php
$dataset = $data["dataset"];
$community = $data["community"];
?>

<script src="/js/module-dataset-view.js"></script>
<div class="container" style="margin-top: 20px">
    <input type="hidden" id="dataset-id" value="<?php echo $dataset->id ?>" />
    <div class="row" ng-app="datasetViewApp" ng-controller="datasetViewController as dvc" ng-cloak>
        <div class="col-md-10">
            <h2 ng-show="!dvc.dataset.long_name">{{ dvc.dataset.name }}</h2>
            <h2 ng-hide="!dvc.dataset.long_name">{{ dvc.dataset.long_name }}</h2>
            <p>lab status: {{ dvc.dataset.lab_status }}</p>
            <p>{{ dvc.dataset.description }}</p>
            <div class="row">
                <form class="col-md-4" ng-submit="dvc.changeQuery(dvc.search_query)">
                    <div class="input-group">
                        <input type="text" class="form-control" ng-model="dvc.search_query" />
                        <span class="input-group-btn"><input class="btn btn-success" type="submit" value="search" /></span>
                    </div>
                </form>
                <a href="/php/dataset-csv.php?datasetid=<?php echo $dataset->id ?>" target="_self"><div class="btn btn-primary">Download CSV <i class="fa fa-download"></i></div></a>
            </div>
        </div>
        <div class="col-md-2">
            <a href="javascript:void(0)">
                <div class="btn btn-success" ng-click="dvc.openGraphScatterPlot()">Scatter plot</div>
            </a>
        </div>
        <div class="row">
            <div class="col-md-12">
                <h4>{{ dvc.dataset.data.count }} results found<span ng-show="dvc.previous_search"> for the search '{{ dvc.previous_search }}'</span></h4>
            </div>
        </div>

        <div class="col-md-9" ng-hide="dvc.searching" style="overflow-x: auto; padding-top: 20px; padding-bottom: 20px">
            <table class="table">
                <thead>
                    <tr>
                        <th ng-repeat="field in dvc.dataset.field_set" ng-click="dvc.selectField(field)" style="cursor: pointer">
                            {{ field }}
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="row in dvc.dataset.data.records">
                        <td ng-repeat="field in dvc.dataset.field_set">
                            {{ row[field] }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <span>
                <uib-pagination
                    ng-hide="!dvc.dataset.data.records"
                    total-items="dvc.dataset.data.count"
                    items_per_page="dvc.per_page"
                    ng-model="dvc.page"
                    ng-change="dvc.changePage(dvc.page)"
                    max-size="5"
                    boundary-links="true"
                />
            </span>
        </div>
        <div class="col-md-9" ng-show="dvc.searching" style="height: 1000px">
            <h1 style="text-align: center; padding-top: 200px"><i class="fa fa-spinner fa-spin"></i></h1>
        </div>
        <div class="col-md-3">
            <div ng-hide="dvc.selected_field === null">
                <h4>CDE information</h4>
                <dl class="dl-horizontal">
                    <dt>Field name</dt><dd>{{ dvc.selected_field.name }}</dd>
                    <dt>CDE type</dt><dd>{{ dvc.selected_field.termid.label }}</dd>
                    <dt>CDE ILX identifier</dt><dd><a target="_blank" ng-href="/<?php echo Community::getPortalName($community) ?>/about/term/ilx/{{ dvc.selected_field.termid.ilx }}">{{ dvc.selected_field.termid.ilx }}</a></dd>
                    <dt>CDE definition</dt><dd>{{ dvc.selected_field.termid.definition }}</dd>
                </dl>
            </div>
        </div>
        <script type="text/ng-template" id="graph-scatter-plot.html">
            <div class="modal-header">
                <h3 class="modal-title">Scatter plot</h3>
            </div>
            <div class="modal-body">
                <div class="container">
                    <div class="col-md-6">
                        <p>Select two fields to plot against</p>
                        <ul>
                            <li style="cursor:pointer" ng-click="selectField(field)" ng-repeat="field in dataset.field_set" ng-if="selectedFields.indexOf(field) === -1">
                                {{ field }}
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <p>Selected fields</p>
                        <span style="color:red">{{ errorText }}</span>
                        <ul>
                            <li style="cursor:pointer" ng-click="unselectField(field)" ng-repeat="field in selectedFields">
                                {{ field }}
                            </li>
                        </ul>
                    </div>
                    <div ng-show="selectedFields.length == 2">
                        <button class="btn btn-success" ng-click="makeGraph()">Make graph</button>
                    </div>
                    <div id="graph-scatter-plot"></div>
                </div>
            </div>
        </script>
    </div>
</div>
