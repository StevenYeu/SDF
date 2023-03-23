(function(){

var directives_app = angular.module("resourceDirectives", ["ui.bootstrap"]);

directives_app.directive("resourceMentionsDir", function(){
    return {
        restrict: 'A',
        templateUrl: '/templates/resource-mentions.html'
    };
});

directives_app.directive("resourceRelationshipsAddDir", function(){
    return {
        restrict: "A",
        templateUrl: "/templates/resource-relationships-add.html"
    };
});

directives_app.directive("resourceRelationshipsListDir", function(){
    return {
        restrict: "A",
        templateUrl: "/templates/resource-relationships-list.html"
    };
});

directives_app.directive("resourceRelationshipsFilterDir", function(){
    return {
        restrict: "A",
        templateUrl: "/templates/resource-relationships-filter.html"
    };
});

directives_app.directive("resourceMentionUserSubscriptionDir", function(){
    return {
        restrict: "A",
        templateUrl: "/templates/resource-mention-user-subscription.html"
    };
});

directives_app.directive("singleResourceMentionDir", function(){
    return {
        restrict: "A",
        templateUrl: "/templates/single-resource-mention.html"
    };
});

directives_app.directive("claimResourceOwnershipDir", function() {
    return {
        restrict: "A",
        templateUrl: "/templates/claim-resource-ownership.html"
    };
});

directives_app.directive("customOnChange", function() {
    return {
        restrict: "A",
        link: function(scope, element, attrs) {
            var onChangeHandler = scope.$eval(attrs.customOnChange);
            element.bind("change", onChangeHandler);
        }
    };
});

}());
