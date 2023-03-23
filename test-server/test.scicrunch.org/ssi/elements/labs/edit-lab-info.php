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
        <div class="col-md-9">
            <div class="panel panel-primary">
                <div class="panel-heading">
                    <div class="panel-title">
                        Information
                        |
                        <a class="lab-link" href="javascript:void(0)" ng-click="ctrl.toggleEditLabInfo()"><i class="fa fa-wrench"></i> edit</a>
                    </div>
                </div>
                <div class="panel-body">
                    <table class="table">
                        <tbody>
                            <tr>
                                <td width="25%">
                                    Lab name
                                </td>
                                <td ng-hide="ctrl.edit_lab_info_mode">
                                    {{ ctrl.lab.name }}
                                </td>
                                <td ng-show="ctrl.edit_lab_info_mode">
                                    <input type="text" ng-model="ctrl.lab_edit.name" />
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Public description
                                </td>
                                <td ng-hide="ctrl.edit_lab_info_mode">
                                    {{ ctrl.lab.public_description }}
                                </td>
                                <td ng-show="ctrl.edit_lab_info_mode">
                                    <textarea ng-model="ctrl.lab_edit.public_description" cols="80" rows="4"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Private description
                                </td>
                                <td ng-hide="ctrl.edit_lab_info_mode">
                                    {{ ctrl.lab.private_description }}
                                </td>
                                <td ng-show="ctrl.edit_lab_info_mode">
                                    <textarea ng-model="ctrl.lab_edit.private_description" cols="80" rows="4"></textarea>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    Lab broadcast message
                                </td>
                                <td ng-hide="ctrl.edit_lab_info_mode">
                                    {{ ctrl.lab.broadcast_message }}
                                </td>
                                <td ng-show="ctrl.edit_lab_info_mode">
                                    <textarea ng-model="ctrl.lab_edit.broadcast_message" cols="80" rows="4"></textarea>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <div ng-show="ctrl.edit_lab_info_mode">
                        <button class="btn btn-success" ng-click="ctrl.updateLabInfo()">Update</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>