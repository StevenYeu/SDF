<?php
$holder = new Component();
$components = $holder->getByCommunity($community->id);
$header_img_class = "community-logo";
$header_name_class = "community-name";
if(isset($components['header']) && $components['header'][0]->icon1 && $components['header'][0]->icon1 == "large"){
    $header_img_class = "community-logo-large";
    $header_name_class = "community-name-small";
}

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

?>

<style>
    <?php if($component->color1){?>
    .header-v1 .navbar-default .navbar-nav > li > a:hover, .header-v1 .navbar-default .navbar-nav > .active > a,.header-v1 .navbar-default .navbar-nav > li:hover > a {
        color: #fff;
        background: <?php echo '#'.$component->color1?> !important;
    }
    .header .dropdown-menu {
        border-top: solid 2px <?php echo '#'.$component->color1?>;
    }
    .topbar-link:hover {
        color:<?php echo '#'.$component->color1?>;
    }
    .header .navbar-default .navbar-nav > li > a:hover, .header .navbar-default .navbar-nav > .active > a {
        border-bottom: solid 2px <?php echo '#'.$component->color1?>;
    }
    .header-v1 .navbar .nav > li > .search:hover {
        background: <?php echo '#'.$component->color1?>;
    }
    .header .navbar .nav > li > .search:hover {
        color: #fff;
        border-bottom-color: <?php echo '#'.$component->color1?>;
    }
    <?php } ?>
    .header-v1 .navbar-default .navbar-nav > li > a {
        padding: 12px 30px 9px 20px;
    }
    .header-v1 .navbar .nav > li > .search {
        padding: 12px 10px;
    }
    .header-v1 .dropdown > a:after {
        top:13px;
    }
</style>
<div class="header header-v1 <?php if($vars['editmode']) echo 'editmode' ?>" <?php echo 'style="z-index:99"' ?>>
    <!-- Topbar -->
    <div class="topbar-v1 margin-bottom-20" <?php if($vars['editmode']) echo 'style="position:fixed;top:0;left:0;z-index:110;width:100%"' ?>>
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                </div>

                <div class="col-md-6">
                    <ul class="list-unstyled top-v1-data">
                        <?php include $_SERVER['DOCUMENT_ROOT'] . '/components/header/topbar.php';  ?>
                        <!-- <li><i class="search fa fa-info-circle tutorial-header-btn" style="cursor:pointer"></i></li> -->
                        <li><a target="_blank" href="https://dknet.org/about/help"><i class="search fa fa-info-circle" style="cursor:pointer"></i></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <!-- End Topbar -->

    <!-- Navbar -->
    <div class="navbar navbar-default" role="navigation" style="">
        <div class="container">
            <!-- Brand and toggle get grouped for better mobile display -->
            <div class="navbar-header" style="padding:10px 0;">
                <a class="navbar-brand" href="/<?php echo $community->portalName ?>">
                    <img class="<?php echo $header_img_class ?>" src="/upload/community-logo/<?php echo $community->logo ?>"/>
                    <?php if(!$community->rinStyle()): ?>
                        <span class="<?php echo $header_name_class ?>"><?php echo $community->name ?></span>
                    <?php endif ?>
                </a>
            </div>

            <!-- Collect the nav links, forms, and other content for toggling -->
            <div>
                <ul class="nav navbar-nav">
                    <!-- Home -->
                    <li class="<?php if ($tab === 0) echo 'active' ?> dropdown">
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

                            ?>
                            <?php
                            if (isset($_SESSION['user']) && $_SESSION['user']->levels[$community->id] > 1) {?>
                            <li style="background:#e8e8e8" <?php if ($tab == 0 && $hl_sub == -5) echo 'class="active"' ?>><a
                                    href="/<?php echo $community->portalName ?>/about/search"><i class="fa fa-eye-slash"></i> Search Articles</a></li>
                            <?php /*
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
                            <?php if(!$community->rinStyle()): ?>
                                <li <?php if ($tab == 0 && $hl_sub == -4) echo 'class="active"' ?>><a
                                        href="/<?php echo $community->portalName ?>/about/resource">Add a Resource</a></li>
                            <?php endif ?>
                            <?php if($community->labEnabled()): ?>
                                <li <?php if ($tab == 0 && $hl_sub == -8) echo 'class="active"' ?>><a
                                    href="/<?php echo $community->portalName ?>/community-labs/main">Labs</a></li>
                            <?php endif ?>
                        </ul>
                    </li>
                    <?php if($community->rinStyle()): ?>
                        <li <?php if($tab === "resource-reports") echo 'class="active"' ?>>
                            <a href="<?php echo $community->fullURL() ?>/rin/rrids"
                              title="A dkNET resource report offers a detailed overview of each resource, citation metrics from biomedical literature, information about what resources have been used together and more.">
                              Resource Reports
                            </a>
                        </li>
                        <li <?php if($tab === "discovery-portal") echo 'class="active"' ?>>
                            <a href="<?php echo $community->fullURL() ?>/data/search"
                              title="The dkNET Discovery Portal connects researchers directly to more than 300 biomedical databases and millions of resources. Researchers can also explore community resources that are highly relevant to the disease fields in NIDDK's mission.">
                              Discovery Portal
                            </a>
                        </li>
                        <li <?php if($tab === "rigor-reproducibility") echo 'class="active"' ?>>
                            <a href="<?php echo $community->fullURL() ?>/rin/rigor-reproducibility-about"
                              title="View resources on how to comply with NIH's new policies on authentication of key biological resources, using our reproducibility reports, and making data FAIR.">
                              Authentication Report
                            </a>
                        </li>
                        <li <?php if($tab === "hypothesis-center") echo 'class="active"' ?>>
                            <a href="<?php echo $community->fullURL() ?>/about/hypothesis_center"
                              title="Analyze diverse ‘omics data to generate or test research hypotheses – powered by the Signaling Pathways Project (SPP).">
                              Hypothesis Center
                            </a>
                        </li>
                    <?php else: ?>
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
                            </li>
                        <?php } ?>
                        <!-- End Home -->

                        <!-- Job Pages -->

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
                                <a href="<?php echo $search->generateURL($newVars) ?>" class="joyride-more-resources"><?php echo Community::getSearchNameMoreResources($community) ?></a>
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
                                $newVars['page']=1;
                                $newVars['facet'] = null;
                                $newVars['filter'] = null;
                                $newVars['parent'] = null;
                                $newVars['child'] = null;
                                $newVars["type"] = NULL;
                                ?>
                                <a href="<?php echo $search->generateURL($newVars) ?>" class="joyride-literature"><?php echo Community::getSearchNameLiterature($community) ?></a>
                            </li>
                        <?php } ?>
                    <?php endif ?>

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
                                <li<?php if ($hl_sub == 3&&$tab==4) echo ' class="active"' ?>>
                                    <a href="/<?php echo $community->portalName?>/account/saved">
                                        <i class="fa fa-floppy-o"></i>
                                        Saved Searches
                                        <?php if($n_saved_searches > 0) echo \helper\htmlElement("notification-inline", Array("text" => $n_saved_searches, "type" => "default")) ?>
                                        <?php if($n_notifs_searches > 0) echo \helper\htmlElement("notification-inline", Array("text" => $n_notifs_searches)); ?>
                                    </a>
                                </li>
                                <li<?php if ($hl_sub == 6&&$tab==4) echo ' class="active"' ?>>
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
                                <li<?php if ($hl_sub == 7 && $tab == 4) echo ' class="active"' ?>>
                                    <a href="/<?php echo $community->portalName ?>/account/developer">
                                        <i class="fa fa-key"></i>
                                        API Keys
                                    </a>
                                </li>
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
        echo '<div class="body-overlay"><h3>' . $component->component_ids[$component->component] . '</h3>';
        echo '<div class="pull-right">';
        echo '<button class="btn-u btn-u simple-toggle" modal=".cont-select-container" title="Add About Page"><i class="fa fa-plus"></i><span class="button-text"> Add</span></button><button class="btn-u btn-u-default edit-body-btn" componentType="other" componentID="'.$component->id.'"><i class="fa fa-cogs"></i><span class="button-text"> Edit</span></button></div>';
        echo '</div>';
    } ?>
</div>
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

<?php if ($_SESSION['user']->levels[$community->id] < 1) { ?>
    <div class="modal fade" id="joinModal" tabindex="-3" role="dialog" aria-labelledby="joinModalLabel">
      <div class="modal-dialog" role="document">
        <div class="modal-content">
          <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title" id="joinModalLabel">Join Community</h4>
          </div>
          <div class="modal-footer">
                <form class="form-horizontal well" data-async data-target="#join-modal" action="/forms/login.php?join=true&cid=<?php echo $community->id ?>" method="POST">
            <?php
                // if no api_key or no default list, use simple join
                if (!(is_null($community->mailchimp_api_key) || is_null($community->mailchimp_default_list))):
            ?>
                <div style="text-align: left"><strong><?php echo $community->name; ?></strong> provides channels for you to stay up to date. <br />
                <input type="checkbox" value="1" id="mailchimp" name="mailchimp" checked /> Add me to the mailing list.
                </div>
            <?php endif; ?>

                <button class="btn btn-default" id="simple-post">Join Community</button>

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
