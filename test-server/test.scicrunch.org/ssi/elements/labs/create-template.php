<?php

$lab = $data["lab"];
$community = $data["community"];

if(!$lab || !$community) {
    return;
}

?>

<div id="create-template-app" ng-controller="createTemplateController as ctrl">
    <div class="row margin-bottom-20">
        <div class="col-md-6">
            <h2>Create a template - <a target="_self" class="lab-link" href="<?php echo $community->fullURL() ?>/lab?labid=<?php echo $lab->id ?>"><?php echo $lab->name ?></a></h2>
            <h4 ng-show="ctrl.mode == 'auto'">Create a template from an existing dataset spreadsheet</h4>
            <h4 ng-show="ctrl.mode == 'manual'">Create a template from scratch or choose a template to modify</h4>
        </div>
        <div class="col-md-6">
            <?php echo \helper\htmlElement("labs/header-buttons", Array("lab" => $lab, "community" => $community)) ?>
        </div>
    </div>
    <div>
        <div class="col-md-9">
            <div ng-show="ctrl.mode == 'choose-type'">
                <div class="margin-bottom-20">
                    <h3>Instructions</h3>
                    <h4>
                        All datasets within the <?php echo $community->portalName ?> portal are based on templates that define the data fields (i.e. a codebook) and how they align to commonly used data elements.
                        This allows the portal to provide feedback when uploading data and also allows for the integration of data when data is published.
                    </h4>
                </div>
                <div class="col-md-3">
                    <div class="text-center">
                        <a href="javascript:void(0)">
                            <div class="lab-button lab-big-button" style="cursor:pointer" ng-click="ctrl.changeMode('auto')">
                                <span>Create template from existing data</span>
                            </div>
                        </a>
                        <h4 class="margin-top-20">
                            Use an existing dataset to automatically create a dataset template.
                            Individual fields can then be aligned to common data elements.
                        </h4>
                    </div>
                </div>
                <div class="col-md-1"></div>
                <div class="col-md-3">
                    <div class="text-center">
                        <a href="javascript:void(0)">
                            <div class="lab-button lab-big-button" style="cursor:pointer" ng-click="ctrl.changeMode('manual')">
                                <span>Manually create template</span>
                            </div>
                        </a>
                        <h4 class="margin-top-20">
                            Manually create a template by adding individual fields and aligning these fields to common data elements.
                        </h4>
                    </div>
                </div>
            </div>
            <div ng-show="ctrl.mode == 'auto'">
                <autogen-template-component
                    type="template"
                    labid="<?php echo $lab->id ?>"
                    portal-name="<?php echo $community->portalName ?>"
                />
            </div>
            <div ng-show="ctrl.mode == 'manual'">
                <div>
                    <div class="col-md-6">
                        <h4>Enter template name</h4>
                        <form ng-submit="ctrl.createTemplate()">
                            <div class="form-group">
                                <label>Template name</label>
                                <input ng-model="ctrl.new_template.name" class="form-control" type="text" />
                            </div>
                            <input type="submit" ng-show="ctrl.submitable()" class="btn btn-success" value="Submit" />
                        </form>
                        <h4>
                            (Optional) Select an existing template as a starting point
                        </h4>
                        <h3 ng-show="ctrl.selected_template">
                            Selected template: <a class="lab-link" href="javascript:void(0)" ng-click="ctrl.selectTemplate(ctrl.selected_template)">{{ ctrl.selected_template.name }}</a>
                        </h3>
                        <table class="table">
                            <tbody>
                                <tr ng-repeat="template in ctrl.templates" ng-if="template != ctrl.selected_template">
                                    <td>
                                        <a class="lab-link" href="javascript:void(0)" ng-click="ctrl.selectTemplate(template)">
                                            {{ template.name }}
                                        </a>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <div ng-show="ctrl.selected_template" class="well">
                            <h4>Preview template</h4>
                            <a ng-href="/php/datasetfields-csv-template.php?templateid={{ctrl.selected_template.id}}">
                                <button class="btn btn-primary">Download template</button>
                            </a>
                            <table class="table">
                                <tr ng-repeat="field in ctrl.selected_template.fields">
                                    <td>{{ field.name }}</td>
                                    <td>
                                        <a class="lab-link" ng-href="<?php echo $community->fullURL() ?>/interlex/view/{{field.termid.ilx}}">
                                            {{ field.termid.label }} ({{ field.termid.ilx }})
                                        </a>
                                    </td>
                                </tr>
                            </table>
                            <div class="element-overlay" ng-show="ctrl.loading_single_template">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <dataset-workflow lab="ctrl.lab" workflow="ctrl.workflows.choose_type" ng-show="ctrl.mode == 'choose-type'"></dataset-workflow>
            <dataset-workflow lab="ctrl.lab" workflow="ctrl.workflows.autogen" ng-show="ctrl.mode == 'auto'"></dataset-workflow>
            <dataset-workflow lab="ctrl.lab" workflow="ctrl.workflows.manual" ng-show="ctrl.mode == 'manual'"></dataset-workflow>
        </div>
    </div>
</div>
