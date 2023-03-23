<?php $mod_flag = $_SESSION["user"]->role > 1; ?>
<?php
    $dev_view_flag = isset($_SESSION["loginconfirm"]);
    if($dev_view_flag) unset($_SESSION["loginconfirm"]);
?>

<?php if(!$dev_view_flag): ?>
    <?php include $_SERVER["DOCUMENT_ROOT"] . "/profile/developer/confirm.php"; ?>
<?php else: ?>
<?php
    echo Connection::createBreadCrumbs('My Resources',array('Home','Account'),array($profileBase,$profileBase.'account'),'API Keys');
?>

    <script src="/js/api-key.js"></script>
    <?php if($mod_flag): ?>
        <script src="/js/angular-1.7.9/angular.min.js"></script>
        <script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
        <script src="/js/angular-1.7.9/angular-sanitize.js"></script>
        <script src="/js/module-error.js"></script>
        <script src="/js/module-keys-mod.js"></script>
    <?php endif ?>

    <div class="profile container content">
        <div class="row">
            <?php include $_SERVER["DOCUMENT_ROOT"] . "/profile/left-column.php"; ?>
            <div class="col-md-9">
                <div class="profile-body">
                    <div class="tab-v1">
                        <ul class="nav nav-tabs nav-tabs-js margin-bottom-20">
                            <li class="page1-tab active"><a href="#api-keys" data-toggle="tab">API Keys</a></li>
                            <?php if($mod_flag): ?>
                                <li class="final-tab"><a href="#api-keys-mod" data-toggle="tab">API Moderation</a></li>
                            <?php endif ?>
                        </ul>
                        <div class="tab-content">
                            <?php include $GLOBALS["DOCUMENT_ROOT"] . "/profile/developer/tabs/api.php" ?>
                            <?php if($mod_flag) include $GLOBALS["DOCUMENT_ROOT"] . "/profile/developer/tabs/api-keys-mod.php" ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>
