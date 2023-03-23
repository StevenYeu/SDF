<?php

$lab = $data["lab"];
$community = $data["community"];
$user = $data["user"];

if(!$lab || !$community || !$user || !$lab->isModerator($user)) {
    return;
}

$user_membership = LabMembership::loadBy(Array("labid", "uid"), Array($lab->id, $user->id));
$lab_members = LabMembership::loadArrayBy(Array("labid"), Array($lab->id));

?>

<div id="lab-management-app" ng-controller="labManagementController as ctrl">
    <div class="row">
        <div class="col-md-8">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">Datasets <a data-toggle="tooltip" title='Tutorial on "How to Share/Release/Request to Publish a Dataset"' href="<?php echo $community->fullURL() ?>/about/tutorials?#share_release_publish" target="_blank"><i class="fa fa-question-circle"></i></a></div>
                </div>
                <div class="pre-scrollable panel-body" style="max-height: 60vh;">
                    <table class="table">
                        <tbody>
                            <tr ng-repeat="dataset in ctrl.lab_datasets">
                                <td width="70%">
                                    <a class="lab-link" ng-href="<?php echo $community->fullURL() ?>/lab/dataset?labid=<?php echo $lab->id ?>&datasetid={{ dataset.id }}">{{ dataset.name }}</a>
                                </td>
                                <td>
                                    <a class="lab-link" href="javascript:void(0)" ng-click="ctrl.changeDatasetStatus(dataset)"><span ng-style="{'color': dataset.lab_status_color}">{{ dataset.lab_status_pretty }}</span></a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script type="text/ng-template" id="change-dataset-status-modal.html">
        <div class="modal-header">
            <h3 class="modal-title">Change Dataset status</h3>
        </div>
        <div class="modal-body">
            <div>
                <select ng-model="status">
                    <option ng-repeat="stat in statuses" value="{{stat}}">{{ stat | datasetStatusFilter }}</option>
                </select>
            </div>

        </div>
        <div class="modal-footer" style="border-top: 0px">
            <div>
                <button class="btn btn-success" ng-click="done()">Update status</button>
                <button class="btn btn-danger" ng-click="cancel()">Cancel</button>
            </div>
        </div>
        <div class="modal-footer">
            <div style="text-align: left; padding-top: 10px">
                <p>Reminder: Changing the status will change who can view and access the dataset. A figure showing the Access of ODC-SCI data spaces is below:<p>
                <img src="/upload/community-components/ODC_Data_Access.png" width="500">
                <p>For more information, see the "<a href="/about/help#privacy" target="_blank">How does privacy work on the ODC-SCI</a>" section of the General Help/FAQ page.</p>
            </div>
        </div>
    </script>

    <script type="text/ng-template" id="user-status-confirm-modal.html">
        <div class="modal-header">
            <h3 class="modal-title">{{ title }}</h3>
        </div>
        <div class="modal-body">
            <div>
                Are you sure you want to {{ action }}?
            </div>
            <div>
                <button class="btn btn-success" ng-click="ok()">Ok</button>
                <button class="btn btn-danger" ng-click="cancel()">Cancel</button>
            </div>
        </div>
    </script>
</div>
<input type="hidden" id="data-uid" value="<?php echo $_SESSION["user"]->id ?>" />
