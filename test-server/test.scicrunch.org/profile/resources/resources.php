<?php

$holder = new Resource();
$resources = $holder->getByUser($_SESSION['user']->id,0,200);

?>
<?php
echo Connection::createBreadCrumbs('My Resources',array('Home','Account'),array($profileBase,$profileBase.'account'),'My Resources');
?>
<div class="profile container content">
    <div class="row">
        <!--Left Sidebar-->
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/profile/left-column.php'; ?>
        <!--End Left Sidebar-->

        <div class="col-md-9">
            <!--Profile Body-->
            <div class="profile-body">
                <div class="tab-v1">
                    <ul class="nav nav-tabs nav-tabs-js margin-bottom-20">
                        <li class="page1-tab active"><a href="#mention-subscribed-resources" data-toggle="tab">Mention Subscribed Resources</a></li>
                        <li class="page2-tab"><a href="#submitted-resources" data-toggle="tab">Submitted Resources</a></li>
                        <li class="final-tab"><a href="#owned-resources" data-toggle="tab">Owned Resources</a></li>
                    </ul>
                    <div class="tab-content">
                        <?php include $GLOBALS["DOCUMENT_ROOT"] . "/profile/resources/tabs/submitted-resources.php" ?>
                        <?php include $GLOBALS["DOCUMENT_ROOT"] . "/profile/resources/tabs/mention-subscribed-resources.php" ?>
                        <?php include $GLOBALS["DOCUMENT_ROOT"] . "/profile/resources/tabs/owned-resources.php" ?>
                    </div>
                </div>
            </div>
        </div>
        <!--End Profile Body-->
    </div>
    <!--/end row-->
</div>
<!--/container-->
<!--=== End Profile ===-->
