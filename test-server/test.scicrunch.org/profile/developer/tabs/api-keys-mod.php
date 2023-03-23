<div ng-app="apikeysmodApp" class="tab-pane fade" id="api-keys-mod" ng-controller="keyModController as kmc">
    <div>
        <input
            type="text"
            ng-model="kmc.next_selection"
            uib-typeahead="result.name + ' (' + result.email + ')' for result in kmc.searchUsers($viewValue)"
            typeahead-min-length="2"
            typeahead-wait-ms="250"
            typeahead-select-on-blue="true"
            typeahead-on-select="kmc.selectUser($item, $model, $label)"
        />
        <button class="btn btn-success" ng-click="kmc.getUser()">Lookup</button>
    </div>

    <div ng-hide="kmc.keys.keys === undefined">
        <div class="page-header">
            <h1>Keys for {{ kmc.current_name }} ({{ kmc.current_email }})</h1>
            <button class="btn btn-success" ng-click="kmc.newKeyConfirm()">Generate New API Key</button>
            <button class="btn btn-success" ng-click="kmc.saveText()" ng-show="kmc.keys.keys.length > 0">Save Text</button>
        </div>
        <div class="table-search-v2 margin-bottom-20">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Key</th>
                            <th>Project Name</th>
                            <th>Description</th>
                            <th>Permissions</th>
                            <th>Add Permission</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="key in kmc.keys.keys">
                            <td>
                                <i class="fa fa-toggle-on" style="color:green" ng-show="key.active === 1" ng-click="kmc.keyToggle(key, 'off')" tooltip="Deactivate"></i>
                                <i class="fa fa-toggle-off" style="color:red" ng-show="key.active === 0" ng-click="kmc.keyToggle(key, 'on')" tooltip="Activate"></i>
                                <code>{{key.key_val.substr(0,16)}}****************</code>
                            </td>
                            <td><input ng-model="key.project_name" /></td>
                            <td><input ng-model="key.description" /></td>
                            <td>
                                <div ng-repeat="perm in key.permissions">
                                    <i class="fa fa-toggle-on" style="color:green" ng-show="perm.active === 1" ng-click="kmc.permToggle(key, perm, 'off')" tooltip="Deactivate"></i>
                                    <i class="fa fa-toggle-off" style="color:red" ng-show="perm.active === 0" ng-click="kmc.permToggle(key, perm, 'on')" tooltip="Activate"></i>
                                    {{perm.permission_type}}
                                </div>
                            </td>
                            <td>
                                <i class="fa fa-plus-circle" style="color:green" uib-popover-template="kmc.dynamicPopover.templateUrl"></i>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
