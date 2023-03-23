(function(){
    var error_app = angular.module("errorApp", ["ngSanitize"]);

    error_app.factory("errorModalCaller", ["$uibModal", function($uibModal){
        var emc = {};
        emc.call = function(message, cb1, cb2){
            if(cb1 === undefined) cb1 = function() {};
            if(cb2 === undefined) cb2 = function() {};
            var modalInstance = $uibModal.open({
                animation: true,
                templateUrl: "/templates/error-modal.html",
                controller: "errorModal",
                resolve: {
                    error_msg: function(){ return message; }
                }
            });
            modalInstance.result.then(cb1, cb2);
        };
        return emc;
    }]);

    error_app.controller("errorModal", ["$scope", "$uibModalInstance", "error_msg", function($scope, $uibModalInstance, error_msg){
        $scope.error_msg = error_msg;
        $scope.ok = function(){
            $uibModalInstance.close();
        };
    }]);

}());
