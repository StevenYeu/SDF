$(function() {
    var app = angular.module("communityDatasetRequiredFieldsApp", ["ui.bootstrap"])
        .run(function($rootScope, $log) {
            $rootScope.cid = $("#community-id").val();
        });

    app.factory("requiredFields", ["$http", "$log", function($http, $log) {
        var required_fields = {};
        required_fields.required_fields = [];
        required_fields.refresh = function(cid) {
            return $http.get("/api/1/datasets/community-required-field?cid=" + cid)
                .then(function(response) {
                    var raw_fields = response.data.data;
                    var required_fields_obj = {};
                    required_fields.required_fields = [];
                    for(var i = 0; i < raw_fields.length; i++) {
                        if(required_fields_obj[raw_fields[i].dataset_type_name] === undefined) {
                            required_fields_obj[raw_fields[i].dataset_type_name] = [];
                        }
                        required_fields_obj[raw_fields[i].dataset_type_name].push(raw_fields[i]);
                    }
                    for(rf in required_fields_obj) {
                        if(!required_fields_obj.hasOwnProperty(rf)) continue;
                        var template = {
                            name: rf,
                            fields: required_fields_obj[rf]
                        };
                        template.fields.sort(function(a, b) {
                            if(a.position < b.position) return -1;
                            if(a.position > b.position) return 1;
                            return 0;
                        });
                        required_fields.required_fields.push(template);
                    }
                    required_fields.required_fields.sort(function(a,b) {
                        if(a.name < b.name) return -1;
                        if(a.name > b.name) return 1;
                        return 0;
                    });
                });
        };

        required_fields.addNewField = function(cid, template_name, field_name, field_ilx) {
            return $http.post("/api/1/datasets/community-required-field/" + template_name + "/" + field_name + "/add", {
                cid: cid,
                ilx: field_ilx
            })
                .then(function(response) {
                    required_fields.refresh(cid);
                });
        };

        required_fields.makeSubject = function(cid, template_name, field_name) {
            $http.post("/api/1/datasets/community-required-field/" + template_name + "/" + field_name + "/make-subject", {
                cid: cid
            })
                .then(function(response) {
                    required_fields.refresh(cid);
                });
        };

        required_fields.deleteField = function(cid, template_name, field_name) {
            $http.post("/api/1/datasets/community-required-field/" + template_name + "/" + field_name + "/remove", {
                cid: cid
            })
                .then(function(response) {
                    required_fields.refresh(cid);
                });
        };

        return required_fields;
    }]);

    app.controller("requiredFieldsController", ["$http", "$rootScope", "$uibModal", "$log", "requiredFields", function($http, $rootScope, $uibModal, $log, required_fields) {
        var that = this;
        this.required_fields = required_fields;

        this.addNewFieldModal = function() {
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: "add-field.html",
                controller: "addFieldModalController"
            });
            modalInstance.result.then(function(data) {
                required_fields.addNewField($rootScope.cid, data.template_name, data.field_name, data.field_ilx);
            });
        };

        this.makeSubject = function(field) {
            required_fields.makeSubject($rootScope.cid, field.dataset_type_name, field.name);
        };

        this.deleteFieldConfirm = function(field) {
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: "delete-field-confirm.html",
                controller: "deleteFieldConfirmController"
            });
            modalInstance.result.then(function() {
                required_fields.deleteField($rootScope.cid, field.dataset_type_name, field.name);
            });
        };

        this.required_fields.refresh($rootScope.cid);
    }]);

    app.controller("addFieldModalController", ["$scope", "$http", "$uibModalInstance", function($scope, $http, $uibModalInstance) {
        $scope.submit = function() {
            $uibModalInstance.close({field_name: $scope.new_field_name, template_name: $scope.new_template_name, field_ilx: $scope.new_field_ilx});
        };

        $scope.cancel = function() {
            $uibModalInstance.dismiss();
        };

        $scope.filterFieldTypes = function(filter) {
            if(!filter) filter = "";
            getCDEs(filter);
        };

        function getCDEs(query) {
            $http.get("/api/1/term/search_by_annotation?type=cde&count=100&term=" + query)
                .then(function(response) {
                    var raw_terms = response.data.data;
                    var terms = {};
                    for(var i = 0; i < raw_terms.length; i++) {
                        terms[raw_terms[i].ilx] = raw_terms[i];
                    }
                    $scope.terms = terms;
                });
        }

        getCDEs("");
    }]);

    app.controller("deleteFieldConfirmController", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance) {
        $scope.confirm = function() {
            $uibModalInstance.close();
        };
        $scope.cancel = function() {
            $uibModalInstance.dismiss();
        }
    }])

    if($("#community-dataset-required-fields").length) {
        angular.bootstrap(document.getElementById("community-dataset-required-fields"), ["communityDatasetRequiredFieldsApp"]);
    }
});
