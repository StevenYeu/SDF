<?php

$lab = $data["lab"];
$community = $data["community"];

if(!$lab || !$community) {
    return;
}

?>

<div id="single-template-app" ng-controller="singleTemplateController as ctrl">
    <div ng-show="ctrl.initial_load">
        <h2>Loading...</h2>
    </div>
    <div ng-hide="ctrl.initial_load">
        <div class="row margin-bottom-20" ng-show="ctrl.template">
            <div class="col-md-6">
                <h2>{{ ctrl.template.name }} - <a target="_self" class="lab-link" href="<?php echo $community->fullURL() ?>/lab?labid=<?php echo $lab->id ?>"><?php echo $lab->name ?></a></h2>

            </div>
            <div class="col-md-6">
                <?php echo \helper\htmlElement("labs/header-buttons", Array("lab" => $lab, "community" => $community)) ?>
            </div>
        </div>
        <div ng-show="ctrl.template">
            <div>
                <div class="row">
                    <div class="col-md-6">
                    </div>
                    <div class="col-md-6">
                        <div class="row">
                            <div class="pull-right">
                                <a href="javascript:void(0)" ng-click="ctrl.deleteTemplate()" ng-hide="ctrl.template.in_use">
                                    <div class="btn btn-danger btn-sm">Delete template</div>
                                </a>
                                <a ng-show="ctrl.template.submitted" target="_self" ng-href="<?php echo $community->fullURL() ?>/lab/create-dataset?labid=<?php echo $lab->id ?>&templateid={{ctrl.template.id}}">
                                    <div class="lab-button lab-small-button" style="cursor:pointer">
                                        <i class="fa fa-plus"></i> New dataset
                                    </div>
                                </a>
                                <a target="_self" ng-href="/php/datasetfields-csv-template.php?templateid={{ctrl.template.id}}">
                                    <div class="lab-button lab-small-button">
                                        <i class="fa fa-download"></i> Download
                                    </div>
                                </a>
                                <div ng-show="ctrl.template.submitted" class="lab-button lab-small-button" style="cursor:pointer" ng-click="ctrl.submitTemplate('unsubmit')">
                                    <i class="fa fa-times-circle-o"></i> Disable
                                </div>
                                <div ng-hide="ctrl.template.submitted" class="lab-button lab-small-button" style="cursor:pointer" ng-click="ctrl.submitTemplate('submit')">
                                    <i class="fa fa-arrow-circle-o-left"></i> Enable
                                </div>
                                <a href="javascript:void(0)" class="lab-link" ng-click="ctrl.toggleChangeName()">
                                    <div class="lab-button lab-small-button">
                                        <i class="fa fa-edit"></i> Change name
                                    </div>
                                </a>
                            </div>
                        </div>
                        <div class="row">
                            <div class="alert alert-danger" ng-show="!ctrl.template.submitted">
                                Before you can use this template, it will need to be enabled.
                            </div>
                            <div class="alert alert-info" ng-show="ctrl.template.submitted">
                                This template is enabled and datasets can be created from it.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6 col-md-offset-6 ">
                        <div class="pull-right">
                            <form ng-show="ctrl.change_name_mode" class="form" ng-submit="ctrl.updateName()">
                                <input type="text" ng-model="ctrl.edit.name" />
                                <input type="submit" class="btn btn-success" value="Update" />
                            </form>
                        </div>
                    </div>
                </div>
                <div class="row" ng-show="ctrl.mode == 'add-field'">
                    <div ng-hide="ctrl.template.in_use">
                        <h2>Add a field</h2>
                        <div class="col-md-6">
                            <div class="form-group row">
                                <label class="control-label col-sm-2">Field name</label>
                                <div class="col-sm-10">
                                    <form ng-submit="ctrl.addField()">
                                        <input type="text" class="form-control" ng-model="ctrl.new_field.name" />
                                        <span style="color:red">{{ ctrl.errors.field_name }}</span>
                                    </form>
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="control-label col-sm-2">Field type</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control" placeholder="Filter existing CDEs"
                                        ng-model="ctrl.new_field_type_filter"
                                        ng-model-options="{debounce : 700}"
                                        ng-change="ctrl.filterFieldTypes(ctrl.new_field_type_filter)"/>
                                    <select class="form-control" ng-model="ctrl.new_field_type_ilx" size="10" ng-change="ctrl.changeNewFieldType()">
                                        <option ng-repeat="(ilx, term) in ctrl.terms" value="{{ term.ilx }}">{{ term.label }}</option>
                                    </select>
                                    <div style="color:red" ng-show="!ctrl.new_field.termid">{{ ctrl.errors.field_type }}</div>
                                    Don't see your field type?
                                    <a class="lab-link" href="javascript:void(0)" ng-click="ctrl.addCDE()">Add a new CDE</a>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <dl class="dl-horizontal">
                                {{ valueRestrictions=ctrl.valueRestrictions(ctrl.new_field);"" }}
                                <dt>New field name</dt>
                                <dd>{{ ctrl.new_field.name }}</dd>
                                <dt>Type name</dt>
                                <dd>{{ ctrl.new_field.termid.label }}</dd>
                                <dt>Type description</dt>
                                <dd>{{ ctrl.new_field.termid.definition }}</dd>
                                <span ng-show="valueRestrictions.allowedValues">
                                    <dt>Value restrictions</dt>
                                    <dd>
                                        <value-restrictions-values values="valueRestrictions.allowedValues"></value-restrictions-range>
                                    </dd>
                                </span>
                                <span ng-show="valueRestrictions.allowedRange != null">
                                    <dt>Value range</dt>
                                    <dd>
                                        <value-restrictions-range range="valueRestrictions.allowedRange"></value-restrictions-range>
                                    </dd>
                                </span>
                                <dt>ILX</dt>
                                <dd><a tooltip="View the CDE" target="_blank" class="lab-link" ng-href="/scicrunch/interlex/view/{{ ctrl.new_field.termid.ilx }}">{{ ctrl.new_field.termid.ilx }}</a></dd>
                            </dl>
                            <div>
                                <div class="panel panel-primary">
                                    <div class="panel-heading" ng-show="ctrl.type_filters.domain == null">Domain filter</div>
                                    <div class="panel-heading" ng-show="ctrl.type_filters.domain != null && ctrl.type_filters.subdomain == null">Subdomain filter</div>
                                    <div class="panel-heading" ng-show="ctrl.type_filters.domain != null && ctrl.type_filters.subdomain != null">CDE filter</div>
                                    <div class="panel-body">
                                        <div ng-hide="ctrl.type_filters.domain == null" style="border-bottom:1px solid #ccc;margin-bottom:10px;padding-bottom:10px">
                                            <a class="lab-link" href="javascript:void(0)" ng-click="ctrl.clearFilterVals()">
                                                <button class="btn btn-danger btn-xs"><i class="fa fa-times"></i> remove filter</button>
                                            </a>
                                            <span ng-show="ctrl.type_filters.subdomain == null">{{ ctrl.type_filters.domain }}</span>
                                            <span ng-hide="ctrl.type_filters.subdomain == null">{{ ctrl.type_filters.subdomain }}</span>
                                        </div>
                                        <ul>
                                            <li ng-repeat="fv in ctrl.filter_vals">
                                                <a
                                                    class="lab-link"
                                                    ng-class="{selectedVal: ctrl.type_filters.assessmentdomain == fv.value && ctrl.type_filters.subdomain != null}"
                                                    href="javascript:void(0)"
                                                    ng-click="ctrl.toggleFilterVals(fv.value)"
                                                >
                                                    {{ fv.value }} ({{fv.count}})
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div>
                            <button ng-click="ctrl.addField()" class="btn btn-success">Add field</button>
                        </div>
                    </div>
                    <div ng-show="ctrl.template.in_use">
                        <h4>This template is used by the following datasets and cannot be edited or deleted</h4>
                        <ul>
                            <li ng-repeat="dataset in ctrl.template.dataset_names">
                                <a class="lab-link" target="_self" ng-href="<?php echo $community->fullURL() ?>/lab/dataset?labid=<?php echo $lab->id ?>&datasetid={{ dataset.id }}">{{ dataset.name }}</a>
                            </li>
                        </ul>
                    </div>
                    <hr/>
                    <h2>Fields</h2>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>CDE</th>
                                <th></th>
                                <th ng-hide="ctrl.template.in_use">Delete</th>
                                <th ng-hide="ctrl.template.in_use">Move</th>
                                <th ng-hide="ctrl.template.in_use">Subject field</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="field in ctrl.template.fields">
                                <td>{{ field.name }}</td>
                                <td>
                                    {{ field.termid.label }} (<a tooltip="View the CDE" target="_blank" class="lab-link" ng-href="/scicrunch/interlex/view/{{ field.termid.ilx }}">{{ field.termid.ilx }}</a>)
                                    <a class="lab-link" href="javascript:void(0)" ng-click="ctrl.changeFieldILX(field)">[change CDE]</a>
                                </td>
                                <td>
                                    {{ vr = ctrl.valueRestrictions(field);"" }}
                                    <span ng-show="vr.allowedValues">
                                        <strong>Value restrictions:</strong>
                                        <value-restrictions-values values="vr.allowedValues"></value-restrictions-range>
                                    </span>
                                    <span ng-show="vr.allowedRange">
                                        <strong>Value range:</strong> <value-restrictions-range range="vr.allowedRange"></value-restrictions-range>
                                    </span>
                                </td>
                                <td ng-hide="ctrl.template.in_use"><i class="fa fa-times" style="color:red; cursor:pointer" ng-click="ctrl.deleteField(field)"></i></td>
                                <td ng-hide="ctrl.template.in_use">
                                    <i ng-show="!$first" class="fa fa-arrow-up" style="cursor:pointer" ng-click="ctrl.moveField(field, 'up')"></i>
                                    <i ng-show="!$last" class="fa fa-arrow-down" style="cursor:pointer" ng-click="ctrl.moveField(field, 'down')"></i>
                                </td>
                                <td ng-hide="ctrl.template.in_use">
                                    <i ng-show="ctrl.isSubjectField(field)" ng-click="ctrl.toggleSubjectField(field)" style="color:green;cursor:pointer" class="fa fa-toggle-on"></i>
                                    <i ng-hide="ctrl.isSubjectField(field)" ng-click="ctrl.toggleSubjectField(field)" style="color:red;cursor:pointer" class="fa fa-toggle-off"></i>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="container" ng-hide="ctrl.template">
            <h4>
                Could not find dataset template
            </h4>
        </div>
    </div>

    <script type="text/ng-template" id="deleteFieldConfirm.html">
        <div class="modal-header">
            <h3 class="modal-title">Delete field?</h3>
        </div>
        <div class="modal-body">
            Are you sure you want to delete this field?
        </div>
        <div class="modal-footer">
            <button class="btn btn-danger" ng-click="delete()">Delete</button>
            <button class="btn btn-primary" ng-click="cancel()">Cancel</button>
        </div>
    </script>

    <script type="text/ng-template" id="addCDE.html">
        <div class="modal-header">
            <h3 class="modal-title">Add new CDE</h3>
        </div>

        <div class="modal-body">
            <div ng-hide="!!term">
                <form class="sky-form">
                    <fieldset>
                        <label class="label">Label <span class="text-danger">*</span></label>
                        <label class="input">
                            <input type="text" ng-model="label" required />
                        </label>
                        <label class="label">Definition</label>
                        <label class="input">
                            <input type="text" ng-model="definition" />
                        </label>
                        <label class="label">
                            Time series
                            <input type="checkbox" ng-model="timeseries" />
                        </label>
                        <label class="label" title="If at least one required value is added, then only values matching a required value will be allowed in this field.  Do not add any required values for free text.">
                            Required values <i class="fa fa-question"></i>
                        </label>
                        <label class="input">
                            <input ng-repeat="vr in valueRestrictions track by $index" type="text" ng-model="valueRestrictions[$index]" ng-change="changeValueRestrictions()" />
                        </label>
                        <label title="If these fields are set, then values must be a number that falls within the required range." class="label">
                            Required values range <i class="fa fa-question"></i>
                            <div style="color:red">
                                {{ errors.valueRange }}
                            </div>
                        </label>
                        <label class="label">
                            <div class="col-md-2">
                                Start
                            </div>
                            <div class="col-md-10">
                                <input type="number" ng-model="valueRange.start" ng-disabled="valueRestrictionsInUse"/>
                            </div>
                        </label>
                        <label class="label">
                            <div class="col-md-2">
                                End
                            </div>
                            <div class="col-md-10">
                                <input type="number" ng-model="valueRange.end" ng-disabled="valueRestrictionsInUse" />
                            </div>
                        </label>
                        <label class="label">
                            <div class="col-md-2">
                                Step
                            </div>
                            <div class="col-md-10">
                                <input type="number" ng-model="valueRange.step" ng-disabled="valueRestrictionsInUse" />
                            </div>
                        </label>
                        <hr/>
                        <label class="label" title="The default name of the field when creating a field of this CDE type">Default field name <i class="fa fa-question"></i></label>
                        <label class="input">
                            <input type="text" ng-model="defaultValue" />
                        </label>
                        <label class="label" title="The domain of this CDE.  This field is used for categorizing the CDE.">Domain <i class="fa fa-question"></i></label>
                        <label class="input">
                            <input type="text" ng-model="domain" />
                        </label>
                        <label class="label" title="The subdomain of this CDE.  This field is used for categorizing the CDE.">Subdomain <i class="fa fa-question"></i></label>
                        <label class="input">
                            <input type="text" ng-model="subdomain" />
                        </label>
                        <label class="label" title="The assessment domain of this CDE.  This field is used for categorizing the CDE.">Assessment domain <i class="fa fa-question"></i></label>
                        <label class="input">
                            <input type="text" ng-model="assessmentDomain" />
                        </label>
                    </fieldset>
                </form>
                <button class="btn btn-success" ng-click="addTerm()">Add term</button>
                <button class="btn btn-primary" ng-click="cancel()">Cancel</button>
            </div>

            <div ng-show="!!term">
                <h4>{{ term.label }}</h4>
                <p>{{ curiefyIlx(term.ilx) }}</p>
                <p>Definition: {{ term.definition }}</p>
                <a target="_blank" class="lab-link" ng-href="/{{portalName}}/interlex/view/{{term.ilx}}">Edit this term</a>
                <button class="btn btn-primary" ng-click="done()">Done</button>
            </div>
        </div>

        <div class="modal-footer">
        </div>
    </script>
</div>
