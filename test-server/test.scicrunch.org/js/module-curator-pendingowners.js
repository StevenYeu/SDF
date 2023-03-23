(function(){
    var pending_owners_app = angular.module("pendingOwnersApp", ["errorApp", "ui.bootstrap", "curatorConversationApp"]);

    pending_owners_app.controller("pendingOwners", ["$scope", "$http", "curatorConversation", "$log", function($scope, $http, cc, $log) {
        this.pending_owners = [];
        var that = this;
        this.current_page = 1;
        this.per_page = 20;
        this.total_count = 0;

        this.page = function(p) {
            that.current_page = p;
            var offset = (that.current_page - 1) * that.per_page; 
            $http.get("/api/1/resource/pendingowner?count=" + that.per_page + "&offset=" + offset).
                then(function(response){
                    that.pending_owners = response.data.data;
                    for(var i = 0; i < that.pending_owners.length; i++) {
                        getResourceName(that.pending_owners[i].rid, i);
                        that.pending_owners[i].text_data = that.pending_owners[i].text_data.replace("&#39;", "'");
                        that.checkForConversations();
                    }
                });
            $http.get("/api/1/resource/pendingowner/count").
                then(function(response){
                    that.total_count = response.data.data;
                });
        };
        var getResourceName = function(rid, idx) {
            $http.get("/api/1/resource/fields/view/" + rid).
                then(function(response){
                    var fields = response.data.data.fields;
                    for(var i = 0; i < fields.length; i++){
                        if(fields[i].field === "Resource Name"){
                            that.pending_owners[idx].resource_name = fields[i].value;
                            break;
                        }
                    }
                });
        };
        this.review = function(pending_owner, review_val) {
            $http.post("/api/1/resource/pendingowner/" + pending_owner.rid + "/review", {uid: pending_owner.uid, status: review_val}).
                then(function(response){
                    that.page(that.current_page);
                });
        };
        this.createConversation = function(pending_owner) {
            cc.createConversation("resource-owners", pending_owner.id, function(){ that.checkForConversations(); });
        };
        this.checkForConversations = function() {
            for(var i = 0; i < that.pending_owners.length; i++) {
                checkSingleConversation(i);
            }
        };
        var checkSingleConversation = function(i) {
            var ref_id = that.pending_owners[i].id;
            cc.checkConversationExists("resource-owners", ref_id, function(response) {
                var result = response.data.data;
                if(result != null) {
                    that.pending_owners[i].conversation = result;
                }
            });
        };

        this.page(1);
    }]);
}());
