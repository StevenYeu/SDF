(function(){
    var view_resource_app = angular.module("viewResourceApp", ["resourceApp", "resolverApp", "resourceDirectives", "ui.bootstrap"])
        .config(function($locationProvider){
            $locationProvider.html5Mode({enabled: true, requireBase: false});
        })
        .run(function($rootScope, $http, $location){
            $rootScope.rid = $("#resource-id").val();
            $rootScope.community_portal_name = $("#community-portal-name").val();
            $rootScope.page_type = "datafed";
            $rootScope.logInCheck();
            var get_params = $location.search();
            if(get_params["mentionssort"] === "added_date") $rootScope.mentions_sort = get_params["mentionssort"];
        });

    view_resource_app.controller("resourceFields", ["$scope", "$log", "fields", function($scope, $log, fields){
        $scope.fields = fields;
    }]);

    view_resource_app.controller("mentionsModalCaller", ["$rootScope", "$scope", "$q", "$http", "$uibModal", "mentions", "$log", function($rootScope, $scope, $q, $http, $uibModal, mentions, $log) {
        var subscription_id = $("#subscription-id").val();
        var subscription_type = $("#subscription-type").val();
        if(subscription_id && subscription_type) {
            $http.get("/api/1/subscription/newdata/" + subscription_id + "/" + subscription_type).
                then(function(response) {
                    var mention_ids = response.data.data;
                    var promises = [];
                    if(mention_ids.length > 0){
                        for(var i = 0; i < mention_ids.length; i++){
                            var mentionid = mention_ids[i];
                            var promise = $http.get("/api/1/resource/mention/view/" + $rootScope.rid + "/" + mentionid);
                            promises.push(promise);
                        }
                        $q.all(promises).then(function(responses) {
                            var new_mentions = [];
                            for(var i = 0; i < responses.length; i++){
                                var mention = responses[i].data.data;
                                mention.snippet_html = mentions.highlightSnippet(mention.snippet);
                                new_mentions.push(mention);
                            }
                            var modalInstance = $uibModal.open({
                                animation: true,
                                templateUrl: "/templates/mentions-subscription-new.html",
                                controller: "newMentionsModal",
                                resolve: {
                                    new_mentions: function(){ return new_mentions; },
                                }
                            });
                        });
                    }
                });
        }
    }]);

    view_resource_app.controller("newMentionsModal", ["$scope", "$uibModalInstance", "new_mentions", "mentions", "$log", function($scope, $uibModalInstance, new_mentions, mentions, $log) {
        $scope.hide_other_resources_mentions = true;
        $scope.new_mentions = new_mentions;
        $scope.mentions = mentions;
        $scope.ok = function() {
            $uibModalInstance.close();
        };
    }]);
}());
