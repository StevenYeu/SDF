<link rel="stylesheet" href="/js/node_modules/angular-material/angular-material.min.css">
<link rel="stylesheet" href="/js/node_modules/angular-material/angular-material.layouts.min.css">
<link rel="stylesheet" type="text/css" href="/css/term.css" />

<script src="/assets/plugins/jquery-3.4.1.min.js"></script>
<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>

<script src="/js/bootstrap-filestyle.min.js"></script>
<script src="/js/ng-file-upload/ng-file-upload-shim.min.js"></script>
<script src="/js/ng-file-upload/ng-file-upload.min.js"></script>

<script src="/js/module-error.js"></script>
<script src="/js/term/term.js"></script>
<script src="/js/term/term-upload.js"></script>


<?php
    if($community->shortName != 'scicrunch' && $community->portalName != 'scicrunch') $home = $community->shortName.' Home';
    else $home = 'Home';

    echo Connection::createBreadCrumbs('Term Bulk Upload',array($home, 'Term Dashboard'),array('/'.$community->portalName,'/'.$community->portalName.'/interlex/dashboard'),'Term Upload');
?>



<div class="container content" ng-app="termUploadApp">
    <?php if (!isset($_SESSION['user'])): ?>
        <div class="alert alert-danger">
            You are currently not logged in to SciCrunch. Please login to create a term.
        </div>
    <?php elseif ($_SESSION['user']->role < 1): ?>
        <div class="alert alert-danger">
            You don't have permission to use this interface. Please contact info@scicrunch.org.
        </div>
    <?php else: ?>
        <div class="row" ng-controller="termUploadCtrl" ng-cloak>

            <ng-include src="'/templates/term/term-messages.html'"></ng-include>

            <div ng-show="<?= isset($_SESSION['user']) ?>">
            <div class="col-md-12">
                <?php include_once 'term.upload.form.php';?>
            </div>

            <div class="col-md-12 panel" style="padding:10px">
                <fieldset><pre ng-show="result.length > 0">{{ result }}</pre></fieldset>
            </div>
        </div>
    <?php endif; ?>
</div>
