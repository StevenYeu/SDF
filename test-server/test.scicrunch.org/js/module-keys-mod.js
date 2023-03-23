(function(){
    var apikeysmod_app = angular.module("apikeysmodApp", ["errorApp", "ui.bootstrap"])
        .run(function($rootScope, $location, $http, $log){
            $rootScope.login_promise = $http.get("/api/1/user/info").
                then(function(response){
                    $rootScope.logged_in = response.data.data.logged_in;
                    if($rootScope.logged_in){
                        $rootScope.role = response.data.data.role;
                        $rootScope.uid = response.data.data.id;
                    }
                });
        });

    apikeysmod_app.factory("other_keys", ["$rootScope", "$http", "$log", function($rootScope, $http, $log){
        var keys = {};
        var that = this;
        keys.refresh = function(uid){
            if(uid === undefined) uid = that.last_uid;
            else that.last_uid = uid;
            keys.keys = undefined;
            $http.get("/api/1/key/lookup?uid=" + uid)
                .then(function(response){
                    keys.keys = response.data.data;
                });
        };
        keys.toggle = function(key, perm, action, type){
            var base_url = "/api/1/key";
            if(action === "on") var url_action = "/enable";
            else if(action === "off") var url_action = "/disable";
            if(type === "key") var url_type = "";
            else if(type === "perm") var url_type = "/permission";

            var args = {keyval: key.key_val};
            if(type === "perm") args.permission_type = perm.permission_type;

            var url = base_url + url_type + url_action;
            $http.post(url, args)
                .then(function(response){
                    keys.refresh();
                });
        };
        keys.addPermissionType = function(key, type){
            var args = {keyval: key.key_val, permission_type: type};
            $http.post("/api/1/key/permission/add", args)
                .then(function(response){
                    keys.refresh();
                }, function(response){
                    $log.log(response);
                });
        };
        keys.generateNewKey = function(uid){
            $http.post("/api/1/key/add", {uid: uid})
                .then(function(response){
                    keys.refresh();
                });
        };
        keys.saveText = function(){
            for(var i = 0; i < keys.keys.length; i++){
                var key = keys.keys[i];
                $http.post("/api/1/key/update", {keyval: key.key_val, description: key.description, project_name: key.project_name})
                    .then(function(response){
                        keys.refresh();
                    });
            }
        };
        $http.get("/api/1/key/permission/types")
            .then(function(response){
                keys.permission_types = response.data.data;
            });

        return keys;
    }]);

    apikeysmod_app.controller("keyModController", ["$http", "$log", "$uibModal", "other_keys", function($http, $log, $uibModal, keys){
        var that = this;
        this.keys = keys;
        this.user_selection = "";
        this.selected_id = undefined;

        this.searchUsers = function(val){
            return $http.get("/api/1/user/autocomplete?name=" + val)
                .then(function(response){
                    return response.data.data;
                });
        };
        this.selectUser = function(item, model, label){
            that.selected_id = item.id;
            that.selected_name = item.name;
            that.selected_email = item.email;
        };
        this.getUser = function(){
            if(that.selected_id === undefined) return;

            that.keys.refresh(that.selected_id);
            that.current_id = that.selected_id;
            that.current_name = that.selected_name;
            that.current_email = that.selected_email;

            that.selected_id = undefined;
            that.selected_name = undefined;
            that.user_selection = "";
        };
        this.toggle = function(key, perm, action){
            keys.toggle(key, perm, action, "key");
        };
        this.keyToggle = function(key, action){
            keys.toggle(key, undefined, action, "key");
        };
        this.permToggle = function(key, perm, action){
            keys.toggle(key, perm, action, "perm");
        };
        this.dynamicPopover = {
            templateUrl: "/templates/permission_types.html"
        };
        this.showPermissionType = function(key, type){
            for(var i = 0; i < key.permissions.length; i++){
                if(type === key.permissions[i].permission_type) return false;
            }
            return true;
        };
        this.addPermissionType = function(key, type){
            keys.addPermissionType(key, type);
        };
        this.newKeyConfirm = function(){
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: "/templates/add_new_key_modal.html",
                controller: "addNewKey"
            });
            modalInstance.result.then(function(){
                keys.generateNewKey(that.current_id);
            });
        };
        this.saveText = function(){
            keys.saveText();
        };
    }]);

    apikeysmod_app.controller("addNewKey", ["$scope", "$uibModalInstance", function($scope, $uibModalInstance){
        $scope.yes = function(){
            $uibModalInstance.close('yes');
        };
        $scope.no = function(){
            $uibModalInstance.dismiss('no');
        };
    }]);
}());
