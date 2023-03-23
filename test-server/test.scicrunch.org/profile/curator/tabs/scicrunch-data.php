<?php

if(!isset($_SESSION["user"]) || $_SESSION["user"]->role < 2) return;

?>

<style>
    .field-selected {
        color: black;
    }
    .scicrunch-data-table {
        font-size: 8pt;
    }
</style>

<div class="tab-pane fade in active" id="scicrunch-data-app" ng-controller="dataController as dc">
    <div class="table-search-v2 margin-bottom-20">
        <select ng-model="dc.selectedType" ng-change="dc.updateType()">
            <option ng-repeat="type in dc.types" value="{{type}}">{{type}}</option>
        </select>
        <hr/>
        <div class="table-responsive" ng-show="!!dc.selectedType">
            <h3>{{ dc.selectedType }} - {{ dc.dataCount }}</h3>
            <form ng-submit="dc.search()" style="margin-bottom:20px">
                <input type="text" ng-model="dc.query" />
                <button class="btn btn-success">Search</button>
            </form>
            <ul uib-pagination
                total-items="dc.dataCount"
                items-per-page="dc.per_page"
                ng-model="dc.page"
                max-size="7"
                boundary-links="true"
                ng-change="dc.search()"
            ></ul>
            <table class="table table-hover scicrunch-data-table">
                <thead>
                    <tr>
                        <th ng-repeat="field in dc.data[0]">
                            {{ field.name }}
                            <a href="javascript:void(0)" ng-click="dc.sort(field.name, 'asc')" ng-class="{'field-selected': dc.sortField == field.name && dc.sortDir == 'asc'}">
                                <i class="fa fa-sort-asc"></i>
                            </a>
                            <a href="javascript:void(0)" ng-click="dc.sort(field.name, 'desc')" ng-class="{'field-selected': dc.sortField == field.name && dc.sortDir == 'desc'}">
                                <i class="fa fa-sort-desc"></i>
                            </a>
                        </th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="datum in dc.data">
                        <td ng-repeat="field in datum">
                            {{ field.value }}
                        </td>
                    </tr>
                </tbody>
            </table>
            <ul uib-pagination
                total-items="dc.dataCount"
                items-per-page="dc.per_page"
                ng-model="dc.page"
                max-size="7"
                boundary-links="true"
                ng-change="dc.search()"
            ></ul>
        </div>
    </div>
</div>

<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/module-scicrunch-data.js"></script>
