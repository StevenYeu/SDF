<?php
if(!isset($_SESSION["user"])){
    echo \helper\loginForm("You must be logged in to edit a term relationship");
    return;
}
?>

<link rel="stylesheet" type="text/css" href="/css/term.css" />

<script src="/js/term/bootstrap.min.js"></script>
<script src="/js/term/angular.min.js"></script>
<script src="/js/term/angular-modal-service.js"></script>

<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>

<script src="/js/module-error.js"></script>
<script src="/js/term/term.js"></script>
<script src="/js/term/term-edit-relationship.js"></script>

<?php
    if($community->shortName != 'scicrunch' && $community->portalName != 'scicrunch') $home = $community->shortName.' Home';
    else $home = 'Home';

    echo Connection::createBreadCrumbs('Edit Term Relationship',array($home, 'Term Dashboard'),array('/'.$community->portalName,'/'.$community->portalName.'/interlex/dashboard'),'Edit Term Relationship');
?>

<div class="container content" ng-app="termEditRelationshipApp" ng-cloak>
    <div ng-controller="termEditRelationshipCtrl" >
        <ng-include src="'/templates/term/term-messages.html'"></ng-include>

        <div style="margin-left:0px;padding-left:0px;" ng-show="missing_id === true">
            <pre class="alert alert-danger" >Term relationship id <?= array_pop(explode("/", $_SERVER[REQUEST_URI])) ?> does not exist!</pre>
        </div>

        <div ng-hide="missing_id === true">
        <div style="margin-left:0px;padding-left:0px;" class="col-md-6">
            <?php include_once 'term.relationship.form.php';?>
        </div>

        <div style="margin-right:0px;padding-right:0px;" class="col-md-6">
            <div ng-show="error === true">
                <pre class="alert alert-danger" >{{ feedback }}</pre>
            </div>
            <?php include_once 'term.relationship.info.php';?>
        </div>
        </div>
    </div>
</div>
