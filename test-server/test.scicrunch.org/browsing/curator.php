<?php
    if(!isset($_SESSION["user"])){
        echo \helper\loginForm("You must be logged in to edit a resource");
        return;
    }

    $rid = (int)explode("SCR_", $id)[1];
    $resource = new Resource();
    $resource->getByID($rid);
    if(!$resource->id) {
        echo "<h3>This resource does not exist.</h3>";
        return;
    }
    
    if(!$resource->isAuthorizedOwner($_SESSION["user"]->id)) {
        echo "<h3>You are not the owner.</h3>";
        return;
    }
?>

    <!-- Manu add the below line to maintain header formatting in the resource edit page -->
    <link rel="stylesheet" href="/css/community-search.css">

<link rel="stylesheet" type="text/css" href="/css/curator.css" />
<link rel="stylesheet" type="text/css" href="/css/multiple-select.min.css" />
<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>
<script src="/js/multiple-select.min.js"></script>
<script src="/js/module-error.js"></script>
<script src="/js/module-curator-conversation.js"></script>
<script src="/js/module-resource.js"></script>
<script src="/js/curator.js"></script>
<script src="/js/module-resource-directives.js"></script>

<div class="container" ng-app="curatorApp" ng-cloak>
    <div class="row">
        <div class="row">
            <div class="col-md-6">
                <h2>{{rname.value}}</h2><h3>{{rid}}</h3>
                <h2 style="color:red" ng-show="is_duplicate">This resource is a duplicate</h2>
                <h3 ng-show="logged_in" ng-controller="resourceConversation as rc">
                    Discussion: {{ rc.resource_id }}
                    <i class="fa fa-plus-circle" style="cursor:pointer;color:blue" ng-hide="rc.conversation != undefined" ng-click="rc.createConversation()"></i>
                    <a target="_blank" ng-show="rc.conversation != undefined" ng-href="{{ '/account/messages?convID=' + rc.conversation.id }}"><i class="fa fa-comments" style="cursor:pointer;color:blue"></i></a>
                </h3>
            </div>
        </div>
        <ul class="nav nav-tabs nav-tabs-js">
            <li class="active"><a href="#resource-fields" data-toggle="tab">Resource Description</a></li>
            <li><a href="#resource-mentions" data-toggle="tab">Literature Mentions</a></li>
        </ul>

        <div class="tab-content">

            <div id="resource-fields" class="row tab-pane active">
                <!-- resourceFields -->
                <div class="sky-form col-md-8 col-xs-12" ng-controller="resourceFields as rf">
                    <div class="panel panel-success" id="fields-panel">
                        <div class="panel-heading clearfix"><h3 class="panel-title">Fields</h3>
                            <div class="col-md-2" ng-show="fields.changed"><button class="btn btn-success" ng-click="rf.save()">Save Changes</button></div>
                            <div class="col-md-2" ng-hide="fields.changed"><button class="btn" disabled="disabled">Up to date</button></div>
                            <?php if ($_SESSION["user"]->role > 0): ?>
                                <div class="col-md-2">
                                    <div class="btn-group">
                                        <div class="btn"
                                             ng-model="fields.curation_status"
                                             ng-class="{'btn-success': fields.curation_status == 'Curated', 'btn-primary': fields.curation_status == 'Pending'}"
                                             ng-click="fields.curate('Curated')"
                                        >Curated</div>
                                        <div class="btn"
                                             ng-model="fields.curation_status"
                                             ng-class="{'btn-danger': fields.curation_status == 'Rejected', 'btn-primary': fields.curation_status == 'Pending'}"
                                             ng-click="fields.curate('Rejected')"
                                        >Rejected</div>
                                        <div class="btn"
                                            ng-click="fields.rejectAll()"
                                        >Reject resource</div>
                                    </div>
                                </div>
                            <?php endif ?>
                            <div class="col-md-2">Status: {{ fields.curation_status }}</div>
                            <div class="col-md-2">Version {{fields.version}}</div>
                            <div class="col-md-2" ng-show="fields.submitter_email">Submitter email: {{fields.submitter_email}}</div>
                            <div class="col-md-2 form-inline">
                                <div class="form-group">
                                    <label>Type:</label>
                                    <span>
                                        <!--TODO: Initialize initial value on ajax load-->
                                        <select class="form-control" ng-model="fields.typeID" ng-change="changeResourceType()"
                                                ng-options="type.typeID as type.typeName for type in types" convert-to-number></select>
                                        <p ng-show="changingType">Submitting...</p>
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <div class="row" style="margin-bottom:10px;">
                                <div class="col-md-6">
                                    <select class="form-control"
                                            ng-model="field"
                                            ng-options="field.field for field in fields.fields | filter:{user_visible: true} | filter:{visible: false} | orderBy:'field'"
                                            ng-change="rf.add(field)">
                                        <option value="">-- Add new field --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-horizontal" id="form-fields">
                                <div class="form-group" ng-show="field.visible && field.user_visible" ng-repeat="field in fields.fields | orderBy:'add_time'">
                                    <label class="col-md-2 control-label">
                                        {{ field.field }}
                                    </label>
                                    <div class="col-md-10">
                                        <div ng-class="{false: 'input-group'}[field.required]">
                                            <input class="form-control" type="text" ng-show="field.type === 'text' || field.type === 'funding-types'" ng-model="field.value" placeholder="{{field.alt}}" ng-change="rf.change(field)" ng-model-options="{ debounce: 500 }"/>
                                            <textarea rows="15" class="form-control" ng-show="field.type === 'textarea'" ng-model="field.value" ng-change="rf.change(field)"></textarea>
                                            <span ng-if="field.type === 'resource-types'" ng-click="rf.change(field)">
                                                <multiple-autocomplete ng-if="field.type === 'resource-types'" suggestions-arr="fields.additional_types" ng-model="field.value"></multiple-autocomplete>
                                            </span>
                                            <span class="input-group-addon" ng-hide="field.required"><i class="input-button delete-button glyphicon glyphicon-remove" ng-click="rf.remove(field)" ng-model-option="{ debound: 500 }"></i></span>
                                        </div>
                                        <p style="color:red" ng-show="field.valid === false"><i>{{ field.valid_message }}</i></p>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <select class="form-control"
                                            ng-model="field"
                                            ng-options="field.field for field in fields.fields | filter:{user_visible: true} | filter:{visible: false} | orderBy:'field'"
                                            ng-change="rf.add(field)">
                                        <option value="">-- Add new field --</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /resourceFields -->

                <div class="sky-form col-md-4 col-xs-12">
                    <!-- relationships -->
                    <div class="panel panel-success" ng-controller="resourceRelationships as rr">
                        <div class="panel-heading clearfix"><h3 class="panel-title">Relationships ({{relationships.totalCount}})</h3>
                            <div class="container">
                                <div class="row">
                                    Check here if this resource is a duplicate of another resource <input type="checkbox" ng-model="rr.checked_duplicate" />
                                </div>
                                <div class="row" ng-show="rr.checked_duplicate && !rr.is_duplicate">
                                    This resource is a duplicate of
                                    <input
                                            type="text"
                                            ng-model="duplicate_resource"
                                            placeholder="duplicated resource"
                                            uib-typeahead="result.name for result in fields.searchResource($viewValue)"
                                            typeahead-min-length="2"
                                            typeahead-on-select="selectDuplicate($item, $model, $label)"
                                            typeahead-wait-ms="250"
                                            typeahead-select-on-blur="true"
                                    />
                                    <button class="btn btn-success" ng-click="addDuplicate()">Add</button>
                                </div>
                                <div class="row" ng-show="rr.is_duplicate">
                                    <ul class="list-inline up-ul">
                                        <li>This resource is a duplicate of <a target="_self" ng-href="/browse/curator/{{relationships.duplicate_of.other_rid}}">{{relationships.duplicate_of.other_name}}</a></li>
                                        <li><i class="input-button delete-button glyphicon glyphicon-remove" ng-click="removeDuplicate(relationships.duplicate_of.other_rid)"></i></li>
                                    </ul>
                                </div>
                                <div resource-relationships-add-dir></div>
                                <div resource-relationships-filter-dir></div>
                            </div>
                        </div>
                        <div class="panel-body right-panel">
                            <div resource-relationships-list-dir></div>
                        </div>
                    </div>
                    <!-- /relationships -->
                    <!-- funding -->
                    <div class="panel panel-success" ng-controller="resourceFunding as rf">
                        <div class="panel-heading clearfix">
                            <div class="container">
                                <h3 class="panel-title">Funding</h3>
                                <div class="row">
                                    This resource is funded by<br/>
                                    <input type="text" ng-model="rf.new_funding_name" placeholder="funder" /><br/>
                                    <input type="text" ng-model="rf.new_funding_id" placeholder="funding ID (optional)" /><br/>
                                    <button class="btn btn-success" ng-click="rf.addFunding()">Add</button>
                                </div>
                            </div>
                        </div>
                        <div class="panel-body">
                            <ul>
                                <li ng-repeat="funding in rf.funding">
                                    <strong>{{ funding.funder }}</strong>
                                    <span ng-show="funding.id">
                                        {{ funding.id }}
                                    </span>
                                    <i class="fa fa-times" style="color:red; cursor:pointer" ng-click="rf.deleteFunding(funding)"></i>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <!-- /funding -->
                    <!-- image -->
                    <div class="panel panel-success">
                        <div class="panel-heading clearfix"><h3 class="panel-title">Image</h3>
                            <div class="row">
                                <form method="post" id="change-image" action="{{'/forms/resource-forms/resource-image.php?rid=' + rid}}" enctype="multipart/form-data">
                                    <div class="col-md-offset-1 col-md-5">
                                        Change the image <input type="file" name="resource-image" class="file-form" />
                                    </div>
                                    <div class="col-md-6">
                                        <button class="resource-image-submit" type="submit" class="btn btn-success">Submit</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div class="panel-body right-panel">
                            <div class="row">
                                <div class="col-md-offset-3 col-md-6">
                                    <img ng-src="{{resource_image}}" class="img-responsive" />
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- /image -->
                    <!-- versions -->
                    <div class="panel panel-success" ng-controller="resourceVersions as rv">
                        <div class="panel-heading"><h3 class="panel-title">Version History</h3></div>
                        <div class="panel-body right-panel">
                            <table class="table">
                                <thead><tr><th>Version</th><th>Status</th><th>Editor</th><th>Date</th><th>Difference</th></tr></thead>
                                <tr ng-repeat="ver in versions.versions" style="cursor:pointer" ng-class="{'active': ver.selected}" ng-click="loadVersion(ver)">
                                    <td>{{ver.version}}</td>
                                    <td>{{ver.status}} <span ng-show="ver.curated_by !== undefined">by {{ver.curated_by}}</span></td>
                                    <td>{{ver.username}}</td>
                                    <td>{{ver.time * 1000| date:'yyyy-MM-dd HH:mm'}}</td>
                                    <td>
                                        <button ng-hide="ver.selected" uib-popover-template="dynamicPopover.templateUrl" ng-click="rv.loadDiff($event, ver); $event.stopPropagation()">Show</button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <!-- /versions -->
                    <!-- owners -->
                    <div class="panel panel-success" ng-controller="resourceOwners as ro">
                        <div class="panel-heading clearfix"><h3 class="panel-title">Owners</h3>
                            <?php if($_SESSION["user"]->role > 0): ?>
                            <p>
                                Add an owner:
                                <input
                                        type="text"
                                        ng-model="new_owner"
                                        placeholder="Owner name or email"
                                        uib-typeahead="result.name for result in owners.searchUsers($viewValue)"
                                        typeahead-min-length="2"
                                        typeahead-on-select="selectOwner($item, $model, $label)"
                                        typeahead-wait-ms="250"
                                        typeahead-select-on-blue="true"
                                />
                                <button class="btn btn-success" ng-click="addOwner()">Add</button>
                            </p>
                          <?php endif ?>
                        </div>
                        <div class="panel-body">
                            <div class="container">
                                <ul class="list-group">
                                    <li class="list-group-item" ng-repeat="owner in owners.owners">
                                        {{ owner.name }}
                                        <?php if($_SESSION["user"]->role > 0): ?>
                                            <span class="input-button delete-button glyphicon glyphicon-remove" ng-click="deleteOwner(owner.uid)"></span>
                                        <?php endif ?>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <!-- /owners -->
                </div>

            </div>

            <!-- resourceMentions -->
            <div id="resource-mentions" class="sky-form tab-pane" ng-controller="resourceMentions as rm">
                <div resource-mentions-dir></div>
            </div>
            <!-- /resourceMentions -->
        </div>

    </div>
</div>
