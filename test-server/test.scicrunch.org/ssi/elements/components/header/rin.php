<?php

$community = $data["community"];
$component = $data["component"];
$vars = $data["vars"];
$tab = $data["tab"];
$hl_sub = $data["hl_sub"];
$ol_sub = $data["ol_sub"];

$holder = new Component();
$components = $holder->getByCommunity($community->id);

?>

<link rel="stylesheet" href="/css/rin.css">
<div class="rin header <?php if($vars['editmode']) echo 'editmode' ?>" <?php echo 'style="z-index:99"' ?>>
    <!-- topbar -->
    <div class="header-v1">
        <div class="topbar-v1" <?php if($vars['editmode']) echo 'style="position:fixed;top:0;left:0;z-index:110;width:100%"' ?>>
            <div class="container">
                <div class="row">
                    <div class="col-md-6">
                    </div>
                    <div class="col-md-6">
                        <ul class="list-unstyled top-v1-data">
                            <?php include $_SERVER['DOCUMENT_ROOT'] . '/components/header/topbar.php';  ?>
                            <li><i class="search fa fa-info-circle tutorial-header-btn" style="cursor:pointer"></i></li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- /topbar -->

    <div class="navbar navbar-default" role="navigation">
        <div class="container-fluid">
            <div class="navbar-header">
                <a class="navbar-brand">
                    <img alt="<?php echo $community->shortName ?>" src="/upload/community-logo/<?php echo $community->logo ?>" />
                </a>
                <ul class="nav navbar-nav">
                    <li class="<?php if($tab === "about") echo 'active' ?> dropdown">
                        <a href="javascript:void(0)" class="dropdown-toggle joyride-about" data-toggle="dropdown">About</a>
                        <ul class="dropdown-menu">
                            <?php
                            if($community->about_home_view){
                                $class_active = ($tab == 0 && $hl_sub == 0) ? 'class="active"' : '';
                                echo '<li ' . $class_active . '><a href="/' . $community->portalName . '">Home</a></li>';
                            }
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
                            if (isset($_SESSION['user']) && $_SESSION['user']->levels[$community->id] > 1) {?>
                            <li style="background:#e8e8e8" <?php if ($tab == 0 && $hl_sub == -5) echo 'class="active"' ?>><a
                                    href="/<?php echo $community->portalName ?>/about/search"><i class="fa fa-eye-slash"></i> Search Articles</a></li>
                            <?php } ?>
                            <?php
                                if($community->about_sources_view){
                                    $class_active = ($tab == 0 && $hl_sub == 1) ? 'class="active"' : '';
                                    echo '<li ' . $class_active . '><a href="/' . $community->portalName . '/about/sources">' . $community->shortName . ' Sources</a></li>';
                                }
                            ?>
                            <?php //if($community->portalName != "dknet"): ##showed "Add a Resource" page?>
                                <li <?php if ($tab == 0 && $hl_sub == -4) echo 'class="active"' ?>><a
                                        href="/<?php echo $community->portalName ?>/about/resource">Add a Resource</a></li>
                            <?php //endif ?>
                            <?php if($community->labEnabled()): ?>
                                <li <?php if ($tab == 0 && $hl_sub == -8) echo 'class="active"' ?>><a
                                    href="/<?php echo $community->portalName ?>/community-labs/main">Labs</a></li>
                            <?php endif ?>
                        </ul>
                    </li>
                    <li <?php if($tab === "resource-reports") echo 'class="active"' ?>>
                        <a href="<?php echo $community->fullURL() ?>/rin/rrids">Resource Reports</a>
                    </li>
                    <li <?php if($tab === "discovery-portal") echo 'class="active"' ?>>
                        <a href="<?php echo $community->fullURL() ?>/data/search">Discovery Portal</a>
                    </li>
                    <li <?php if($tab === "rigor-reproducibility") echo 'class="active"' ?>>
                        <a href="<?php echo $community->fullURL() ?>/rin/rigor-reproducibility-about">Rigor/Reproducibility</a>
                    </li>
                    <li <?php if($tab === "hypothesis-center") echo 'class="active"' ?>>
                        <a href="<?php //echo $community->fullURL() ?>/about/hypothesis_center">Hypothesis Center</a>
                    </li>
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
        </div>
    </div>

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
