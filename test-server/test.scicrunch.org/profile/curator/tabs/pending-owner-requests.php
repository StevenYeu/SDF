<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>
<script src="/js/module-error.js"></script>
<script src="/js/module-resource.js"></script>
<script src="/js/module-curator-conversation.js"></script>
<script src="/js/module-curator-pendingowners.js"></script>
<div class="tab-pane fade in active" ng-app="pendingOwnersApp" ng-controller="pendingOwners as po" ng-cloak>
    <div class="table-search-v2 margin-bottom-20">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Resource</th>
                        <th>Message</th>
                        <th>Approve/Deny</th>
                        <th>Discussion</th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="pending_owner in po.pending_owners">
                        <td>{{ pending_owner.name }}</td>
                        <td>{{ pending_owner.email }}</td>
                        <td><a ng-href="/browse/resources/{{pending_owner.rid}}">{{ pending_owner.resource_name }}</a></td>
                        <td>{{ pending_owner.text_data }}</td>
                        <td>
                            <i class="fa fa-check" style="cursor:pointer;color:green" ng-click="po.review(pending_owner, 'accept')"></i>
                            <i class="fa fa-times" style="cursor:pointer;color:red" ng-click="po.review(pending_owner, 'reject')"></i>
                        </td>
                        <td>
                            <i class="fa fa-plus-circle" ng-hide="pending_owner.conversation.id != undefined" style="cursor:pointer;color:blue" ng-click="po.createConversation(pending_owner)"></i>
                            <a target="_blank" ng-show="pending_owner.conversation.id != undefined" ng-href="{{ '/account/messages?convID=' + pending_owner.conversation.id }}"><i class="fa fa-comments" style="cursor:pointer;color:blue"></i></a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <ul uib-pagination
        ng-hide="po.total_count === 0"
        total-items="po.total_count"
        items-per-page="po.per_page"
        ng-model="po.current_page"
        ng-change="po.page(po.current_page)"
        max-size="7"
        boundary-links="true"
    ></ul>
</div>
