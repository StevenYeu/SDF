<?php
    echo Connection::createBreadCrumbs('My Notifications', array('Home', 'Account'), array($profileBase, $profileBase . 'account'), 'My Notifications');
?>

<div class="profile container content">
    <div class="row">
        <!--Left Sidebar-->
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/profile/left-column.php'; ?>
        <!--End Left Sidebar-->

        <div class="col-md-9">
            <!--Profile Body-->
            <div class="profile-body">
                <!-- test notification script -->
                <?php
                    $_SESSION["test_notification_script"] = 0;
                ?>
                <?php if($_SESSION['user']->role > 1): ?>
                    <?php
                        if($_GET["test_notification"] == "on") $_SESSION["test_notification_script"] = 1;
                    ?>
                    <div class="pull-right">
                        <?php if($_SESSION["test_notification_script"] == 1): ?>
                            <a class="btn btn-danger" href="<?php echo $profileBase ?>account/notifications">Stop notification script</a>
                        <?php else: ?>
                            <a class="btn btn-primary" href="<?php echo $profileBase ?>account/notifications?test_notification=on">Run notification script</a>
                        <?php endif ?>
                    </div>
                <?php endif ?>
                <!-- end test notification script -->
                
                <div class="tab-v5">
                    <ul class="nav nav-tabs" role="tablist">
                        <li class="active"><a href="#interlex" role="tab" data-toggle="tab"><strong>InterLex</strong></a></li>
                    </ul>
                </div>

                <div class="tab-content">
                    <?php include_once 'profile/other-pages/notifications/interlex-notifications.php';?>
                </div>
            </div>
        </div>
        <!--End Profile Body-->
    </div>
    <!--/end row-->
</div>
<!--/container-->
