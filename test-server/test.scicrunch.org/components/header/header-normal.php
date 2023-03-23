<?php

$holder = new Component();
$components = $holder->getByCommunity($community->id);
$header_img_class = "community-logo";
$header_name_class = "community-name";

// Manu
$header_name_class = "community-name-small";

if(isset($components['header']) && $components['header'][0]->icon1 && $components['header'][0]->icon1 == "large"){
    $header_img_class = "community-logo-large";
    $header_name_class = "community-name-small";
}


// Manu
error_log("*********************************************** header-normal");

$n_notifs = 0;
$n_notifs_mentions = 0;
$n_notifs_searches = 0;
$n_notifs_conversations = 0;
$n_saved_searches = 0;
if(isset($_SESSION["user"])){
    $n_notifs_mentions = Subscription::userUpdates($_SESSION["user"], "resource-mention", "scicrunch");
    $n_notifs_searches = Subscription::userUpdates($_SESSION["user"], "saved-search-data", "scicrunch");
    $n_notifs_searches += Subscription::userUpdates($_SESSION["user"], "saved-search-literature", "scicrunch");
    $n_notifs_searches += Subscription::userUpdates($_SESSION["user"], "saved-search-summary", "scicrunch");
    $n_saved_searches = Saved::getUserSavedCount($_SESSION["user"]);

    $cxn = new Connection();
    $cxn->connect();

    $conversation_count = $cxn->select("user_messages_conversations_users", Array("count(*)"), "i", Array($_SESSION["user"]->id), "where uid=? and new_flag=1");
    $n_notifs_conversations = $conversation_count[0]["count(*)"];

    $rrid_reports_count = $cxn->select("rrid_report_item a inner join rrid_report b on a.rrid_report_id = b.id", Array("count(*)"), "i", Array($_SESSION["user"]->id), "where b.uid=? and a.updated_flag=1");
    $n_notifs_rrid_reports = $rrid_reports_count[0]["count(*)"];

    $cxn->close();

    $n_notifs = $n_notifs_mentions + $n_notifs_searches + $n_notifs_conversations + $n_notifs_rrid_reports;
}

if(!isset($search)) {
    $search = new Search();
    $search->community = $community;
}


// print "<pre>"; print_r($vars); print "</pre>";
?>

<style>
    <?php if($component->color1){ ?>
    .header .navbar-default .navbar-nav > li > a:hover,
    .header .navbar-default .navbar-nav > .active > a {
        color: <?php echo '#'. $component->color1?>;
        border-bottom: solid 2px <?php echo '#'. $component->color1?>;
    }

    .header .dropdown-menu {
        border-top: solid 2px <?php echo '#'. $component->color1?>;
    }

    .header .navbar .nav > li > .search:hover {
        color: <?php echo '#'. $component->color1?>;
        background: #f7f7f7;
        border-bottom-color: <?php echo '#'. $component->color1?>;
    }

    .topbar .loginbar li a:hover {
        color: <?php echo '#'. $component->color1?>;
    }

    .header .btn-u {
        background: <?php echo '#'. $component->color1?>;
    }

    .header .btn-u:hover {
        background: <?php echo '#'. $component->color1?>;
    }

    <?php } ?>
    .header .container > .navbar-header, .header .container-fluid > .navbar-header, .header .container > .navbar-collapse, .header .container-fluid > .navbar-collapse {
        margin-bottom: -30px;
    }
</style>

<?php
$holder = new Component_Data();
if($vars){
    $params = '?q='.$vars['q'].'&l='.$vars['l'];
} else {
    $params = '';
}

?>
<div class="header-v1" style="z-index:99">
    <div class="topbar-v1 margin-bottom-20" <?php if($vars['editmode']) echo 'style="position:fixed;top:0;left:0;z-index:110;width:100%"' ?>>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                </div>

                <div class="col-md-6">
                    <ul class="list-unstyled top-v1-data">
                        <?php include $_SERVER['DOCUMENT_ROOT'] . '/components/header/topbar.php'; ?>
                        <!-- <li><i class="search2 fa fa-info-circle tutorial-header-btn" style="cursor:pointer"></i></li> -->
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="header <?php if($vars['editmode']) echo 'editmode' ?>">
    <!-- Topbar -->
    <div class="topbar">
        <div class="container">
            <!-- Topbar Navigation -->
            <!-- End Topbar Navigation -->
        </div>
    </div>
    <!-- End Topbar -->

    <!-- Navbar -->
    <div class="navbar navbar-default" role="navigation" style="margin-bottom: 20px;">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header">
                <?php if($vars["type"] == "interlex"): ?>
                    <a class="navbar-brand" href="/scicrunch/interlex/dashboard">
                        <img src="https://scicrunch.org/upload/community-components/InterLex_MultiColor_cropped_698d51a19d8a121c.png" style="width: 200px">
                    </a>
                <?php else: ?>
                    <a class="navbar-brand" href="/<?php echo $community->portalName ?>">
                        <!-- Manu img class="<?php echo $header_img_class ?>" src="/upload/community-logo/<?php echo $community->logo ?>"/-->
                        <span class="community-name"><?php echo "Software Discovery Portal" ?></span> <br>
                        <span class="<?php echo $header_name_class ?>"><?php echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;",$community->name ?></span>
                    </a>
                <?php endif ?>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div>
                <ul class="nav navbar-nav">
                    <!-- Home -->
                <?php
                    // put community 73's About menu in the db
                    if ($community->id == 73):
                        echo $component->text1;
                    else:
                ?>
                      <?php /* Manu added if */ if (isset($_SESSION['user'])) { ?>

                    <li class="<?php if ($tab == 0) echo 'active' ?> dropdown">
                        <a href="javascript:void(0)" class="dropdown-toggle joyride-about" data-toggle="dropdown">About</a>
                        <ul class="dropdown-menu">
                            <?php
                                if($community->about_home_view){
                                    $class_active = ($tab == 0 && $hl_sub == 0) ? 'class="active"' : '';
                                    echo '<li ' . $class_active . '><a href="/' . $community->portalName . '">Home</a></li>';
                                }
                            ?>
                            <?php
                            $pages = $components['page'];
                            foreach ($pages as $pag) {
                                if ($pag->disabled == 1) {
                                    if (isset($_SESSION['user']) && $_SESSION['user']->levels[$community->id] > 1) {
                                        if ($tab == 0 && $hl_sub == $pag->position + 2)
                                            echo '<li class="active"><a href="/' . $community->portalName . '/about/' . $pag->text2 . '"><i class="fa fa-eye-slash"></i> ' . $pag->text1 . '</a></li>';
                                        else
                                            echo '<li style="background:#e8e8e8"><a href="/' . $community->portalName . '/about/' . $pag->text2 . '"><i class="fa fa-eye-slash"></i> ' . $pag->text1 . '</a></li>';
                                    } else continue;
                                } else {
                                    if ($tab == 0 && $hl_sub == $pag->position + 2)
                                        echo '<li class="active"><a href="/' . $community->portalName . '/about/' . $pag->text2 . '">' . $pag->text1 . '</a></li>';
                                    else
                                        echo '<li><a href="/' . $community->portalName . '/about/' . $pag->text2 . '">' . $pag->text1 . '</a></li>';
                                }
                            }

                            if (isset($_SESSION['user']) && $_SESSION['user']->levels[$community->id] > 1) { /* Manu commented?>
                            <li style="background:#e8e8e8" <?php if ($tab == 0 && $hl_sub == -5) echo 'class="active"' ?>><a
                                    href="/<?php echo $community->portalName ?>/about/search"><i class="fa fa-eye-slash"></i> Search Articles</a></li>
                            <?php 
                            <li style="background:#e8e8e8" <?php if ($tab == 0 && $hl_sub == -6) echo 'class="active"' ?>><a
                                    href="/<?php echo $community->portalName ?>/about/registry"><i class="fa fa-eye-slash"></i> <?php echo $community->shortName?> Registry</a></li>
                            */ ?>
                            <?php } ?>
                            <?php
                                if($community->about_sources_view){
                                    $class_active = ($tab == 0 && $hl_sub == 1) ? 'class="active"' : '';
                                    echo '<li ' . $class_active . '><a href="/' . $community->portalName . '/about/sources">' . $community->shortName . ' Sources</a></li>';
                                }
                            ?>
                            <li <?php if ($tab == 0 && $hl_sub == -4) echo 'class="active"' ?>><a
				    href="/<?php echo $community->portalName ?>/about/resource">Add a Resource</a></li>
                           <li <?php if ($tab == 0 && $hl_sub == -4) echo 'class="active"' ?>><a
                                    href="/<?php echo $community->portalName ?>/resources?status=curated">Browse Resources</a></li>
                            <?php if($community->labEnabled()): ?>
                                <!-- <li <?php if ($tab == 0 && $hl_sub == -8) echo 'class="active"' ?>><a
                                    href="/<?php echo $community->portalName ?>/community-labs/main">Labs</a></li> -->
			    <?php endif ?>
                              <li <?php if ($tab == 0 && $hl_sub == -5) echo 'class="active"' ?>><a
                                    href="https://opuntia.online/entry"><i class="active"></i>License Compatibility Checker</a></li>
                        </ul>
		    </li>
                    <?php /* Manu end if*/ } ?>
                    <?php endif; ?>

                    <?php if ($community->resourceView) { ?>
                        <li class="<?php if ($tab == 1) echo 'active' ?> dropdown">
                            <?php
                            $newVars = $vars;
                            $newVars['category'] = 'Any';
                            $newVars['subcategory'] = null;
                            $newVars['nif'] = null;
                            $newVars['uuid'] = false;
                            $newVars['facet'] = null;
                            $newVars['filter'] = null;
                            $newVars['parent'] = null;
                            $newVars['child'] = null;
                            $newVars['page'] = 1;
                            $newVars["type"] = NULL;
                            ?>
                            <?php if($community->id == 72): ?>
                                <?php
                                    $keys = array_keys($community->urlTree);
                                    $newVars['category'] = $keys[0];
                                ?>
                                <a href="<?php echo $search->generateURL($newVars) ?>" class="joyride-community-resources">Resources</a>
                            <?php else: ?>
                                <a href="<?php echo $search->generateURL($newVars) ?>" class="joyride-community-resources"><?php echo Community::getSearchNameCommResources($community) ?></a>
                                <ul class="dropdown-menu">
                                    <?php
                                    $number = 0;
                                    foreach ($community->urlTree as $category => $array) {
                                        if ($tab==1 && $number == $hl_sub) {
                                            $active = ' active';
                                        } else {
                                            $active = '';
                                        }
                                        if ($array['subcategories'] && count($array['subcategories']) > 0)
                                            echo '<li class="dropdown-submenu' . $active . '">';
                                        else
                                            echo '<li class="' . $active . '">';
                                        $newVars = $vars;
                                        $newVars['category'] = $category;
                                        $newVars['subcategory'] = null;
                                        $newVars['nif'] = null;
                                        $newVars['uuid'] = false;
                                        $newVars['facet'] = null;
                                        $newVars['filter'] = null;
                                        $newVars['parent'] = null;
                                        $newVars['child'] = null;
                                        $newVars['page'] = 1;
                                        $newVars["type"] = NULL;
                                        echo '<a href="'.$search->generateURL($newVars).'">' . $category . '</a>';
                                        if ($array['subcategories'] && count($array['subcategories']) > 0) {
                                            echo '<ul class="dropdown-menu">';
                                            $nextNum = 0;
                                            foreach ($array['subcategories'] as $subcategory => $urls) {
                                                $newVars = $vars;
                                                $newVars['category'] = $category;
                                                $newVars['subcategory'] = $subcategory;
                                                $newVars['nif'] = null;
                                                $newVars['uuid'] = false;
                                                $newVars['facet'] = null;
                                                $newVars['filter'] = null;
                                                $newVars['parent'] = null;
                                                $newVars['child'] = null;
                                                $newVars['page'] = 1;
                                                $newVars["type"] = NULL;
                                                if ($tab==1 && $number == $hl_sub&&$nextNum == $ol_sub)
                                                    echo '<li class="active"><a href="' . $search->generateURL($newVars) .'">' . $subcategory . '</a></li>';
                                                else
                                                    echo '<li><a href="' . $search->generateURL($newVars) .'">' . $subcategory . '</a></li>';
                                                $nextNum++;
                                            }
                                            echo '</ul>';
                                        }
                                        $number++;
                                        echo '</li>';
                                    }

                                    ?>
                                </ul>
                            <?php endif ?>
                        </li>
                    <?php } ?>
                    <!-- End Home -->

                    <!-- Job Pages -->
                    <?php if ($community->dataView) { ?>
                        <li class="<?php if ($tab == 2) echo 'active' ?>">
                            <?php
                            $newVars = $vars;
                            $newVars['category'] = 'data';
                            $newVars['subcategory'] = null;
                            $newVars['nif'] = null;
                            $newVars['uuid'] = false;
                            $newVars['facet'] = null;
                            $newVars['filter'] = null;
                            $newVars['page']=1;
                            ?>
                            <?php if($community->id == 72): ?>
                                <a href="<?php echo $search->generateURL($newVars) ?>" class="joyride-more-resources">Data</a>
                            <?php else: ?>
                                <a href="<?php echo $search->generateURL($newVars) ?>" class="joyride-more-resources"><?php echo Community::getSearchNameMoreResources($community) ?></a>
                            <?php endif ?>
                        </li>
                    <?php } ?>
                    <!-- End Job Pages -->


                    <?php if ($community->literatureView) { ?>
                        <li class="<?php if ($tab == 3) echo 'active' ?>">
                            <?php
                            $newVars = $vars;
                            $newVars['category'] = 'literature';
                            $newVars['subcategory'] = null;
                            $newVars['nif'] = null;
                            $newVars['uuid'] = false;
                            $newVars['facet'] = null;
                            $newVars['filter'] = null;
                            $newVars['parent'] = null;
                            $newVars['child'] = null;
                            $newVars['page'] = 1;
                            $newVars["type"] = NULL;
                            ?>
                            <a href="<?php echo $search->generateURL($newVars) ?>" class="joyride-literature"><?php echo Community::getSearchNameLiterature($community) ?></a>
                        </li>
                    <?php } ?>

                    <?php if (isset($_SESSION['user'])) { ?>
                        <li class="<?php if ($tab == 4) echo 'active' ?> dropdown">
                            <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown">My Account <?php if($n_notifs > 0) echo \helper\htmlElement("notification-bubble", Array("text" => $n_notifs)) ?></a>
                            <ul class="dropdown-menu">
                                <li<?php if ($hl_sub == 0 && $tab==4) echo ' class="active"' ?>>
                                    <a href="/<?php echo $community->portalName?>/account">
                                        <i class="fa fa-bar-chart-o"></i>
                                        Information <?php if($n_notifs_conversations > 0) echo \helper\htmlElement("notification-inline", Array("text" => $n_notifs_conversations)); ?>
                                    </a>
                                </li>
                                <li<?php if ($hl_sub == 1 && $tab == 4) echo ' class="active"' ?>>
                                    <a href="/<?php echo $community->portalName ?>/account/communities">
                                        <i class="fa fa-group"></i>
                                        Communities
                                    </a>
                                </li>
                                <li<?php if ($hl_sub == 2 && $tab == 4) echo ' class="active"' ?>>
                                    <a href="/<?php echo $community->portalName ?>/account/resources">
                                        <i class="fa fa-cubes"></i>
                                        Resources <?php if($n_notifs_mentions > 0) echo \helper\htmlElement("notification-inline", Array("text" => $n_notifs_mentions)); ?>
                                    </a>
                                </li>
                                <li<?php if ($hl_sub == 3 && $tab==4) echo ' class="active"' ?>>
                                    <a href="/<?php echo $community->portalName?>/account/saved">
                                        <i class="fa fa-floppy-o"></i>
                                        Saved Searches
                                        <?php if($n_saved_searches > 0) echo \helper\htmlElement("notification-inline", Array("text" => $n_saved_searches, "type" => "default")) ?>
                                        <?php if($n_notifs_searches > 0) echo \helper\htmlElement("notification-inline", Array("text" => $n_notifs_searches)); ?>
                                    </a>
                                </li>
                                <li<?php if ($hl_sub == 6 && $tab==4) echo ' class="active"' ?>>
                                    <a href="/<?php echo $community->portalName?>/account/collections">
                                        <i class="fa fa-shopping-cart"></i>
                                        My Collections
                                    </a>
                                </li>
                                <?php if ($community->portalName == 'dknet' || $community->portalName == 'dknetbeta'): ?>
                                    <li<?php if ($hl_sub == 9 && $tab == 4) echo ' class="active"' ?>>
                                        <a href="/<?php echo $community->portalName ?>/account/rrid-report">
                                            <i class="fa fa-repeat"></i>
                                            Authentication Reports <?php if($n_notifs_rrid_reports > 0) echo \helper\htmlElement("notification-inline", Array("text" => $n_notifs_rrid_reports)); ?>
                                        </a>
                                    </li>
                                <?php endif ?>
                                <!-- Remove API Keys Section - Steven -->
                                <?php if($_SESSION['user']->levels[$community->id] > 1): ?>
                                    <li<?php if ($hl_sub == 10 && $tab==4) echo ' class="active"' ?>>
                                        <a href="/<?php echo $community->portalName?>/account/communities/<?php echo $community->portalName?>">
                                            <i class="fa fa-cogs"></i>
                                            Manage Community
                                        </a>
                                    </li>
                                <?php endif ?>
                            </ul>
                        </li>
                    <?php } ?>
                </ul>
            </div>
            <!--/navbar-collapse-->
        </div>
    </div>
    <!-- End Navbar -->

    <?php if ($vars['editmode']) {
        echo '<div class="body-overlay"><h3 style="margin-top:10px;margin-left:10px">' . $component->component_ids[$component->component] . '</h3>';
        echo '<div class="pull-right">';
        echo '<button class="btn-u btn-u simple-toggle" modal=".cont-select-container" title="Add About Page"><i class="fa fa-plus"></i><span class="button-text"> Add</span></button><button class="btn-u btn-u-default edit-body-btn" componentType="other" componentID="'.$component->id.'"><i class="fa fa-cogs"></i><span class="button-text"> Edit</span></button></div>';
        echo '</div>';
    } ?>
</div>
<!--=== End Header ===-->
<?php if($vars['editmode']){?>
    <div class="cont-select-container large-modal back-hide">
        <div class="close dark">X</div>
        <div class="selection">
            <h2 align="center">Select a Container to Add</h2>

            <div class="components-select">
                <?php
                echo $component->getContainerSelectHTML($community->id);
                ?>
            </div>
        </div>
    </div>
    <div class="container-add-load back-hide"></div>
<?php } ?>

<?php echo \helper\htmlElement("login-form", Array("errorID" => $errorID, "community" => $community)); ?>

<?php if (isset($_SESSION['user']) && ($_SESSION['user']->levels[$community->id] < 1)) { ?>

    <script type="text/javascript">
        jQuery(document).ready(function($) {

   <?php if (isset($_GET['from']) && ($_GET['from'] == 'joinModal')): ?>
            // if redirected from orcid ...
            $('#joinModal').modal('show');

    <?php endif; ?>

        });
    </script>
    <div class="modal fade" id="joinModal" tabindex="-3" role="form" aria-labelledby="joinModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="joinModalLabel">Join Community</h4>
          </div>
          <div class="modal-body">
                <form class="form-horizontal well sky-form" id="joinModalForm" action="/forms/login.php?join=true&cid=<?php echo $community->id ?>" method="post">
<?php
    if (strtolower(substr($community->shortName, 0, 3)) == "odc"):
        if (isset($_SESSION['user']->orcid_id)) {
            $user_extra_data = UsersExtraData::getRRIDWorksByUser($_SESSION['user']);
            $orcid = $_SESSION['user']->orcid_id;
        } elseif (isset($_SESSION['orcid'])) {
            $orcid = $_SESSION['orcid'];
        } else {
            $orcid = '';
        }

        ?>
                    <section>
                        <label class="label">ORCID</label>
                        <label class="input">
                            <i class="icon-append fa fa-question-circle"></i>
    <?php if (isset($_SESSION['orcid']) || isset($_SESSION['user']->orcid_id)): ?>
                            <input type="text" name="orcid_id" value="<?php echo $orcid; ?>" readonly >
    <?php else: ?>
                        <a href="https://orcid.org/oauth/authorize?client_id=<?php echo ORCID_CLIENT_ID ?>&response_type=code&scope=/authenticate&redirect_uri=<?php echo PROTOCOL ?>://<?php echo \helper\httpHost() ?>/forms/associate-orcid.php?cid=<?php echo $community->id . $_SERVER['REQUEST_URI']; ?>"><button class="btn btn-primary">Associate ORCID iD</button></a>
    <?php endif; ?>
                            <b class="tooltip tooltip-top-right">ORCID</b>
                        </label>

                    </section>
                    <?php
                        // show saved org; else use orcid org, else nothing
                        if (isset($_SESSION['user']->organization))
                            $org = $_SESSION['user']->organization;
                        elseif (isset($_SESSION['orcid']['organization']))
                            $org = $_SESSION['orcid']['organization'];
                        else
                            $org = "";
                    ?>

                    <section>
                        <label class="label">Organization <span class="color-red">*</span></label>
                        <label class="input">
                            <i class="icon-append fa fa-question-circle"></i>
                            <input type="text" name="organization" required="required" value="<?php echo $org; ?>">
                            <b class="tooltip tooltip-top-right">The organization you are affiliated with (ie UCSD, Scripps, etc)</b>
                        </label>
                    </section>
    <?php if ((isset($_SESSION['orcid']['id']) && sizeof($_SESSION['orcid']['works_count'])) || sizeof($user_extra_data)): ?>
                    <section>
                        <label class="label">Lab Website</label>
                        <label class="input">
                            <i class="icon-append fa fa-question-circle"></i>
                            <input type="text" name="website">
                            <b class="tooltip tooltip-top-right">Lab Website</b>
                        </label>
                    </section>
    <?php else: ?>
                    <section>
                        <label class="label">Lab Website <span class="color-red">*</span></label>
                        <label class="input">
                            <i class="icon-append fa fa-question-circle"></i>
                            <input type="text" name="website" required="required">
                            <b class="tooltip tooltip-top-right">Lab Website</b>
                        </label>
                    </section>
    <?php endif; ?>

<?php
            endif;
                // if api_key AND default list, use mailchimp join
                if ($community->mailchimp_api_key && $community->mailchimp_default_list):
            ?>
                <div style="text-align: left"><strong><?php echo $community->name; ?></strong> provides channels for you to stay up to date. <br />
                <input type="checkbox" value="1" id="mailchimp" name="mailchimp" checked /> Add me to the mailing list.
                </div>
            <?php endif; ?>
         <!--   </form> -->
          </div>

          <div class="modal-footer">
                <button class="btn btn-default" id="simple-post" type="submit">Join Community</button>

                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
            </form>

          </div>
        </div>
      </div>
    </div>
<?php } ?>

<div class="large-modal back-hide leave-comm">
    <div class="close dark less-right">X</div>
    <h2>Leaving Community</h2>
    <p style="margin:20px 0">
        Are you sure you want to leave this community? Leaving the community will revoke any permissions you have been
        granted in this community.
    </p>
    <div class="btn-u btn-u-default close-btn">No</div>
    <a class="btn-u btn-u-red" href="/forms/leave.php?cid=<?php echo $community->id?>">Yes</a>
</div>

<?php echo \helper\htmlElement("header-joyride-text"); ?>
