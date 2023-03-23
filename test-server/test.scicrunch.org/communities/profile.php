<?php
$tab = 4;
include 'vars/overview.php';

$allowed_pages = Array("rrid-report");

error_reporting(0);
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

if(!$community) {
    $community = new Community();
    $community->getByID(0);  
}
if($community->id == 0) {
    $tab = 3;
}

$thisCommunity = $community;

$update = $vars['update'];
$currPage = $vars['currPage'];
$query = $vars['query'];
$page = $vars['page'];
$arg1 = $vars['arg1'];
$arg2 = $vars['arg2'];
$arg3 = $vars['arg3'];
$arg4 = $vars['arg4'];
$section = $vars['tab'];
$tutorial = $vars['tutorial'];

if (!isset($_SESSION['user']) && !in_array($page, $allowed_pages)) {
    header('location:/');
    exit();
}

$search = new Search();
$search->community = $thisCommunity;


if ($page) {
    switch ($page) {
        case "edit":
            $hl_sub = 0;
            $pageTitle = 'Edit Information';
            break;
        case "messages":
            $hl_sub = 0;
            $pageTitle = 'Messages';
            break;
        case "communities":
            $hl_sub = 1;
            $pageTitle = 'My Communities';
            if($arg1){
                $hl_sub = 10;
                $community = new Community();
                $community->getByPortalName($arg1);
                $user_access = $community->getUser($_SESSION['user']->id);
                if(count($user_access) == 0 || $user_access[0]['level'] < 2){
                    header("location:/" . $vars['portalName'] . "/account");
                    exit;
            }
            }
            break;
        case "scicrunch":
            if ($_SESSION['user']->role < 1) {
                header('location:/account');
                exit();
            }
            $hl_sub = 4;
            $pageTitle = 'Edit SciCrunch';
            break;
        case "resources":
            $hl_sub = 2;
	    $pageTitle = 'My Resources';
            break;
        case "saved":
            $hl_sub = 3;
            $pageTitle = 'My Saved Searches';
            break;
        case "collections":
            $hl_sub = 5;
            $pageTitle = 'My Collections';
            break;
        case "rrid-report":
            $hl_sub = 9;
            $pageTitle = "Authentication report";
            break;
        case "uptime":
            $hl_sub = 6;
            $pageTitle = 'Uptime Dashboard';
            break;
        // case "developer":
        //     $hl_sub = 7;
        //     $pageTitle = "API Keys";
        //     break; 
        // Removed - Steven 
        case "curator":
            $hl_sub = 8;
            $pageTitle = "Curator";
            break;
        case "labs":
            $hl_sub = 10;
            $pageTitle = "Labs";
            break;
        case "notifications":
            $hl_sub = 11;
            $pageTitle = "My Notifications";
            break;
        case "foundry-dashboard":
            $hl_sub = 12;
			switch ($arg1) {
				case "All_Resources":
					$pageTitle = "Foundry Dashboard | All Resources View";
					break;
				case "Resource":
					$pageTitle = "Foundry Dashboard | Resource Page";
					break;
				case "Resource_Log_Info":
					$pageTitle = "Foundry Dashboard | Resource Logs Page";
					break;
				default:
					$pageTitle = "Foundry Dashboard";
			}
            break;
    }
} else {
    $pageTitle = 'My Account';
    $hl_sub = 0;
}
$community = $thisCommunity;
$components = $community->components;
$component = $components['breadcrumbs'][0];
?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<head>
    <title><?php echo $thisCommunity->portalName ?> | <?php echo $pageTitle ?></title>

    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo \helper\metaDescription($community->description) ?>">
    <meta name="author" content="">
    <meta name="google-site-verification" content="vhe7FXQ5uQHNwM10raiS4rO23GgbFW6-iyRfapxGPJc" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="/favicon.ico">

    <!-- CSS Global Compulsory -->
    <link rel="stylesheet" href="/assets/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="/assets/plugins/line-icons/line-icons.css">
    <link rel="stylesheet" href="/assets/plugins/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/plugins/flexslider/flexslider.css">
    <link rel="stylesheet" href="/assets/plugins/parallax-slider/css/parallax-slider.css">

    <!-- CSS Theme -->
    <link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">
    <link rel="stylesheet" href="/assets/css/pages/page_log_reg_v2.css">
    <link rel="stylesheet" href="/assets/plugins/scrollbar/src/perfect-scrollbar.css">
    <link rel="stylesheet" href="/assets/plugins/sky-forms/version-2.0.1/css/custom-sky-forms.css">
    <!--[if lt IE 9]>
        <link rel="stylesheet" href="assets/plugins/sky-forms/version-2.0.1/css/sky-forms-ie8.css">-->

    <!-- CSS Page Style -->
    <link rel="stylesheet" href="/assets/css/pages/profile.css">

    <!-- CSS Theme -->
    <link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">

    <!-- CSS Customization -->
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/community-search.css">
    <link rel="stylesheet" href="/css/joyride-2.0.3.css">
    <link rel="stylesheet" href="/assets/plugins/summernote/summernote.css"/>
    <style>
        <?php if($component->color3){?>
        .breadcrumbs-v3 {
            background: <?php echo '#'. $component->color3?>;
        }

        <?php } elseif($component->image){ ?>
        .breadcrumbs-v3 {
            background: url('/upload/community-components/<?php echo $component->image?>') 100% 100% no-repeat;
        }

        <?php } ?>
        <?php if($component->color1){?>
        .breadcrumbs-v3 h1, .breadcrumbs-v3 .breadcrumb li a {
            color: <?php echo '#'. $component->color1?>;
        }

        <?php } ?>
        <?php if($component->color2){?>
        .breadcrumbs-v3 .breadcrumb li a:hover, .breadcrumbs-v3 .breadcrumb li.active {
            color: <?php echo '#'. $component->color2?>;
        }

        <?php } ?>
    </style>

    <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
</head>

<body>
<?php echo \helper\topPageHTML(); ?>
<input type="hidden" id="portal-name" value="<?php echo $community->portalName ?>" />
<input type="hidden" id="community-id" value="<?php echo $community->id ?>" />
<div class="wrapper" <?php if ($vars['editmode']) echo 'style="margin-top:32px;"' ?>>
    <!--=== Header ===-->
    <?php
    echo \helper\htmlElement("components/header", Array(
        "community" => $community,
        "component" => $components["header"][0],
        "vars" => $vars,
        "tab" => $tab,
        "hl_sub" => $hl_sub,
        "ol_sub" => $ol_sub,
    ));
    ?>

    <?php
    $profileBase = '/' . $thisCommunity->portalName . '/';
    if ($page) {
        switch ($page) {
            case 'edit':
                $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/edit.php';
                break;
            case 'messages':
                $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/messages.php';
                break;
            case 'communities':
                if ($arg1) {
                    switch ($arg2) {
                        case 'resource':
                            $arg2 = $arg3;
                            $include_file = 'profile/resources/resource-edit.php';
                            break;
                        case 'insert':
                            $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/component-pages/dynamic-data-add.php';
                            break;
                        case 'update':
                            $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/component-pages/dynamic-data-update.php';
                            break;
                        case 'dynamic':
                            $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/component-pages/dynamic-data.php';
                            break;
                        case 'type':
                            switch ($arg3) {
                                case 'add':
                                    $type = new Resource_Type();
                                    $type->getByID($arg4);
                                    $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/resource-pages/type-add.php';
                                    break;
                                case 'edit':
                                    $type = new Resource_Type();
                                    $type->getByID($arg4);
                                    $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/resource-pages/type-edit.php';
                            }
                            break;
                        case 'component':
                            if ($arg3 == 'insert') {
                                $arg3 = $arg4;
                                $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/component-pages/component-insert.php';
                            } elseif ($arg3 == 'update') {
                                $arg3 = $arg4;
                                $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/component-pages/component-update.php';
                            } elseif ($arg3 == 'files') {
                                $arg3 = $arg4;
                                $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/component-pages/component-files.php';
                            }
                            break;
                        case 'form':
                            $type = new Resource_Type();
                            $type->getByID($arg4);
                            $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/resource-pages/form-edit.php';
                            break;
                        case 'components':
                            if ($arg3)
                                $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/component-pages/components-' . $arg3 . '.php';
                            else
                                $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/component-pages/components-page.php';
                            break;
                        case 'edit':
                            $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/communities/community-update.php';
                            break;
                        case 'sources':
                            $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/communities/community-sources.php';
                            break;
                        case "pending-user-requests":
                            $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/communities/pending-user-requests.php';
                            break;
                        case 'view':
                            $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/component-pages/data-view.php';
                            break;
                        default:
                            $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/communities/community-single-page.php';
                    }
                } else
                    $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/communities/community-page.php';
                break;
            case 'resources':
		if ($arg1) {
                    if ($arg1 == 'edit')
                        $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/resources/resource-edit.php';
                } else{
			    $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/resources/resources.php';
               //    $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/other-pages/save-search-overview.php';
		}
                break;
            case 'uptime':
                $include_file = 'uptime/uptime-dashboard.php';
                break;
            case 'developer':
                $include_file = 'profile/developer/developer.php';
                break;
            case "curator":
                $include_file = "profile/curator/curator.php";
                break;
            case "labs":
                $include_file = "profile/other-pages/labs.php";
                break;
            case 'scicrunch':
                $community = new Community();
                $community->id = 0;
                $community->name = 'SciCrunch';
                $community->portalName = 'scicrunch';
                if ($arg1) {
                    switch ($arg1) {
                        case 'users':
                            $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/scicrunch/users.php';
                            break;
                        case 'sources':
                            $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/scicrunch/updateSources.php';
                            break;
                        case 'component':
                            if ($arg2 == 'insert') {
                                $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/component-pages/component-insert.php';
                            } elseif ($arg2 == 'update') {
                                $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/component-pages/component-update.php';
                            } elseif ($arg2 == 'files') {
                                $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/component-pages/component-files.php';
                            }
                            break;
                        case 'type':
                            $type = new Resource_Type();
                            $type->getByID($arg3);
                            if ($arg2 == 'edit')
                                $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/resource-pages/type-edit.php';
                            elseif ($arg2 == 'add')
                                $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/resource-pages/type-add.php';
                            break;
                        case 'form':
                            $type = new Resource_Type();
                            $type->getByID($arg3);
                            if ($arg2 == 'edit')
                                $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/resource-pages/form-edit.php';
                            break;
                        case 'components':
                            if ($arg2)
                                $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/component-pages/components-' . $arg2 . '.php';
                            else
                                $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/component-pages/components-page.php';
                            break;
                        case 'dynamic':
                            $arg3 = $arg2;
                            $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/component-pages/dynamic-data.php';
                            break;
                        case 'view':
                            $arg3 = $arg2;
                            $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/shared-pages/component-pages/data-view.php';
                            break;
                    }
                } else
                    $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/scicrunch/home.php';
                break;
            case "saved":
                $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/other-pages/save-search-overview.php';
                break;
            case "notifications":
                $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/other-pages/notifications-overview.php';
                break;
            case 'foundry-dashboard':
                $include_file = 'foundry-dashboard/foundry-dashboard.php';
                break;
            case "collections":
                if (isset($arg1) && $arg1 != '' && $arg1 != null)
                    $include_file = 'profile/other-pages/collection-view.php';
                else
                    $include_file = 'profile/other-pages/collections-overview.php';
                break;
            case "rrid-report":
                if(!$arg1) {
                    $include_file = 'profile/rrid-report/overview.php';
                } elseif(!$arg2) {
                    $include_file = 'profile/rrid-report/single-report.php';
                } elseif(!$arg3) {
                    if($arg2 == "preview") {
                        $include_file = "profile/rrid-report/preview.php";
                    } elseif($arg2 == "snapshot") {
                        $include_file = "profile/rrid-report/snapshot.php";
                    } else {
                        $include_file = "profile/rrid-report/single-report-item.php";
                    }
                }
                break;
        }
    } else
        $include_file = $_SERVER['DOCUMENT_ROOT'] . '/profile/home.php';

    include $include_file;
    ?>
    <?php
    if (!isset($vars['stripped']) || $vars['stripped'] != 'true') {
        if (count($components['footer']) == 1) {
            $component = $components['footer'][0];
            if ($component->component == 92)
                include $_SERVER['DOCUMENT_ROOT'] . '/components/footer/footer-normal.php';
            elseif ($component->component == 91)
                include $_SERVER['DOCUMENT_ROOT'] . '/components/footer/footer-light.php';
            elseif ($component->component == 90)
                include $_SERVER['DOCUMENT_ROOT'] . '/components/footer/footer-dark.php';
        } else
            include $_SERVER['DOCUMENT_ROOT'] . '/components/footer/footer-normal.php';
    } else  {echo '<div style="background:#fff;height:20px"></div>';}
    ?>
    <div class="background"></div>
    <div class="component-add-load back-hide"></div>
    <div class="component-delete back-hide">
        <div class="close dark">X</div>
        <form method="post"
              id="component-delete-form" class="sky-form" enctype="multipart/form-data">
            <section>
                <p style="font-size: 18px;padding:40px">Are you sure you want to delete that component?</p>
            </section>
            <footer>
                <a href="javascript:void(0)" class="btn-u close-btn">No</a>
                <button type="submit" class="btn-u btn-u-default" style="">Yes</button>
            </footer>
        </form>
    </div>

</div>
<script type="text/javascript" src="/assets/plugins/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="/assets/plugins/summernote/summernote.js"></script>
<script src="/assets/plugins/sky-forms/version-2.0.1/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="/js/jquery.truncate.js"></script>
<script type="text/javascript" src="/js/main.js"></script>
<script type="text/javascript" src="/js/jquery.joyride-2.0.3.js"></script>
<!-- JS Implementing Plugins -->
<script type="text/javascript" src="/assets/plugins/back-to-top.js"></script>
<script type="text/javascript" src="/assets/plugins/flexslider/jquery.flexslider-min.js"></script>
<script type="text/javascript" src="/assets/plugins/parallax-slider/js/modernizr.js"></script>
<script type="text/javascript" src="/assets/plugins/parallax-slider/js/jquery.cslider.js"></script>
<script type="text/javascript" src="/assets/plugins/counter/waypoints.min.js"></script>
<script type="text/javascript" src="/assets/plugins/counter/jquery.counterup.min.js"></script>
<!-- Datepicker Form -->
<script type="text/javascript" src="/assets/plugins/sky-forms/version-2.0.1/js/jquery-ui.min.js"></script>
<!-- Scrollbar -->
<script src="/assets/plugins/scrollbar/src/jquery.mousewheel.js"></script>
<script src="/assets/plugins/scrollbar/src/perfect-scrollbar.js"></script>
<script type="text/javascript" src="/assets/plugins/jquery-ui.min.js"></script>
<!-- JS Page Level -->
<script type="text/javascript" src="/assets/js/app.js"></script>
<script type="text/javascript" src="/assets/js/pages/index.js"></script>
<script type="text/javascript" src="/js/profile.js"></script>
<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function () {
        App.init();
        App.initSliders();
        Index.initParallaxSlider();
        App.initCounter();
        $('.collection-view-table td p').truncate({max_length: 300});
        <?php if(isset($_SESSION['user'])){?>
        setTimeout(updateLogin, <?php if($_SESSION['user']->last_check > time()) echo 1000*($_SESSION['user']->last_check-time()); else echo 1000 ?>);
        <?php } ?>
    });
</script>
<script>

    var inTut = false;
    $('#tutorial').click(function () {
        $("body").joyride();
        $("body").joyride("destroy");   // joyride bug, when multiple joyrides on a page
        inTut = true;
        $('.joyride-next-tip').show();
        $('#joyRideTipContent').joyride({postStepCallback: function (index, tip) {

        }, 'startOffset': 0, 'tip_class': false});
    });
    <?php if ($tutorial == 'true') { ?>
    $('.joyride-next-tip').show();
    $('#joyRideTipContent').joyride({postStepCallback: function (index, tip) {

    }, 'startOffset': 0});
    <?php } ?>
</script>
<script src="/js/GA-timing.js"></script>
<script>
    $(function() {
        if(typeof GATiming !== "function") return;
        GATiming("profile");
    });
</script>
<!--[if lt IE 9]>
<script src="/assets/plugins/respond.js"></script>
<script src="/assets/plugins/html5shiv.js"></script>
<![endif]-->

<?php

if($c_pop_up_flag)
    echo \helper\htmlElement("community-pop-up", $c_pop_up_array_f);
else
  echo \helper\htmlElement("community-pop-up", $c_pop_up_array_t);

?>

</body>
</html>
