<?php

$holder = new Saved();
$searches = $holder->getUserSearches($_SESSION['user']->id);
$commArray = Array();
$searches_fmt = Array();

foreach($searches as $saved){
    if (!isset($commArray[$saved->cid])) {
        $comm = new Community();
        $comm->getByID($saved->cid);
        $commArray[$saved->cid] = $comm;
    } else {
        $comm = $commArray[$saved->cid];
    }

    $fmt = Array();
    $fmt["community"] = $comm;
    $fmt["saved"] = $saved;
    $subs = Subscription::loadArrayBy(Array("fid"), Array($saved->id));
    foreach($subs as $sub) {
        if(\helper\startsWith($sub->type, "saved-search")) {
            $fmt["subscription"] = $sub;
            break;
        }
    }
    $searches_fmt[]= $fmt;
}

?>

<script>
$(function() {
    $('[data-toggle="popover"]').popover();
});
</script>

<?php echo Connection::createBreadCrumbs('My Saved Searches', array('Home', 'Account'), array($profileBase, $profileBase . 'account'), 'My Saved Searches') ?>
<div class="profile container content">
    <div class="row">
        <!--Left Sidebar-->
        <?php include $_SERVER['DOCUMENT_ROOT'] . '/profile/left-column.php'; ?>
        <!--End Left Sidebar-->

        <div class="col-md-9">
            <!--Profile Body-->
            <div class="profile-body">
                <!--Service Block v3-->
                <div class="table-search-v2 margin-bottom-20">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                            <tr>
                                <th>Name</th>
                                <th></th>
                                <th>Community</th>
                                <th>Category</th>
                                <th>Query</th>
                                <th>Insert Time</th>
                                <th>Email Alerts</th>
                                <th>Actions</th>
                            </tr>
                            </thead>
                            <tbody>

                            <?php foreach ($searches_fmt as $sf): ?>
                                <?php
                                $comm = $sf["community"];
                                $saved = $sf["saved"];
                                $sub = $sf["subscription"];
                                if($saved->name == "interlex") $community->type = "interlex";
                                $normal_link = $saved->returnURL(NULL, $community);
                                $update_link = $saved->returnURL($sub->id, $community);
                                ?>
                                <tr>
                                    <td><a style="color:blue" href="<?php echo $normal_link ?>"><?php echo $saved->name ?></a></td>
                                    <td>
                                    <?php if($saved->category !== "literature"): ?>
                                        <a style="color:blue" href="<?php echo $update_link ?>" title="See the most recent updates for your saved search">
                                            <?php echo $sub->new_data_scicrunch === 1 ? \helper\htmlElement("notification-inline", Array("text" => "New")) : "Latest" ?>
                                        </a>
                                    <?php elseif($sub->new_data_scicrunch === 1): ?>
                                        <a style="color:blue" href="<?php echo $update_link ?>"><?php echo \helper\htmlElement("notification-inline", Array("text" => "New")) ?></a>
                                    <?php endif ?>
                                    </td>
                                    <td><?php echo $comm->shortName ?></td>
                                    <td><?php echo $saved->category ?></td>
                                    <?php if($saved->display && $saved->display != ''): ?>
                                        <td><?php echo $saved->display ?></td>
                                    <?php else: ?>
                                        <td><?php echo $saved->query ?></td>
                                    <?php endif ?>
                                    <td><?php echo date('h:ia F j, Y', $saved->time) ?></td>
                                    <td>
                                        <?php if($sub->email_notify): ?>
                                            <a href="/forms/other-forms/toggle-subscription-notification.php?type=<?php echo $sub->type ?>&id=<?php echo $saved->id ?>&action=unsubscribe-email"><i class="fa fa-check-square-o"></i></a>
                                        <?php else: ?>
                                            <a href="/forms/other-forms/toggle-subscription-notification.php?type=<?php echo $sub->type ?>&id=<?php echo $saved->id ?>&action=subscribe-email"><i class="fa fa-square-o"></i></a>
                                        <?php endif ?>
                                    </td>
                                    <td>
                                        <div class="btn-group" style="margin-top:-4px">
                                            <button type="button" class="btn-u btn-default dropdown-toggle" data-toggle="dropdown">Action <i class="fa fa-angle-down"></i></button>
                                            <ul class="dropdown-menu" role="menu">
                                                <li><a href="<?php echo $normal_link ?>">Goto</a></li>
                                                <li><a href="<?php echo $update_link ?>"><i class="fa fa-clock-o"></i> Goto Recently Updated</a></li>
                                                <li><a href="javascript:void(0)" saved="<?php echo $saved->id ?>" saveName="<?php echo $saved->name ?>" class="saved-edit"><i class="fa fa-cogs"></i> Rename</a></li>
                                                <li><a href="/forms/other-forms/delete-saved-search.php?id=<?php echo $saved->id ?>"><i class="fa fa-times"></i> Delete</a></li>
                                            </ul>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach ?>

                            </tbody>
                        </table>
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
<div class="background"></div>
<div class="saved-this-search back-hide no-padding">
    <div class="close dark less-right">X</div>
    <form method="post" action="/forms/other-forms/edit-saved-search.php"
          id="header-component-form" class="sky-form" enctype="multipart/form-data">
        <header>Rename This Saved Search</header>
        <fieldset>
            <section>
                <label class="label">Name</label>
                <label class="input">
                    <i class="icon-append fa fa-question-circle"></i>
                    <input type="hidden" name="id" class="saved-id-input"/>
                    <input type="text" name="name" class="saved-name-input" placeholder="Focus to view the tooltip">
                    <b class="tooltip tooltip-top-right">The name of your saved search.</b>
                </label>
            </section>
        </fieldset>

        <footer>
            <button type="submit" class="btn-u btn-u-default" style="width:100%">Rename</button>
        </footer>
    </form>
</div>
