(function() {
    var conversation_app = angular.module("curatorConversationApp", []);

    conversation_app.factory("curatorConversation", ["$http", function($http) {
        var cc = {};

        cc.createConversation = function(type, id, callback) {
            var promise = $http.post("/api/1/usermessages/conversation", {curator: true, reference_type: type, reference_id: id});
            if(callback != undefined) promise.then(callback);
            return promise;
        };

        cc.checkConversationExists = function(type, id, callback) {
            var promise = $http.get("/api/1/usermessages/conversationcheck?reference_type=" + type + "&reference_id=" + id + "&checkself");
            if(callback != undefined) promise.then(callback);
            return promise;
        };

        return cc;
    }]);
}());
