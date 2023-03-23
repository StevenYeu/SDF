(function(){
    var curator_app = angular.module('curatorApp', ["resourceApp", "curatorConversationApp", "resourceDirectives", "ui.bootstrap", "multipleSelect"])
        .config(function($locationProvider){
            $locationProvider.html5Mode({enabled: true, requireBase: false});
        })
        .run(function($rootScope, $location, $http, errorModalCaller){
            var path = $location.path();
            var path_split = path.split("/");
            if(path_split[3] == "resourcesedit") {
                $rootScope.rid = path_split[4];
            } else {
                $rootScope.rid = path_split[3];
            }
            $rootScope.is_duplicate = false;
            $rootScope.page_type = "edit";
            $rootScope.logInCheck();
        });


    curator_app.controller("resourceFields", ["$rootScope", "$scope", "$http", "$uibModal", "$log", "errorModalCaller", "fields", "versions", function($rootScope, $scope, $http, $uibModal, $log, emc, fields, versions){
        var that = this;
        $scope.types = [{typeName: 'Resource', typeID: 1}, {typeName: 'Organization', typeID: 12}, {typeName: 'Software and Data Product', typeID: 45}];  // SDP added by Manu
        $scope.changingType = false;
        $scope.fields = fields;
        $scope.versions = versions;
        $scope.additional_resource_types = [];
        this.show_versions = false;

        this.add = function(field){
            field.visible = true;
            field.add_time = ++fields.add_time;
            if(field.type == "resource-types") {
                field.value = [];
            }
            $scope.field = "";
            that.change();
        };

        this.remove = function(field){
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: "/templates/remove-field-modal.html",
                controller: "removeFieldConfirm"
            });
            modalInstance.result.then(function(){
                field.visible = false;
                field.value = "";
                that.change();
            });
        };

        this.save = function(){
            var update_field_args = {};
            var valid = true;
            for(var i = 0; i < fields.fields.length; i++){
                valid = valid && fields.validate(fields.fields[i]);
                if(!!fields.fields[i].value){
                    update_field_args[fields.fields[i].field] = fields.fields[i].value;
                }
            }
            if(valid) {
                var post_var = {
                    "fields": update_field_args
                };
                $http.post("/api/1/resource/fields/edit/" + $rootScope.rid, post_var).
                    then(function(response){
                        fields.refresh();
                    }, function(response){
                        var jresponse = response.data;
                        if(jresponse.errormsg == "missing required field") {
                            emc.call("Please make sure all required fields have values");
                        } else if(jresponse.errormsg == "improperly formatted defining citation") {
                            emc.call("Defining citation was not formatted correctly, please use a comma separted list of PMIDs and DOIs(eg. PMID:22434839, DOI:10.1126/science.1157784)");
                        } else {
                            emc.call(jresponse.errormsg);
                        }
                    });
            }
        };
        $scope.save = this.save;

        this.loadVersion = function(n){
            fields.refresh(n);
        };

        this.curate = function(stat){
            fields.curate(stat);
        };

        this.change = function(field){
            if(field !== undefined) fields.validate(field);
            fields.changed = true;
        };

        $scope.changeResourceType = function () {
            $scope.changingType = true;
            $http.post("/api/1/resource/type/" + $rootScope.rid, {typeID: fields.typeID})
                .then(function (res) {
                     fields.refresh();
                    $scope.changingType = false;
                })
                .catch(function (err) {
                    console.log(err);
                });
        };

    }]);

    curator_app.controller("resourceVersions", ["$scope", "$rootScope", "$http", "versions", "fields", "$log", function($scope, $rootScope, $http, versions, fields, $log){
        $scope.versions = versions;
        $scope.loadVersion = function(version){
            fields.refresh(version.version);
        };
        $scope.dynamicPopover = {
            templateUrl: "/templates/versions_diff.html"
        };

        this.loadDiff = function($event, ver){
            var other_version = ver.version;
            var this_version = 0;
            for(var i = 0; i < versions.versions.length; i++){
                if(versions.versions[i].selected){
                    this_version = versions.versions[i].version;
                    break;
                }
            }
            $http.get("/api/1/resource/versions/diff/" + $rootScope.rid + "?1=" + this_version + "&2=" + other_version).then(function(response){
                var diffs = response.data.data;
                var reshaped_diffs = [];
                reshaped_diffs = reshaped_diffs.concat(reshapeDiffs(diffs.in_version1, "added"));
                reshaped_diffs = reshaped_diffs.concat(reshapeDiffs(diffs.in_version2, "removed"));
                reshaped_diffs = reshaped_diffs.concat(reshapeDiffs(diffs.modified, "modified"));
                ver.diffs = reshaped_diffs;
            });

        };

        function reshapeDiffs(diffs, type){
            var reshaped_diffs = [];
            for(var i = 0; i < diffs.length; i++) reshaped_diffs.push({column: diffs[i], type: type});
            return reshaped_diffs;
        }

        this.diffClassColor = function(type){
            switch(type){
                case "added": return "diff-added";
                case "removed": return "diff-removed";
                case "modified": return "diff-modified";
                default: return "";
            }
        };
        this.diffClassFA = function(type){
            switch(type){
                case "added": return "fa-plus-circle";
                case "removed": return "fa-minus-circle";
                case "modified": return "fa-edit";
                default: return "";
            }
        };
    }]);

    curator_app.controller("resourceOwners", ["$scope", "owners", function($scope, owners){
        $scope.selected_id = null;
        $scope.owners = owners;

        $scope.selectOwner = function(item, model, label){
            $scope.selected_id = item.id;
        };

        $scope.addOwner = function(){
            owners.addDeleteOwner($scope.selected_id, "add");
            $scope.new_owner = "";
            $scope.selected_id = null;
        };

        $scope.deleteOwner = function(id){
            owners.addDeleteOwner(id, "del");
        };

        $scope.cancel = function(){
            $scope.selected_id = null;
        };
    }]);

    curator_app.controller("removeFieldConfirm", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance){
        $scope.yes = function(){
            $uibModalInstance.close('yes');
        };
        $scope.no = function(){
            $uibModalInstance.dismiss('no');
        };
    }]);

    curator_app.controller("resourceConversation", ["curatorConversation", "fields", "$log", function(cc, fields, $log) {
        var that = this;
        this.conversation = undefined;

        var resource_id = 0;

        fields.initial_promise.then(function(response) {
            resource_id = parseInt(fields.scicrunch_id.substring(4));
            that.checkSingleConversation();
        });

        this.createConversation = function() {
            cc.createConversation("resources", resource_id, function(response) {
                var conversation = response.data.data;
                if(conversation != null) that.conversation = conversation;
            });
        };

        this.checkSingleConversation = function() {
            cc.checkConversationExists("resources", resource_id, function(response) {
                conversation = response.data.data;
                if(conversation.id != undefined) that.conversation = conversation;
            });
        };
    }]);

    curator_app.controller("resourceFunding", ["$rootScope", "$http", "$log", function($rootScope, $http, $log) {
        var that = this;
        this.new_funding_name = "";
        this.new_funding_id = "";
        this.funding = [];
        this.error = "";

        this.addFunding = function() {
            that.error = "";
            if(!that.new_funding_name) {
                that.error = "Missing funder name";
                return;
            }
            if(that.new_funding_name.indexOf("|||") !== -1) {
                that.error = "Invalid funder name";
                return;
            }
            if(that.new_funding_id.indexOf("|||") !== -1) {
                that.error = "Invalid funding id";
                return;
            }
            var resource_id = $rootScope.rid;
            var full_funder = that.new_funding_name + "|||" + that.new_funding_id;

            $http.post("/api/1/resource/rel/add/" + resource_id, {id1: resource_id, id2: full_funder, relationship: "is funded by", type: "funding"})
                .then(function(response) {
                    that.new_funding_name = "";
                    that.new_funding_id = "";
                    refreshFunding();
                });
        };

        this.deleteFunding = function(funding) {
            var id_str = funding.funder + "|||" + funding.id;
            var resource_id = $rootScope.rid;

            $http.post("/api/1/resource/rel/del/" + resource_id, {id1: resource_id, id2: id_str, relationship: "is funded by", type: "funding"})
                .then(function(response) {
                    refreshFunding();
                });
        };

        function refreshFunding() {
            $http.get("/api/1/resource/rel/view/" + $rootScope.rid + "/bytype?type=is funded by")
                .then(function(response) {
                    that.funding = [];
                    for(var i = 0; i < response.data.data.length; i++) {
                        var split = response.data.data[i].id1.split("|||");
                        that.funding.push({funder: split[0], id: split[1]});
                    }
                });
        }

        refreshFunding();
    }]);

}());
