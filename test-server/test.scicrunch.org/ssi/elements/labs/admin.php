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

<style>
    .app-modal-window .modal-dialog {
        width: 600px;
    }
    .app-modal-window2 {
        width: 1000px;
    }

    a.white_hover:hover { text-decoration: underline; color: white; }

    .dataTables_filter, .dataTables_paginate, .dataTables_info, .dataTables_length { display: none; }

    .panel > .panel-heading {
        background-image: none;
        background-color: #f7f7f7;
        color: black;
    }
</style>

<script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/angular-datatables/0.6.4/angular-datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.23/css/dataTables.bootstrap.min.css">

<div id="lab-management-app" ng-controller="labManagementController as ctrl">
                 
    <div class="row">
        <div class="col-md-6">
            <h2 style="display:inline">{{ ctrl.lab.name }} <a href="javascript:void(0)" ng-click="ctrl.changeMode('edit')"><i class="fa fa-pencil-square-o" aria-hidden="true" style="font-size: .8em; color: #427c98;"></i><span class="lab-link" style="font-size: 14px;">Edit Lab Info</span></a></h2>
            <h4>{{ ctrl.lab.public_description }}</h4>
        </div>
        <div class="col-md-6">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="panel-title">Users <a data-toggle="tooltip" title='Tutorial on "How to approve/promote lab members"' href="<?php echo $community->fullURL() ?>/about/tutorials?#users" target="_blank"><i class="fa fa-question-circle"></i></a></div>
                </div>
                <div class="panel-body scroll-height-200">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Level</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="user in ctrl.users" ng-show="user.level == 0">
                                <td>{{user.username}}</td>
                                <td>{{user.level | levelFilter}}</td>
                                <td>
                                        <button ng-click="ctrl.updateUserLevelConfirm(user, 1, 'Approval', 'approve this user')" class="btn btn-success btn-xs">Approve</button>
                                        <button ng-click="ctrl.updateUserLevelConfirm(user, 0, 'Rejection', 'reject this user')" class="btn btn-danger btn-xs">Reject</button>
                                </td>
                            </tr>
                        
                            <tr ng-repeat="user in ctrl.users" ng-show="user.level != 0">
                                <td>{{user.username}}</td>
                                <td>{{user.level | levelFilter}}</td>
                                <td>
                                    <span ng-show="user.level > 0 && user.level < ctrl.user.lab_level">
                                        <button ng-click="ctrl.updateUserLevelConfirm(user, user.level + 1, 'Promote user', 'promote this user')" class="btn btn-primary btn-xs">Promote</button>
                                        <button ng-show="user.level > 1" ng-click="ctrl.updateUserLevelConfirm(user, user.level - 1, 'Demote user', 'demote this user')" class="btn btn-primary btn-xs">Demote</button>
                                        <button ng-click="ctrl.updateUserLevelConfirm(user, 0, 'Remove user', 'remove this user from the lab')" class="btn btn-danger btn-xs">Remove from lab</button>
                                    </span>
                                    <span ng-show="user.uid == ctrl.user.id && ctrl.user.can_demote_self">
                                        <button ng-show="user.level > 1" ng-click="ctrl.updateUserLevelConfirm(user, user.level - 1, 'Demote self', 'demote yourself')" class="btn btn-primary btn-xs">Demote self</button>
                                    </span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <div class="row" ng-show="ctrl.mode == 'edit'">
        <div class="col-md-6">
            <h4>Update lab info</h4>
            <form class="form" ng-submit="ctrl.updateLabInfo()">
                <table class="table">
                    <tbody>
                        <tr>
                            <td width="25%">
                                Lab name
                            </td>
                            <td>
                                <input type="text" ng-model="ctrl.lab_edit.name" size="50" />
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Public description
                            </td>
                            <td>
                                <textarea ng-model="ctrl.lab_edit.public_description" cols="80" rows="4"></textarea>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                Lab broadcast message
                            </td>
                            <td>
                                <textarea ng-model="ctrl.lab_edit.broadcast_message" cols="80" rows="4"></textarea>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <input type="submit" class="btn btn-success" value="Update" />
                <div class="pull-right">                            
                    <a href="javascript:void(0)" ng-click="ctrl.changeMode('data')" ng-show="ctrl.mode != 'data'">
                        <div class="btn btn-primary">Back to datasets</div>
                    </a>
                </div>

                <span ng-show="ctrl.updating_info">
                    Updating <i class="fa fa-spinner fa-spin"></i>
                </span>
                <p ng-show="ctrl.error_info" class="text-danger">
                    There was an error updating the dataset info.
                </p>
            </form>
            
        </div>
    </div>

    <div class="row" ng-hide="ctrl.mode == 'edit'">
        <div class="col-md-12">
            <div class="panel panel-default">
                <div class="panel-heading">
                    <div class="panel-title">Datasets in <strong>{{ ctrl.lab.name }}</strong> <a style="float:right" href="<?php echo $community->fullURL() ?>/about/tutorials?#share_release_publish" target="_blank" class="white_hover"><i class="fa fa-question-circle"></i> Instructions for publishing datasets/requesting DOI</a></div>
                </div>
                <div class="pre-scrollable panel-body" style="max-height: 60vh;">
                    <label ng-class="{active: active1}">
                        <input type="checkbox" ng-model="active1" /> Only show my datasets
                    </label>
                <div class="pull-right">Search <input type="text" id="filterbox" ng-model="filterbox" 
                                      ng-change="searchTable()" ></div>
                    <table class="table table-bordered" datatable="ng"  dt-options="dtOptions" dt-instance="dtInstance"  dt-column-defs="dtColumnDefs">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th width="30%">Dataset</th>
                                <th>Uploader</th>
                                <th>Records</th>
                                <th>Fields</th>
                                <th>Last Updated</th>
                                <th width="15%">Data Space Status</th>
                                <th width="10%">Editorial Status</th>
                                <th width="12%">DOI Review Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="dataset in ctrl.lab_datasets" ng-if="dataset.curation_status == 'curation-approved'" style="background-color: #fcffa4" ng-hide="active1 && dataset.uid != ctrl.user.id">
                                <td>{{ dataset.id }}</td>
                                <td>
                                    <a class="lab-link" ng-href="<?php echo $community->fullURL() ?>/lab/dataset?labid=<?php echo $lab->id ?>&datasetid={{ dataset.id }}" id="{{ dataset.id }}">{{ dataset.name }}</a>
                                </td>
                                <td>{{ dataset.owner_reversed }}</td>
                                <td align="right">{{ dataset.total_records_count }}</td>
                                <td align="right">{{ dataset.field_set.length }}</td>
                                <td><span style="white-space: nowrap;">{{ dataset.last_updated_time * 1000| date:'yyyy-MM-dd' }}</span></td>
                                <td>
                                    <a class="lab-link" href="javascript:void(0)" ng-click="ctrl.changeDatasetStatus(dataset)"><button class="btn btn-xs" style="color: white; background-color: {{dataset.lab_status_color }} ">{{ dataset.lab_status_pretty }}</button></a>
                                </td>
                                <td>{{ dataset.editor_status }}</td>
                                <td><a class="lab-link" href="javascript:void(0)" ng-click="ctrl.publishDOI(dataset)"><button class="btn btn-xs" style="color: white; background-color: green">Publish DOI</button></a></td>
                            </tr>
                            <tr ng-repeat="dataset in ctrl.lab_datasets" ng-if="dataset.lab_status == 'pending'" style="background-color: #fcffa4" ng-hide="active1 && dataset.uid != ctrl.user.id">
                                <td>{{ dataset.id }}</td>
                                <td>
                                    <a class="lab-link" ng-href="<?php echo $community->fullURL() ?>/lab/dataset?labid=<?php echo $lab->id ?>&datasetid={{ dataset.id }}" id="{{ dataset.id }}">{{ dataset.name }}</a>
                                </td>
                                <td>{{ dataset.owner_reversed }}</td>
                                <td align="right">{{ dataset.total_records_count }}</td>
                                <td align="right">{{ dataset.field_set.length }}</td>
                                <td><span style="white-space: nowrap;">{{ dataset.last_updated_time * 1000| date:'yyyy-MM-dd' }}</span></td>
                                <td>
                                    <a class="lab-link" href="javascript:void(0)" ng-click="ctrl.changeDatasetStatus(dataset)"><button class="btn btn-xs" style="color: white; background-color: {{dataset.lab_status_color }}">{{ dataset.lab_status_pretty }}</button></a>
                                </td>
                                <td>{{ dataset.editor_status }}</td>
                                <td>{{dataset.curation_status }}</td>
                            </tr>
                            <tr ng-repeat="dataset in ctrl.lab_datasets" ng-if="dataset.curation_status == 'request-doi-locked' || dataset.curation_status == 'request-doi-unlocked'" ng-hide="active1 && dataset.uid != ctrl.user.id">
                                <td>{{ dataset.id }}</td>
                                <td>
                                    <a class="lab-link" ng-href="<?php echo $community->fullURL() ?>/lab/dataset?labid=<?php echo $lab->id ?>&datasetid={{ dataset.id }}">{{ dataset.name }}</a>
                                </td>
                                <td>{{ dataset.owner_reversed }}</td>
                                <td align="right">{{ dataset.total_records_count }}</td>
                                <td align="right">{{ dataset.field_set.length }}</td>
                                <td><span style="white-space: nowrap;">{{ dataset.last_updated_time * 1000| date:'yyyy-MM-dd' }}</span></td>
                                <td>
                                    <a class="lab-link" href="javascript:void(0)" ng-click="ctrl.changeDatasetStatus(dataset)"><button class="btn btn-xs" style="color: white; background-color: {{dataset.lab_status_color }} ">{{ dataset.lab_status_pretty }}</button></a>
                                </td>
                                <td>{{ dataset.editor_status }}</td>
                                <td><span ng-if="dataset.curation_status=='request-doi-locked'">DOI Requested <i class='fa fa-lock'></i></span><span ng-if="dataset.curation_status=='request-doi-unlocked'">DOI Requested <i class='fa fa-unlock'></i></span></td>
                            </tr>
                            <tr ng-repeat="dataset in ctrl.lab_datasets" ng-if="(dataset.lab_status != 'pending') && (dataset.curation_status != 'request-doi-locked' && dataset.curation_status != 'request-doi-unlocked' && dataset.curation_status != 'curation-approved')" ng-hide="active1 && dataset.uid != ctrl.user.id">
                                <td>{{ dataset.id }}</td>
                                <td>
                                    <a class="lab-link" ng-href="<?php echo $community->fullURL() ?>/lab/dataset?labid=<?php echo $lab->id ?>&datasetid={{ dataset.id }}">{{ dataset.name }}</a>
                                </td>
                                <td>{{ dataset.owner_reversed }}</td>
                                <td align="right">{{ dataset.total_records_count }}</td>
                                <td align="right">{{ dataset.field_set.length }}</td>
                                <td><span style="white-space: nowrap;">{{ dataset.last_updated_time * 1000| date:'yyyy-MM-dd' }}</span></td>
                                <td>
                                    <a class="lab-link" href="javascript:void(0)" ng-click="ctrl.changeDatasetStatus(dataset)"><button class="btn btn-xs" style="color: white; background-color: {{dataset.lab_status_color }}">{{ dataset.lab_status_pretty }}</button></a>
                                </td>
                                <td>{{ dataset.editor_status }}</td>
                                <td>{{ dataset.curation_status_pretty }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script type="text/ng-template" id="change-dataset-status-modal.html">
        <div ng-if="mode == 'status'">
            <div class="modal-header">
                <h3 class="modal-title">Change Dataset Status</h3>
                <strong>Dataset:</strong> {{ dataset_name }}<br />
                <strong>Current Status:</strong> {{ current_status | labStatusPrettyFilter }}
            </div>
            <div class="modal-body">
                <form ng-submit="changeStatus()" >
                <div class="row">
                    <div class="col-md-12">
                        <div ng-show="current_status == 'not-submitted'" ng-init="status=='not-submitted'">
                            <ul style="list-style-type:none; padding-inline-start: 0px;">
                                <li><input type="radio" ng-model="status" value="not-submitted"> <span style="color: {{ 'not-submitted' | datasetStatusColorFilter }}"><i class="fa fa-square"></i></span> Personal Space</li>
                                <li><input type="radio" ng-model="status" value="approved-internal"> <span style="color: {{ 'approved-internal' | datasetStatusColorFilter }}"><i class="fa fa-square"></i></span> Share to Lab Space</li>
                                <li><input type="radio" ng-model="status" value="approved-community"> <span style="color: {{ 'approved-community' | datasetStatusColorFilter }}"><i class="fa fa-square"></i></span> Release to Community Space</li>
                            </ul>
                        </div>

                        <div ng-show="current_status == 'approved-internal'" ng-init="status=='approved-internal'">
                            <ul style="list-style-type:none; padding-inline-start: 0px;">
                                <li><input type="radio" ng-model="status" value="not-submitted"> <span style="color: {{ 'not-submitted' | datasetStatusColorFilter }}"><i class="fa fa-square"></i></span> Move back to Personal Space</li>
                                <li><input type="radio" ng-model="status" value="approved-internal"> <span style="color: {{ 'approved-internal' | datasetStatusColorFilter }}"><i class="fa fa-square"></i></span> Lab Space</li>
                                <li><input type="radio" ng-model="status" value="approved-community"> <span style="color: {{ 'approved-community' | datasetStatusColorFilter }}"><i class="fa fa-square"></i></span> Release to Community Space</li>
                            </ul>
                        </div>

                        <div ng-show="current_status == 'approved-community'" ng-init="status=='approved-community'">
                            <ul style="list-style-type:none; padding-inline-start: 0px;">
                                <li><input type="radio" ng-model="status" value="not-submitted"> <span style="color: {{ 'not-submitted' | datasetStatusColorFilter }}"><i class="fa fa-square"></i></span> Move back to Personal Space</li>
                                <li><input type="radio" ng-model="status" value="approved-internal"> <span style="color: {{ 'approved-internal' | datasetStatusColorFilter }}"><i class="fa fa-square"></i></span> Move back to Lab Space</li>
                                <li><input type="radio" ng-model="status" value="approved-community"> <span style="color: {{ 'approved-community' | datasetStatusColorFilter }}"><i class="fa fa-square"></i></span> Community Space</li>
                            </ul>
                        </div>

                        <div ng-show="current_status == 'approved-doi'">
                            <p>This Dataset has been published to the Public data space and cannot be moved to other data spaces.You can find the public data page at [landing page link].</p>

                            <p>If you need to make changes or delete the published dataset, please review the [Version Control Policy] and contact data@<?php echo $community->portalName; ?>.org.</p>
                        </div>

                        <div ng-show="current_status == 'pending'">
                            <p>Once shared to the Lab space, the dataset can be released to the Community or have a DOI requested. A dataset can always be moved back to the Personal space.</p>

                            <button class="btn btn-success" ng-click="done('approve-internal')">Approve Share to Lab Space</button>
                            <button class="btn btn-danger" ng-click="done('rejected')">Reject (keep in Personal Space)</button>
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<button class="btn btn-danger" ng-click="cancel()">Cancel</button>
                        </div>
                    </div>
                </div>
                </form>

                <div class="row">
                    <div class="col-md-7">
                        <span ng-if="current_status != 'pending'" >
                            <span ng-if="current_status != 'approved-doi'" >
                                <button type="submit" class="btn btn-success" ng-disabled="current_status == status" ng-click="done(status)">Update Status</button>
                            </span>
                            <button class="btn btn-danger" ng-click="cancel()">Cancel</button>
                        </span>
                    </div>
                    <div class="col-md-5">
                        <input ng-click="changeMode('request_doi_form')" type="submit" class="btn btn-success" value="Request DOI Form" ng-if="current_status != 'pending'" ng-show="curation_status == null" />
                        
                    </div>
                </div>    
            </div>
            <div class="modal-footer">
                <div style="text-align: left; padding-top: 10px">
                    <p>Reminder: Changing the status will change who can view and access the dataset. A figure showing the Access of <?php echo strtoupper($community->portalName); ?> data spaces is below:<p>
                    <img src="/upload/community-components/ODC_Data_Access_short.png" width="500">
                    <p>For more information, see the "<a href="/about/help#privacy" target="_blank">How does privacy work on the <?php echo strtoupper($community->portalName); ?></a>" section of the General Help/FAQ page.</p>
                </div>
            </div>
        </div>

        <div ng-if="mode == 'request_doi_form'">
            <form ng-submit="submitRequestDOIForm()" >
            <div class="modal-header">
                <h3>What do I need before I request a DOI/dataset publication?</h3>
                <p>When you submit a dataset for DOI and publication, the dataset will undergo a curation process with the ODC Data Team. During the process, the Data Team will work with you to ensure that your dataset meets the FAIR principles and data standards established by the <?php echo strtoupper($community->portalName); ?>. The Data Team will look for:
                    <ul>
                        <li style="margin-top:0px; margin-bottom: 0px">Proper dataset formatting</li>
                        <li style="margin-top:0px; margin-bottom: 0px">Complete and sufficient dataset metadata</li>
                        <li style="margin-top:0px; margin-bottom: 0px">Complete and sufficient dataset-associated data dictionary</li>
                    </ul>
                </p>

                <h3>How long will it take to receive a DOI and publish the dataset?</h3>
                <p>The dataset publication process can take a few weeks depending on the revisions needed for the 3 key components listed above. The <?php echo strtoupper($community->portalName); ?> Data Team will be contacting you to ensure that the required items are up to <?php echo strtoupper($community->portalName); ?> standards.</p>
                <p>If you have concerns about the process, please contact the Data Team at data@<?php echo $community->portalName; ?>.org.</p>
                <p>For more information, see the Help sections:<br />
                    "Dataset Publication checklist: What you need before publication" and "Minimal Dataset Standards for publication"</p>
                <p>Through the data review process, the Data Team will operate under the “Data Team Agreement” to make recommendations and changes to your dataset for publication.</p>

                <h3 class="modal-title">DOI/Publication Request Information</h3>
            </div>
            <div class="modal-body">
                <p>To help us prioritize your publication request, please fill out the following information (* fields are required):</p>
                <div class="form-group">
                <!-- PENDING DEADLINES -->
                <div class="form-group">
                    <label>Any pending deadlines</label><br />
                    <input type="text" ng-model="form.pending_deadlines" name="pending_deadlines" size="70" ng-init="form.pending_deadlines = ''">
                </div>

                <!-- JOURNALS INVOLVED -->
                <div class="form-group">
                    <label>Any journals involved</label><br />
                    <input type="text" ng-model="form.journals_involved" name="journals_involved" size="70" ng-init="form.journals_involved = ''">
                </div>

                <!-- RELEVANT DETAILS -->
                <div class="form-group">
                    <label>Other relevant details</label><br />
                    <input type="text" ng-model="form.relevant_details" name="relevant_details" size="70" ng-init="form.relevant_details = ''">
                </div>
                <div class="form-group">
                    <input type="checkbox" ng-model="Iagree"> I understand that the Data Team will view and propose changes to my dataset and associated files/metadata during the Request DOI process.
                </div>
            </div>
            <div class="modal-footer">
                <input type="hidden" name="stub" ng-model="form.stub" ng-init="form.stub ='can you see me'" >
                <button ng-if="Iagree" type="submit" class="btn btn-success" ng-disabled="form.modalForm.$invalid" ng-click="done_requestDOI(form)">Submit DOI Request</button>
                <button class="btn btn-warning" ng-click="cancel()">Close</button>
            </div>
        </form>
        </div>

    </script>

    <script type="text/ng-template" id="user-status-confirm-modal.html">
        <div class="modal-header">
            <h3 class="modal-title" ng-if="title=='Approval' || title=='Rejection'">Confirm Lab Member {{title}}</h3>
            <h3 class="modal-title" ng-if="title=='Promote user' || title=='Demote user' || title=='Demote self' || title=='Remove user'">{{title}}</h3>
        </div>
        <div class="modal-body" ng-if="title=='Approval'">
            <p>
                By approving this user to your lab, you will be giving them access to the datasets in your lab
                as well as unpublished datasets released to the Commons. Please verify that you recognize the 
                user and confirm your approval.
            </p>
            <div>
                <button class="btn btn-success" ng-click="ok()">Approve</button>
                <button class="btn btn-danger" ng-click="cancel()">Cancel</button>
            </div>
        </div>
        <div class="modal-body" ng-if="title!='Approval'">
            <p>
                Are you sure you want to {{ action }}?
            </p>
            <div>
                <button class="btn btn-success" ng-click="ok()">{{title}}</button>
                <button class="btn btn-danger" ng-click="cancel()">Cancel</button>
            </div>
        </div>
    </script>
</div>

    <input type="hidden" id="data-uid" value="<?php echo $_SESSION["user"]->id ?>" data-ng-model="data-uid" />