<?php
if(!isset($_SESSION["user"]) || $_SESSION["user"]->role === 0) exit;

$link_base = "/account/curator";
if(isset($community)) $link_base = "/" . $community->portalName . $link_base;

function active($a, $b) {
    if(is_array($b)) {
        return in_array($a, $b) ? " active" : "";
    }
    return $a === $b ? " active" : "";
}

?>


<?php
echo Connection::createBreadCrumbs('My Resources',array('Home','Account'),array($profileBase,$profileBase.'account'),'Curator');
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
                    <ul class="nav nav-tabs margin-bottom-20">
                        <li class="page1-tab<?php echo active($arg1, Array("resources", "resources-recent")) ?>"><a href="<?php echo $link_base ?>/resources">Resource registry</a></li>
                        <li class="page2-tab<?php echo active($arg1, "bulk-upload") ?>"><a href="<?php echo $link_base ?>/bulk-upload">Bulk upload</a></li>
                        <li class="page2-tab<?php echo active($arg1, "resource-relationships-upload") ?>"><a href="<?php echo $link_base ?>/resource-relationships-upload">Resource relationships upload</a></li>
                        <li class="page2-tab<?php echo active($arg1, "rrid-mappings") ?>"><a href="<?php echo $link_base ?>/rrid-mappings">RRID mappings</a></li>
                        <li class="page2-tab<?php echo active($arg1, "rrid-failure-log") ?>"><a href="<?php echo $link_base ?>/rrid-failure-log">RRID failure log</a></li>
                        <li class="page2-tab<?php echo active($arg1, "failed-searches-log") ?>"><a href="<?php echo $link_base ?>/failed-searches-log">Failed searches</a></li>
                        <li class="page2-tab<?php echo active($arg1, "communities") ?>"><a href="<?php echo $link_base ?>/communities">Communities</a></li>
                        <li class="page2-tab<?php echo active($arg1, "resource-suggestions") ?>"><a href="<?php echo $link_base ?>/resource-suggestions">Resource suggestions</a></li>
                        <li class="page2-tab<?php echo active($arg1, "system-messages") ?>"><a href="<?php echo $link_base ?>/system-messages">System messages</a></li>
                        <li class="page2-tab<?php echo active($arg1, "missing-community-views") ?>"><a href="<?php echo $link_base ?>/missing-community-views">Missing community views</a></li>
                        <li class="page2-tab<?php echo active($arg1, "views-with-errors") ?>"><a href="<?php echo $link_base ?>/views-with-errors">Views with errors</a></li>
                        <li class="page2-tab<?php echo active($arg1, "view-statuses") ?>"><a href="<?php echo $link_base ?>/view-statuses">View Statuses</a></li>
                        <li class="page2-tab<?php echo active($arg1, "pending-owner-requests") ?>"><a href="<?php echo $link_base ?>/pending-owner-requests">Pending owner requests</a></li>
                        <li class="page2-tab<?php echo active($arg1, "rrid-prefix-redirect") ?>"><a href="<?php echo $link_base ?>/rrid-prefix-redirect">RRID prefix redirects</a></li>
                        <?php if($_SESSION["user"]->role > 1): ?>
                            <li class="final-tab<?php echo active($arg1, "scicrunch-data") ?>"><a href="<?php echo $link_base ?>/scicrunch-data">SciCrunch Data</a></li>
                        <?php endif ?>
                    </ul>
                    <div class="tab-content">
                        <?php if($arg1 === "resources") include $GLOBALS["DOCUMENT_ROOT"] . "/profile/curator/tabs/resources.php" ?>
                        <?php if($arg1 === "resources-recent") include $GLOBALS["DOCUMENT_ROOT"] . "/profile/curator/other-pages/resources-recent.php" ?>
                        <?php if($arg1 === "bulk-upload") include $GLOBALS["DOCUMENT_ROOT"] . "/profile/curator/tabs/bulk-upload.php" ?>
                        <?php if($arg1 === "resource-relationships-upload") include $GLOBALS["DOCUMENT_ROOT"] . "/profile/curator/tabs/resource-relationships-upload.php" ?>
                        <?php if($arg1 === "rrid-mappings") include $GLOBALS["DOCUMENT_ROOT"] . "/profile/curator/tabs/rrid-mappings.php" ?>
                        <?php if($arg1 === "rrid-failure-log") include $GLOBALS["DOCUMENT_ROOT"] . "/profile/curator/tabs/rrid-failure-log.php" ?>
                        <?php if($arg1 === "failed-searches-log") include $GLOBALS["DOCUMENT_ROOT"] . "/profile/curator/tabs/failed-searches-log.php" ?>
                        <?php if($arg1 === "communities") include $GLOBALS["DOCUMENT_ROOT"] . "/profile/curator/tabs/communities.php" ?>
                        <?php if($arg1 === "resource-suggestions") include $GLOBALS["DOCUMENT_ROOT"] . "/profile/curator/tabs/resource-suggestions.php" ?>
                        <?php if($arg1 === "view-statuses") include $GLOBALS["DOCUMENT_ROOT"] . "/profile/curator/tabs/view-statuses.php" ?>
                        <?php if($arg1 === "system-messages") include $GLOBALS["DOCUMENT_ROOT"] . "/profile/curator/tabs/system-messages.php" ?>
                        <?php if($arg1 === "missing-community-views") include $GLOBALS["DOCUMENT_ROOT"] . "/profile/curator/tabs/missing-community-views.php" ?>
                        <?php if($arg1 === "views-with-errors") include $GLOBALS["DOCUMENT_ROOT"] . "/profile/curator/tabs/views-with-errors.php" ?>
                        <?php if($arg1 === "pending-owner-requests") include $GLOBALS["DOCUMENT_ROOT"] . "/profile/curator/tabs/pending-owner-requests.php" ?>
                        <?php if($arg1 === "rrid-prefix-redirect") include $GLOBALS["DOCUMENT_ROOT"] . "/profile/curator/tabs/rrid-prefix-redirect.php" ?>
                        <?php if($arg1 === "scicrunch-data") include $GLOBALS["DOCUMENT_ROOT"] . "/profile/curator/tabs/scicrunch-data.php" ?>
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
