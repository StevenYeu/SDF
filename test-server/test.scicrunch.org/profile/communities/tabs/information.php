<?php
$comm_views = array_keys($community->views);
$views = Array();
foreach($comm_views as $cv) $views[] = $sources[$cv];
uasort($views, function($a, $b){
    if(!$a || !$b) return 0;
    $titlea = $a->getTitle();
    $titleb = $b->getTitle();
    if($titlea < $titleb) return -1;
    if($titlea > $titleb) return 1;
    return 0;
});
?>
<link rel="stylesheet" href="/css/community-search.css" />
<div class="snippet-load back-hide"></div>
<div class="background"></div>
<div class="tab-pane fade <?php if ($section == 'information') echo 'in active' ?>" id="information">

    <!--Profile Blog-->
    <div class="panel panel-profile">
        <div class="panel-heading overflow-h">
            <h2 class="panel-title heading-sm pull-left"><i
                    class="fa fa-info"></i>Information</h2>
            <a href="<?php echo $profileBase?>account/communities/<?php echo $community->portalName ?>/edit"><i
                    class="fa fa-cog pull-right tut-cog"></i></a>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-8">
                    <div class="profile-blog" style="padding:5px;">
                        <b>Description</b>

                        <p>
                            <?php echo $community->description ?>
                        </p>
                        <b>Website Location</b>

                        <p>
                            <?php echo $community->url ?>
                        </p>
                        <b>Visibility</b>

                        <p>
                            <?php
                            if ($community->private) {
                                if($community->front_page_visible) {
                                    echo 'Private, but front page is visible to non-members';
                                } else {
                                    echo 'Private';
                                }
                            } else {
                                echo 'Public';
                            }
                            ?>
                        </p>
                        <b>Google Analytics Code</b>

                        <p>
                            <?php
                                if(!is_null($community->ga_code)){
                                    echo $community->ga_code;
                                }else{
                                    echo "not set";
                                }
                            ?>
                        </p>

                        <b>Mailchimp API Key</b>

                        <p>
                            <?php
                                if(!is_null($community->mailchimp_api_key)){
                                    echo $community->mailchimp_api_key;
                                }else{
                                    echo "not set";
                                }
                            ?>
                        </p>

                        <b>Mailchimp Default List ID</b>

                        <p>
                            <?php
                                if(!is_null($community->mailchimp_default_list)){
                                    echo $community->mailchimp_default_list;
                                }else{
                                    echo "not set";
                                }
                            ?>
                        </p>

                        <?php if($community->private): ?>
                            <b>Pending user requests</b>
                            <p>
                                <a href="<?php echo $profileBase ?>account/communities/<?php echo $community->portalName ?>/pending-user-requests"><?php echo CommunityAccessRequest::getPendingCount($community) ?></a>
                            </p>
                        <?php endif ?>
                    </div>
                </div>
                <div class="col-sm-4">
                    <div class="profile-blog" style="padding:5px;">
                        <b>Logo</b>
                        <img class="img-responsive" style="float:none"
                             src="/upload/community-logo/<?php echo $community->logo ?>"/>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--/end row-->
    <!--End Profile Blog-->

    <hr>

    <div class="row margin-bottom-20">
        <!--Profile Post-->
        <div class="col-sm-6">
            <?php $users = $community->getUsers(); ?>
            <div class="panel panel-profile no-bg">
                <div class="panel-heading overflow-h">
                    <h2 class="panel-title heading-sm pull-left"><i class="fa fa-group"></i>Users (<?php echo count($users) ?>)</h2>
                    <a href="javascript:void(0)" class="user-add"><i class="fa fa-plus pull-right"></i></a>
                </div>
                <div id="scrollbar" class="panel-body contentHolder">
                    <?php
                    $colors = array('', 'color-two', 'color-four', 'color-one', 'color-three');
                    $levels = array('', 'User', 'Moderator', 'Administrator', 'Owner');
                    if (count($users) > 0) {
                        foreach ($users as $user) {
                            ?>
                            <div class="profile-post <?php echo $colors[$user['level']] ?>" style="position: relative">

                                <div class="profile-post-in" style="margin-left: 10px;;margin-right: 60px">
                                    <h3 class="heading-xs"><a href="#"><?php echo $user['name'] ?></a></h3>

                                    <p>
                                        <?php echo $levels[$user['level']] ?>
                                        -
                                        joined <?php echo date("M j, Y", $user["date"]) ?>
                                    </p>
                                </div>
                                <?php if ($_SESSION['user']->levels[$community->id] > $user['level']) { ?>
                                    <div class="btn-group" style="position:absolute;right:10px;top:20px ">
                                        <button type="button" class="btn-u btn-u-default btn-default dropdown-toggle"
                                                data-toggle="dropdown">
                                            <i class="fa fa-cog"></i>
                                            <i class="fa fa-angle-down"></i>
                                        </button>
                                        <ul class="dropdown-menu" role="menu" style="left:auto;right:0">
                                            <li><a href="javascript:void(0)" level="<?php echo $user['level']?>" class="user-edit" uid="<?php echo $user['uid'] ?>" user="<?php echo $user['name'];?>"><i class="fa fa-wrench"></i> Edit Permissions</a></li>
                                            <li>
                                                <a href="/forms/community-forms/user-remove.php?uid=<?php echo $user['uid']?>&cid=<?php echo $community->id ?>"><i
                                                        class="fa fa-times"></i> Remove User</a></li>
                                        </ul>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php
                        }
                    }
                    ?>

                </div>
            </div>
        </div>
        <!--End Profile Post-->

        <!--Profile Event-->
        <div class="col-sm-6 md-margin-bottom-20">
            <div class="panel panel-profile no-bg">
                <div class="panel-heading overflow-h">
                    <h2 class="panel-title heading-sm pull-left"><i class="fa fa-file-archive-o"></i>Sources</h2>
                    <a href="<?php echo $profileBase?>account/communities/<?php echo $community->portalName ?>/sources"><i
                            class="fa fa-cog pull-right"></i></a>
                </div>
                <div id="scrollbar2" class="panel-body contentHolder">
                    <?php
                    foreach ($views as $id => $view) {
                        if(!$view) continue;
                        if($view->image && !\helper\startsWith($view->nif, $view->image)) {
                            $image_html = '<img src="' . $view->image . '" style="width:60px;margin-right:20px;vertical-align:top"/>';
                        } else {
                            $image_html = '<div style="width:60px;margin-right:20px;vertical-align:top;display:inline-block"></div>';
                        }
                        echo '<div class="profile-event">';
                        echo $image_html;
                        echo '<div class="overflow-h" style="display: inline-block">';
                        echo '<h3 class="heading-xs"><a href="/' . $community->portalName . '/about/sources/' . $view->nif . '">' . $view->getTitle() . '</a></h3>';
                        echo '<h3 class="heading-xs"><a style="cursor:pointer" class="snippet-edit" cid="' . $community->id . '" view="' . $view->nif . '">[Edit Snippet]</a></h3>';
                        echo '</div>';
                        echo '</div>';
                    }
                    ?>

                </div>
            </div>
        </div>
        <!--End Profile Event-->
    </div>
    <!--/end row-->

</div>
<div class="back-hide user-add-container no-padding">
    <div class="close dark less-right">X</div>
    <form action="/forms/community-forms/user-add.php?cid=<?php echo $community->id ?>" method="post"
          class="sky-form create-form" enctype="multipart/form-data">

        <fieldset>
            <section>
                <label class="label">Find Existing User</label>
                <label class="input">
                    <i class="icon-append fa fa-question-circle"></i>
                    <input type="text" placeholder="Focus to view the tooltip" class="user-find" name="name">
                    <input type="hidden" class="user-id" name="id"/>
                    <input type="hidden" class="cid" value="<?php echo $community->id ?>"/>
                    <div class="autocomplete_append auto" style="z-index:10"></div>
                    <b class="tooltip tooltip-top-right">Search for a user by name or email</b>
                </label>
            </section>
            <section>
                <label class="label">Or Invite New User</label>
                <label class="input">
                    <i class="icon-append fa fa-question-circle"></i>
                    <input type="email" placeholder="Focus to view the tooltip" name="email">
                    <b class="tooltip tooltip-top-right">Email of person to invite</b>
                </label>
            </section>
            <section>
                <label class="label">User Level</label>
                <label class="select">
                    <i class="icon-append fa fa-question-circle"></i>
                    <select name="level">
                        <?php
                        for($i=1;$i<4;$i++){
                            if($_SESSION['user']->levels[$community->id]>=$i)
                                echo '<option value="'.$i.'">'.$levels[$i].'</option>';
                        }
                        ?>
                    </select>
                </label>
            </section>
        </fieldset>
        <footer>
            <button class="btn-u btn-u-default" type="submit">Add User</button>
        </footer>
    </form>
</div>
<div class="back-hide user-edit-container no-padding">
    <div class="close dark less-right">X</div>
    <form action="/forms/community-forms/user-edit.php?cid=<?php echo $community->id ?>" method="post"
          class="sky-form create-form" enctype="multipart/form-data">

        <fieldset>
            <section>
                <label class="label">User</label>
                <div class="theName"></div>
                <input name="uid" type="hidden" class="uid"/>
            </section>
            <section>
                <label class="label">User Level</label>
                <label class="select">
                    <i class="icon-append fa fa-question-circle"></i>
                    <select name="level" class="edit-level">
                        <?php
                        for($i=1;$i<4;$i++){
                            if($_SESSION['user']->levels[$community->id]>=$i)
                                echo '<option value="'.$i.'">'.$levels[$i].'</option>';
                        }
                        ?>
                    </select>
                </label>
            </section>
        </fieldset>
        <footer>
            <button class="btn-u btn-u-default" type="submit">Edit User</button>
        </footer>
    </form>
</div>
