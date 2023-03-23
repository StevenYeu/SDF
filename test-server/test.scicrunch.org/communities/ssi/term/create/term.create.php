<link rel="stylesheet" type="text/css" href="/css/angular-chips.css" />
<link rel="stylesheet" type="text/css" href="/css/term.css" />

<script src="/js/term/bootstrap.min.js"></script>
<script src="/js/term/angular.min.js"></script>
<script src="/js/term/angular-modal-service.js"></script>

<!-- <script src="/js/angular-chips/angular-sanitize.js"></script> -->
<script src="/js/module-error.js"></script>
<script src="/js/angular-chips/ui-bootstrap.js"></script>
<!-- <script src="/js/angular-chips/ui-bootstrap-tpls-0.14.3.js"></script> -->
<script src="/js/angular-chips/angular-chips.min.js"></script>
<script src="/js/module-utilities.js"></script>
<script src="/js/term/term.js"></script>
<script src="/js/term/term-add.js"></script>

<!-- <script src="/js/angular-1.7.9/angular.min.js"></script> -->
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>


<?php
    if($community->shortName != 'scicrunch' && $community->portalName != 'scicrunch') $home = $community->shortName.' Home';
    else $home = 'Home';

    echo Connection::createBreadCrumbs('Term Submission',array($home, 'Term Dashboard'),array('/'.$community->portalName,'/'.$community->portalName.'/interlex/dashboard'),'Create Term');
?>


<div class="container content" ng-app="termAddApp" ng-cloak>
    <?php if (!isset($_SESSION['user'])) { ?>
        <div class="alert alert-danger">
            You are currently not logged in to SciCrunch. Please login to create a term.
        </div>
    <?php } ?>


    <div class="row" ng-controller="termAddCtrl" >
        <ng-include src="'/templates/term/term-messages.html'"></ng-include>

        <div ng-show="<?= isset($_SESSION['user']) ?>">
        <div class="col-md-6">
            <?php include_once 'term.create.form.php';?>
        </div>

        <div class="col-md-6">
            <?php include_once 'term.matches.php';?>
        </div>

        </div>

        <?php include_once 'templates/term/term-curie-modal.html';?>
        <?php include_once 'templates/term/term-synonym-modal.html';?>
        <?php include_once 'templates/term/term-existingid-modal.html';?>
        <?php include_once 'templates/term/curie-catalog-add-modal.html';?>
        <?php include_once 'templates/term/term-ontology-add-modal.html';?>
    </div>
</div>
