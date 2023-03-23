<?php

$user = $data["user"];
$community = $data["community"];
$error = $data["error"];

$rrid_reports = RRIDReport::loadArrayBy(Array("uid"), Array($user->id));
$base_uri = Community::fullURLStatic($community) . "/rin/rrid-report";

?>

<div id="rrid-report-overview" ng-controller="overviewController as ctrl">
    <?php if(isset($error)): ?>
        <div class="row">
            <?php if($error == 1): ?>
                <p style="color:red">You have already created an RRID report with that name.  Please create one with a unique name.</p>
            <?php elseif($error == 2): ?>
                <p style="color:red">Unable to create RRID report.</p>
            <?php endif ?>
        </div>
    <?php endif ?>
    <div class="row">
        <div class="col-md-12">
            <p>
              For NIH grant applications, dkNET has created an automated tool to enable researchers to add individual resources, gather more information and <a target="_blank" href="https://dknet.org/about/authentication-report">generate an Authentication Report including the authentication plans of key biological resources</a> to <a target="_blank" href="https://dknet.org/about/NIH-Policy-Rigor-Reproducibility">comply with the NIH Submission Policy</a>.
            </p>
            <p>
                Create a New Report
                <?php if (!$user): ?>
                  <p>
                    * To get started: create an NIDDK Information Network account or log into your existing account.
                    <a href="javascript:void(0)" class="btn btn-primary btn-login">Create New Account or Login</a>
                  </p>
                <?php endif ?>
                <ol>
                    <p>
                        Step 1. Click "New authentication report" and create report<br />
                        Step 2. Find and add your resource(s)<br />
                        Step 3. Check resource information by clicking resource name under Report Items<br />
                        Step 4. Preview the report<br />
                        Step 5. Save a report and generate a downloadable pdf with date and timestamps.<br />
                    </p>
                </ol>
            </p>
            <?php if($user): ?>
                <p>
                    <a href="javascript:void(0)" class="btn btn-primary" ng-click="ctrl.newReport()">New authentication report</a>
                    <!--<a target="_blank" href="https://dknet.org/about/rr_tutorial"><button class="btn btn-default">Step by step tutorial</button></a>-->
                </p>
            <?php endif ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <p>
                <?php echo \helper\htmlElement("rin/rrid-report-disclaimer") ?>
            </p>
        </div>
    </div>
    <?php if(!empty($rrid_reports)): ?>
        <h2>My authentication reports:</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Description</th>
                    <th>Created on</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($rrid_reports as $rr): ?>
                    <?php
                        $updated = false;
                        foreach($rr->items() as $rri) {
                            if($rri->updated_flag == 1) {
                                $updated = true;
                                break;
                            }
                        }
                    ?>
                    <tr>
                        <td>
                            <?php if($updated) echo \helper\htmlElement("notification-inline", Array("text" => "NEW DATA")); ?>
                            <a href="<?php echo $base_uri . "/" . $rr->id ?>"><?php echo $rr->name ?> <i class="fa fa-external-link"></a></i>
                        </td>
                        <td><?php echo $rr->description ?></td>
                        <td><?php echo date("h:ia F j, Y", $rr->timestamp) ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php endif ?>

    <script type="text/ng-template" id="add-report-template.html">
        <div method="post" id="name-form" action="/forms/rrid-report-forms/new-rrid-report.php" class="sky-form" enctype="multipart/form-data">
            <div class="modal-header"><h2>Create new authentication report</h2></div>
            <div class="modal-body">
                <form class="sky-form" ng-submit="submit()">
                    <section>
                        <p class="text-danger">
                            {{ create_error }}
                        </p>
                    </section>
                    <section>
                        <label class="label">Report name</label>
                        <label class="input">
                            <input ng-model="name" type="text" name="name" required>
                        </label>
                    </section>
                    <section>
                        <label class="label">Description</label>
                        <label class="input">
                            <textarea ng-model="description" name="description" style="width:100%" required></textarea>
                        </label>
                    </section>
                    <section>
                        <button class="btn btn-success">Submit</button>
                        <button ng-click="cancel()" class="btn btn-danger">Cancel</button>
                    </section>
                </form>
            </div>
        </div>
    </script>
</div>

<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>
<script src="/js/module-error.js"></script>
<script>
$(function() {
    var app = angular.module("rridReportOverviewApp", ["errorApp", "ui.bootstrap"]);

    app.controller("overviewController", ["$log", "$uibModal", function($log, $uibModal) {
        var that = this;

        this.newReport = function() {
            var modal_instance = $uibModal.open({
                animation: true,
                templateUrl: "add-report-template.html",
                controller: "addTemplateModalController"
            });
            modal_instance
        };
    }]);

    app.controller("addTemplateModalController", ["$http", "$log", "$uibModalInstance", "$scope", function($http, $log, $uibModalInstance, $scope) {
        $scope.submit = function() {
            var data = {
                name: $scope.name,
                description: $scope.description
            };
            $http.post("/api/1/rrid-report/new", data)
                .then(function(response) {
                    window.location.href = (window.location.pathname.split("overview")[0] + response.data.data.id).replace("account/rrid-report", "rin/rrid-report/");  // goto report directly -- Vicky-2019-4-18
                    $uibModalInstance.close();
                }, function(error) {
                    $scope.create_error = "There was a problem creating your new authentication report.  Please try again";
                });
        };

        $scope.cancel = function() {
            $scope.name = "";
            $scope.description = "";
            $uibModalInstance.dismiss();
        };
    }]);

    angular.bootstrap(document.getElementById("rrid-report-overview"), ["rridReportOverviewApp"]);
});
</script>
