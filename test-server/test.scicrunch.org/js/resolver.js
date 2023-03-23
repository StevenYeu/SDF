(function(){
    var resolver_app = angular.module("resolverApp", ["ui.bootstrap", "errorApp"])
        .config(function($locationProvider){
            $locationProvider.html5Mode({enabled:true, requireBase:false});
        })
        .run(function($rootScope, $http, $log){
            $rootScope.rrid = $("#rrid").val();
            $http.get("/api/1/user/info?no-datasets=1").
                then(function(response){
                    var data = response.data.data;
                    if(!data.logged_in){
                        $rootScope.logged_in = false;
                    }else{
                        $rootScope.logged_in = true;
                        $rootScope.is_curator = data.role > 0;
                    }
                });
        });

    resolver_app.controller("altIDs", ["$rootScope", "$scope", "$http", "errorModalCaller", "$log", function($rootScope, $scope, $http, emc, $log){
        var that = this;
        this.refresh = function(){
            $http.get("/api/1/rrid/alternate/view/" + $rootScope.rrid)
                .then(function(response){
                    var data = response.data.data;
                    that.alt_ids = data;
                });
        };
        this.new_altid = "";
        this.addNewAltID = function(){
            if(that.new_altid != ""){
                $http.post("/api/1/rrid/alternate/add/" + $rootScope.rrid, {altid: that.new_altid})
                    .then(function(response){
                        that.new_altid = "";
                        that.refresh();
                    }, function(response){
                        emc.call("Could not create mapping, this alternate ID may already exists");
                    });
            }
        };
        this.setActive = function(setting, altid){
            if(setting === "true" || setting === "false"){
                $http.post("/api/1/rrid/alternate/active/" + $rootScope.rrid, {altid: altid, active: setting})
                    .then(function(response){
                        that.refresh();
                    }, function(response){
                        $log.log(response);
                    });
            }
        }
        this.dynamicPopover = {"templateUrl": "/templates/rrid_mappings.html"};
        this.refresh();
    }]);
}());
