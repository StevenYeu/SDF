<link rel="stylesheet" type="text/css" href="/css/term.css" />

<script src="/js/term/bootstrap.min.js"></script>
<script src="/js/term/angular.min.js"></script>
<script src="/js/term/angular-modal-service.js"></script>

<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>

<script src="/js/module-error.js"></script>
<script src="/js/term/term.js"></script>
<script src="/js/term/term-add-relationship.js"></script>

<?php
    if($community->shortName != 'scicrunch'  && $community->portalName != 'scicrunch') $home = $community->shortName.' Home';
    else $home = 'Home';

    echo Connection::createBreadCrumbs('Add Term Relationship',array($home, 'Term Dashboard'),array('/'.$community->portalName,'/'.$community->portalName.'/interlex/dashboard'),'Add Term Relationship');
?>


<div class="container content" ng-app="termAddRelationshipApp" ng-cloak>
    <?php if (!isset($_SESSION['user'])) { ?>
        <div class="alert alert-danger">
            You are currently not logged in to SciCrunch. Please login to add term relationships.
        </div>
    <?php } ?>


    <div class="row" ng-controller="termAddRelationshipCtrl" >
        <ng-include src="'/templates/term/term-messages.html'"></ng-include>

        <div ng-show="<?= isset($_SESSION['user']) ?>">
        <div class="col-md-6">
            <input type="hidden" id="id" value="<?= $_GET['id'] ?>"></input>

            <?php include_once 'term.relationship.form.php';?>
        </div>

        <div class="col-md-6">
            <?php include_once 'term.relationship.info.php';?>
        </div>

        </div>
    </div>
</div>
