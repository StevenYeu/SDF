<?php

$system_messages = SystemMessage::getNonExpiredMessages();

?>

<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>

<div class="tab-pane fade in active" ng-app="smApp" ng-controller="systemMessages as sm">
    <h4>New message</h4>

    <input ng-model="sm.new_message" />

    <select ng-model="sm.new_type">
        <option value="info">Info</option>
        <option value="success">Success</option>
        <option value="warning">Warning</option>
        <option value="danger">Danger</option>
    </select>

    <h5>Start Time</h5>
    <div uib-datepicker ng-model="sm.new_start_date"></div>
    <div uib-timepicker ng-model="sm.new_start_time"></div>
    <h5>End Time</h5>
    <div uib-datepicker ng-model="sm.new_end_date"></div>
    <div uib-timepicker ng-model="sm.new_end_time"></div>
    <button class="btn btn-success" ng-click="sm.addMessage()">Add new Message</button>

    <hr/>

    <div class="table-search-v2 margin-bottom-20">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Message</th>
                        <th>Community</th>
                        <th>Type</th>
                        <th>Start Time</th>
                        <th>End Time</th>
                        <th><!-- Delete button --></th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="message in sm.messages.messages">
                        <th>{{ message.message }}</th>
                        <th>{{ message.comm_portal_name }}</th>
                        <th>{{ message.type }}</th>
                        <th>{{ message.start_time * 1000 | date:'yyyy-MM-dd HH:mm Z' }}</th>
                        <th>{{ message.end_time * 1000 | date:'yyyy-MM-dd HH:mm Z' }}</th>
                        <th><button class="btn btn-danger" ng-click="sm.deleteMessage(message.id)">Delete</button></th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
(function() {
    var sm_app = angular.module("smApp", ["ui.bootstrap"]);

    sm_app.factory("messages", ["$http", "$log", function($http, $log) {
        messages = {};

        messages.loadMessages = function() {
            $http.get("/api/1/systemmessages").then(function(response) {
                messages.messages = response.data.data;
            });
        };

        messages.addMessage = function(message, start_time, end_time, type) {
            $http.post("/api/1/systemmessages/add", {message: message, start_time: start_time, end_time: end_time, cid: -1, type: type}).then(function(response) {
                messages.loadMessages();
            });
        };

        messages.deleteMessage = function(id) {
            $http.post("/api/1/systemmessages/delete", {id:id}).then(function(response) {
                messages.loadMessages();
            });
        };

        messages.loadMessages();
        return messages;
    }]);

    sm_app.controller("systemMessages", ["$scope", "$log", "messages", function($scope, $log, messages) {
        var that = this;
        this.messages = messages;

        var now_time = new Date();
        var now_date = new Date();
        now_date.setHours(0);
        now_date.setMinutes(0);
        now_date.setSeconds(0);
        now_date.setMilliseconds(0);
        this.new_message = "";
        this.new_start_date = now_date;
        this.new_start_time = now_time;
        this.new_end_date = now_date;
        this.new_end_time = now_time;

        this.addMessage = function() {
            var start_time = (that.new_start_date.getTime() / 1000) + (that.new_start_time.getHours() * 3600) + (that.new_start_time.getMinutes() * 60);
            var end_time = (that.new_end_date.getTime() / 1000) + (that.new_end_time.getHours() * 3600) + (that.new_end_time.getMinutes() * 60);
            that.messages.addMessage(that.new_message, start_time, end_time, that.new_type);
        };

        this.deleteMessage = function(id) {
            that.messages.deleteMessage(id);
        };
    }]);
}());
</script>
