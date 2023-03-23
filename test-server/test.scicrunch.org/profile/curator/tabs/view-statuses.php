<script src="/js/angular-1.7.9/angular.min.js"></script>
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/angularjs/1.0.3/angular-sanitize.js"></script>

<div class="tab-pane fade in active" ng-app="viewsApp" ng-controller="viewsController" ng-cloak>
    <div class="container">
        <input type="checkbox" ng-model="showHidden"> Show hidden views
    </div>
    <div class="table-search-v2 margin-bottom-20">
        <div class="table-responsive">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Nif ID</th>
                        <th>Date Last Checked</th>
                        <th>Production Date</th>
                        <th>Current Date</th>
                        <!--<th> &lt; 1 Month</th>-->
                        <th>Current Status</th>
                        <th>In SciCrunch</th>
                        <th>In Disco</th>
                        <th>Result Count</th>
                        <th>Count Difference</th>
                        <th>Page Loads</th>
                        <th>SciCrunch Title</th>
                        <th>SciCrunch Description</th>
                        <th>SciCrunch Has Data</th>
                        <th>Valid Row Link</th>
                        <th>Valid Description Link</th>
                        <th>Date Added</th>
                        <th>In Production </th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="t1 in viewstats" ng-style="highlightRow(t1)" ng-show="!t1.hidden || showHidden">
                        <td><a ng-href="/scicrunch/data/source/{{t1.nif_id}}/search">{{ t1.view_name }}</a></td>
                        <td><a ng-href="/scicrunch/data/source/{{t1.nif_id}}/search">{{t1.nif_id}}</a></td>
                        <td>{{t1.date_checked}}</td>
                        <td ng-bind="changeProdDate(t1.production_date)"></td>
                        <td>{{t1.curr_date}}</td>
                        <!--<td><check-status status="t1.exceeds_month"></check-status></td>-->
                        <td>{{t1.curr_status}}</td>
                        <td><check-status status="t1.insc"></check-status></td>
                        <td><check-status status="t1.indisco"></check-status></td>
                        <td>{{t1.final_count}}</td>
                        <td><view-count-diff status="t1.good_count_diff" count="t1.count_diff"></view-count-diff></td>
                        <td><check-status status="t1.sc_page_loads"></check-status></td>
                        <td><check-status status="t1.sc_title"></check-status></td>
                        <td><check-status status="t1.sc_descr"></check-status></td>
                        <td><check-status status="t1.sc_has_data"></check-status></td>
                        <td><check-status status="t1.sc_valid_link"></check-status></td>
                        <td><check-status status="t1.sc_descr_link"></check-status></td>
                        <td>{{t1.date_added}}</td>
                        <td><check-status status="t1.in_production"></check-status></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script>

var views_app = angular.module('viewsApp', []);

views_app.controller('viewsController', function ($scope, $log, $http, $sce) {
    $scope.showHidden = false;

    var hiddenViews = ["nlx_152892-3", "nlx_157982-1", "nlx_152892-1", "nlx_151737-1", "nlx_158095-2", "nlx_154720-1", "nlx_29861-1", "nlx_158095-1", "nlx_152525-14", "nif-0000-08127-5", "nif-0000-00383-1", "nlx_158375-20", "nif-0000-21427-14", "nlx_153944-1", "SCR_014999-1", "nlx_158375-21", "nlx_154697-17", "nlx_152892-2", "nif-0000-10159-1", "nif-0000-07730-3", "nlx_154720-1", "SCR_010494-1"];

    $http({
        url: '/api/1/viewstatuses',
        dataType: "json",
        method: 'GET',
        data: '',
        headers: {
            'Accept': 'application/json'
        }
    })
    .success(function (response) {
        $scope.viewstats = response.data;

        $scope.viewstats.sort(function(a,b) {
            return parseFloat(a.mark) - parseFloat(b.mark);
        });

        for(var i = 0; i < $scope.viewstats.length; i++) {
            if(hiddenViews.indexOf($scope.viewstats[i].nif_id) !== -1) {
                $scope.viewstats[i].hidden = true;
            } else {
                $scope.viewstats[i].hidden = false;
            }
        }

        $scope.trustAsHtml = function(string) {
        return $sce.trustAsHtml(string);
    };

    $scope.highlightRow = function(view) {
        if (view.hidden) {
            return {"background-color": "#AAA788"};
        } else if (view.mark == "0") {
            return {"background-color": "#FFFBCC"};
        } else {
            return;
        }
    };

    $scope.changeProdDate = function(string) {
        if (!string) {
            return "";
        } else {
            return string;
        }
    };

  });
});

views_app.directive("checkStatus", function() {
    return {
        restrict: "E",
        scope: {
            status: "="
        },
        template: '<i class="fa fa-check" ng-show="status == 1"></i><i class="fa fa-times" style="color:red" ng-hide="status == 1"></i>'
    };
});

views_app.directive("viewCountDiff", function() {
    return {
        restrict: "E",
        scope: {
            status: "=",
            count: "="
        },
        template: '<span ng-show="status == 1">{{ count }}</span><span ng-hide="status == 1" style="color:red">{{ count }}</span>'
    };
});

</script>
