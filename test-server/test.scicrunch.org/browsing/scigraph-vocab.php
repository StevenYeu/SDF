<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>
<script src="/js/module-error.js"></script>
<script src="/js/scigraph-vocab.js"></script>

<div class="container" ng-app="vocabApp" ng-cloak>
    <div ng-controller="searchCtrl as sc">
        <div class="panel panel-success">
            <div class="panel-heading clearfix">
                <div class="col-md-3">
                    <h3 ng-hide="sc.q_prev === ''">Search: {{ sc.q_prev }}</h3>
                </div>
                <div class="col-md-6">
                    <form ng-submit="sc.search()">
                        <input ng-model="sc.q" placeholder="term" />
                        <button class="btn btn-success">Search</button>
                    </form>
                </div>
            </div>
            <div class="panel-body">

                <div ng-repeat="(key, results) in sc.results">
                    <h3>{{ key }}</h3>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th ng-repeat="header in sc.table_titles">{{ header }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="res in results">
                                    <td ng-repeat="field in sc.table_fields">
                                        {{ res[field] }}
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <hr/>
                </div>

            </div>
        </div>
    </div>
</div>
