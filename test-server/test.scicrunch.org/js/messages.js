(function() {
    var messages_app = angular.module("curatorApp", ["ui.bootstrap"])
        .config(function($locationProvider) {
            $locationProvider.html5Mode({enabled: true, requireBase: false});
        })
        .run(function($rootScope, $http, $location, $log) {
            $http.get("/api/1/user/info").then(function(response) {
                $rootScope.user = response.data.data;
            });

            $rootScope.convID = $location.search().convID;
        });

    messages_app.factory("conversations", ["$rootScope", "$http", "$q", "$log", function($rootScope, $http, $q, $log) {
        var conversations = {};

        conversations.loadAll = function() {
            return $http.get("/api/1/usermessages/conversation").then(function(response) {
                var return_data = response.data.data;
                conversations.conversations = [];
                for(var i = 0; i < return_data.length; i++) {
                    var conversation = return_data[i].conversation;
                    conversation.new_flag = return_data[i].new_flag;
                    conversations.conversations.push(conversation);

                    conversations.loadUsers(conversations.conversations[i]);
                    conversations.conversations[i].accordion_open = false;
                    conversations.conversations[i].open_yet = false;
                }
                conversations.conversations.sort(function(a,b) {
                    if(a.new_flag && !b.new_flag) return -1;
                    if(!a.new_flag && b.new_flag) return 1;
                    return 0;
                });
            });
        };

        conversations.reloadConversation = function(conversation) {
            conversations.loadMessages(conversation, 50);
            conversations.loadUsers(conversation);
        };

        conversations.loadMessages = function(conversation, count) {
            return $http.get("/api/1/usermessages/conversation/" + conversation.id + "/messages/new?offset=" + count)
                .then(function(response) {
                    conversation.messages = response.data.data;
                });
        };

        conversations.loadUsers = function(conversation) {
            $http.get("/api/1/usermessages/conversation/" + conversation.id + "/users").
                then(function(response) {
                    conversation.users = response.data.data;
                });
        };


        return conversations;
    }]);

    messages_app.factory("searchUsers", ["$http", function($http) {
        searchUsers = {};
        searchUsers.search = function(val) {
            return $http.get("/api/1/user/autocomplete?name=" + val).
                then(function(response) {
                    var results = response.data.data;
                    for(var i = 0; i < results.length; i++) {
                        var display = results[i].name + " (" + results[i].email + ")";
                        results[i].display = display;
                    }
                    return results;
                });
        }

        return searchUsers;
    }]);

    messages_app.controller("conversationsController", ["$rootScope", "$scope", "$http", "conversations", "searchUsers", "$log", function($rootScope, $scope, $http, conversations, searchUsers, $log) {
        var that = this;
        this.conversations = conversations;
        this.new_message = null;
        this.searchUsers = searchUsers;
        this.new_user = null;

        this.addMessage = function(conversation) {
            if(!that.new_message) return;
            var message = that.new_message;
            that.new_message = null;

            var message_count = conversation.messages.length;
            $http.post("/api/1/usermessages/conversation/" + conversation.id + "/message", {message: message}).
                then(function(response) {
                    that.conversations.loadMessages(conversation, message_count);
                });
        };

        this.removeUserFromConversation = function(conversation, user) {
            $http.post("/api/1/usermessages/conversation/" + conversation.id + "/user/remove", {uid: user.id}).
                then(function(response) {
                    conversations.reloadConversation(conversation);
                });
        };

        this.leaveConversation = function(conversation) {
            $http.post("/api/1/usermessages/conversation/" + conversation.id + "/user/leave").
                then(function(response) {
                    conversations.loadAll().then(function() {
                        that.watchConversationsAccordion();
                    });
                });
        };

        this.selectUser = function(item, model, label, conversation) {
            $http.post("/api/1/usermessages/conversation/" + conversation.id + "/user/add", {uid: item.id}).
                then(function(response) {
                    that.new_user = null;
                    conversations.reloadConversation(conversation);
                });
        };

        this.accordionOpen = function(conversation) {
            if(!conversation.open_yet) {
                conversation.open_yet = true;
                conversations.loadMessages(conversation, 50);
                conversation.new_flag = false;
            }
        };

        conversations.loadAll().then(function() {
            if($rootScope.convID != undefined) {
                for(var i = 0; i < conversations.conversations.length; i++) {
                    if(conversations.conversations[i].id == $rootScope.convID) {
                        conversations.conversations[i].accordion_open = true;
                        that.accordionOpen(conversations.conversations[i]);
                    }
                }
            }
        });
    }]);

    messages_app.controller("newConversationsController", ["$scope", "$http", "$log", "conversations", "searchUsers", function($scope, $http, $log, conversations, searchUsers) {
        var that = this;

        this.showNew = false;

        this.showNewToggle = function() {
            this.showNew = !this.showNew;
        };

        this.newConversationUIDs = [{}];
        this.newConversationTitle = null;
        this.newConversationText = null;

        this.searchUsers = searchUsers;

        this.selectUser = function(item, model, label) {
            model.name = item.display;
            model.uid = item.id;
            this.newConversationUIDs.push({});
        };

        this.createConversation = function() {
            if(!this.newConversationText || !this.newConversationTitle) return;
            var uids = [];
            for(var i = 0; i < this.newConversationUIDs.length; i++) {
                var newUID = this.newConversationUIDs[i];
                if(newUID.uid) uids.push(newUID.uid);
            }
            if(uids.length === 0) return;

            $http.post("/api/1/usermessages/conversation", {uids: uids, message: this.newConversationText, name: this.newConversationTitle}).
                then(function(response) {
                    that.newConversationText = null;
                    that.newConversationTitle = null;
                    that.newConversationUIDs = [{}];
                    conversations.loadAll();
                });
        };
    }]);
}());
