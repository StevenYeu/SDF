<?php

$page = isset($_GET["page"]) ? $_GET["page"] : 1;
$per_page = 100;
$offset = 0; 
$status = "pending";

$is_community_moderator = isset($_SESSION["user"]) && $_SESSION["user"]->levels[$community->id] >= 2;

?>
<div class="tab-pane fade <?php if($section=='datasets') echo 'in active' ?>" id="datasets">
    <div class="row margin-bottom-20">
        <div class="container">
            <div class="panel" style="padding:20px">
                <?php echo \helper\htmlElement("community-datasets", Array("per_page"=>$per_page, "page"=>$page, "is_community_moderator"=>$is_community_moderator, "offset"=>$offset, "status"=>$status, "community"=>$community, "show_pagination" => false)) ?>
            </div>
        </div>
    </div>
    <div class="row margin-bottom-20">
        <div class="container" id="community-dataset-required-fields" ng-controller="requiredFieldsController as rfc">
            <div class="panel" style="padding:20px">
                <h4>Dataset template required fields:</h4>
                <div>
                    <button ng-click="rfc.addNewFieldModal()" class="btn btn-success">Add new field</button>
                </div>
                <div>
                    <div ng-repeat="rf in rfc.required_fields.required_fields">
                        <hr/>
                        <h4>{{ rf.name }}:</h4>
                        <table class="table">
                            <thead>
                                <th>Name</th>
                                <th>Subject</th>
                                <th>Delete</th>
                            </thead>
                            <tbody>
                                <tr ng-repeat="field in rf.fields">
                                    <td>{{ field.name }}</td>
                                    <td>
                                        <a href="javascript:void(0)" ng-click="rfc.makeSubject(field)">
                                            <span ng-show="field.subject"><i class="fa fa-check-square-o text-success"></i></span>
                                            <span ng-hide="field.subject"><i class="fa fa-square-o text-muted"></i></span>
                                        </a>
                                    </td>
                                    <td>
                                        <a href="javascript:void(0)" ng-click="rfc.deleteFieldConfirm(field)">
                                            <i class="fa fa-times text-danger"></i>
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <script id="add-field.html" type="text/ng-template">
                <div class="modal-header">
                    <div class="modal-title"><h4>Add required field</h4></div>
                </div>
                <div class="modal-body">
                    <form ng-submit="submit()">
                        <div class="form-group">
                            <label>Template name</label>
                            <input type="text" ng-model="new_template_name" required class="form-control" />

                            <label>Field name</label>
                            <input type="text" ng-model="new_field_name" required class="form-control" />

                            <label>Template term</label>
                            <input type="text" class="form-control" placeholder="Filter existing CDEs"
                                ng-model="newFieldTypeFilter" 
                                ng-model-options="{debounce : 700}"
                                ng-change="filterFieldTypes(newFieldTypeFilter)"/>
                            <select class="form-control" ng-model="new_field_ilx" size="10" required>
                                <option ng-repeat="(ilx, term) in terms" value="{{ term.ilx }}">{{ term.label }}</option>
                            </select>

                            <input type="submit" class="btn btn-success" value="Submit" />
                        </div>
                    </form>
                </div>
            </script>
            <script id="delete-field-confirm.html" type="text/ng-template">
                <div class="modal-header">
                    <div class="modal-title"><h4>Confirm delete</h4></div>
                    <div class="modal-body">
                        <p>Do you want to delete this field?</p>
                        <button ng-click="confirm()" class="btn btn-danger">Delete</button>
                        <button ng-click="cancel()" class="btn btn-primary">Cancel</button>
                    </div>
                </div>
            </script>
        </div>
    </div>
</div>
<script src="/js/module-community-dataset-required-fields.js"></script>
