<?php echo Connection::createBreadCrumbs('Messages',array('Home','Account'),array($profileBase,$profileBase.'account'),'Messages'); ?>

<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>
<script src="/js/module-error.js"></script>
<script src="/js/messages.js"></script>

<div class="profile container content">
    <div class="row">
        <?php include "left-column.php"; ?>

        <div class="col-md-9" ng-app="curatorApp">
            <div class="profile-body">

                <div ng-controller="newConversationsController as ncc" ng-show="user.role > 1">
                    <button class="btn btn-default" ng-click="ncc.showNewToggle()">Create new conversation</button>
                    <div style="margin-top:20px" ng-show="ncc.showNew">
                        <div class="">
                            <label>Chat Title</label>
                            <input class="form-control" ng-model="ncc.newConversationTitle" />
                        </div>
                        <div style="margin-top:10px" class="form-group" ng-repeat="uid in ncc.newConversationUIDs">
                            <label>User</label>
                            <input
                                type="text"
                                class="form-control"
                                ng-model="uid.name"
                                placeholder="User's name"
                                uib-typeahead="result.display for result in ncc.searchUsers.search($viewValue)"
                                typeahead-on-select="ncc.selectUser($item, uid, $label)"
                                typeahead-wait-ms="250"
                                typeahead-select-on-blue="true"
                            />
                        </div>
                        <div style="margin-top:10px" class="form-group">
                            <label>Message</label>
                            <textarea ng-model="ncc.newConversationText" class="form-control"></textarea>
                        </div>
                        <button class="btn btn-success" ng-click="ncc.createConversation()">Create conversation</button>
                    </div>
                </div>

                <div ng-controller="conversationsController as cc">
                    <accordion>
                        <accordion-group ng-repeat="conversation in cc.conversations.conversations" is-open="conversation.accordion_open">
                            <accordion-heading>
                                <h4 class="panel-title" ng-click="cc.accordionOpen(conversation)">
                                    <span ng-show="conversation.new_flag" class="label label-danger">!</span>
                                    {{conversation.name}}
                                    <button class="btn btn-danger pull-right" ng-click="cc.leaveConversation(conversation)">Leave conversation</button>
                                </h4>
                            </accordion-heading>
                            <a ng-href="{{ conversation.ref_link }}" target="_blank" ng-show="conversation.ref_link != undefined"><i class="fa fa-link"></i></a>
                            <i style="color:blue;cursor:pointer" class="fa fa-refresh" ng-click="cc.conversations.reloadConversation(conversation)"></i>
                            <div>
                                <p><strong>Participants:</strong> <span ng-repeat="conv_user in conversation.users">{{ conv_user.name }}<span ng-show="user.role > 0 && user.id != conv_user.id"> <i class="fa fa-times-circle" style="color:red;cursor:pointer" ng-click="cc.removeUserFromConversation(conversation, conv_user)"></i></span><span ng-show="!$last">, </span></span></p>
                                <div ng-show="user.role > 1" class="form-inline">
                                    <div class="form-group">
                                        <label>Add user to conversation:</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            ng-model="cc.new_user"
                                            uib-typeahead="result.display for result in cc.searchUsers.search($viewValue)"
                                            typeahead-on-select="cc.selectUser($item, uid, $label, conversation)"
                                            typeahead-wait-ms="250"
                                            typeahead-select-on-blue="true"
                                        />
                                    </div>
                                </div>
                                <blockquote ng-class="{'blockquote-reverse': message.uid == user.id}" ng-repeat="message in conversation.messages">
                                    <p>{{message.message}}</p>
                                    <footer style="font-size: 10px">{{message.fullname}} - {{message.timestamp * 1000 | date:"M/d/yy h:mm a"}}</footer>
                                </blockquote>
                                <hr/>
                                <form ng-submit="cc.addMessage(conversation)">
                                    <div class="input-group">
                                        <input type="text" class="form-control" ng-model="cc.new_message" placeholder="New message" />
                                        <span class="input-group-btn"><button class="btn btn-success" type="button" ng-click="cc.addMessage(conversation)">Send</button></span>
                                    </div>
                                </form>
                            </div>
                        </accordion-group>
                    </accordion>
                </div>
            </div>
        </div>

    </div>
</div>
