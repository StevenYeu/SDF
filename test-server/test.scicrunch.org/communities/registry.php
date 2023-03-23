<?php
$tab = 0;
//error_reporting(E_ALL);
//ini_set("display_errors", 1);
$page = $vars['page'];
$query = $vars['query'];
if (!$page)
    $page = 1;

if (!$query) {
    $query = '';
}
$components = $community->components;

$filter = $vars['content'];

if ($filter) {
    switch ($filter) {
        case 'questions':
            $title = 'Browse Questions';
            $searchText = 'Search for previously asked questions';
            break;
        case 'tutorials':
            $title = 'Browse Tutorials';
            $searchText = 'Search for tutorials to help you navigate ' . $community->shortName;
            break;
        default:
            $holds = new Component();
            $holds->getPageByType($community->id, $filter);
            $title = 'Browse ' . $holds->text1;
            $searchText = 'Search against all ' . $holds->text1;
            break;
    }
} else {
    $title = 'Browse ' . $community->shortName . ' Content';
    $searchText = 'Search across all ' . $community->shortName . ' articles';
}

$hl_sub = -6;

$search = new Search();
$search->community = $community;

?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<head>
    <title>SciCrunch | <?php echo $title ?></title>

    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="google-site-verification" content="vhe7FXQ5uQHNwM10raiS4rO23GgbFW6-iyRfapxGPJc" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="/favicon.ico">

    <!-- CSS Global Compulsory -->
    <link rel="stylesheet" href="/assets/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/assets/plugins/sky-forms/version-2.0.1/css/custom-sky-forms.css">

    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="/assets/plugins/line-icons/line-icons.css">
    <link rel="stylesheet" href="/assets/plugins/font-awesome/css/font-awesome.min.css">

    <!-- CSS Page Style -->
    <link rel="stylesheet" href="/assets/css/pages/page_search_inner.css">
    <link rel="stylesheet" href="/assets/css/pages/page_log_reg_v2.css">
    <link rel="stylesheet" href="/css/community-search.css">
    <link rel="stylesheet" href="/assets/css/shop/shop.blocks.css">

    <!-- CSS Theme -->
    <link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">

    <!-- CSS Customization -->
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/css/joyride-2.0.3.css">

    <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
</head>

<body>
<?php echo \helper\topPageHTML(); ?>

<div class="wrapper" <?php if ($vars['editmode']) echo 'style="margin-top:32px;"' ?>>
    <!-- Brand and toggle get grouped for better mobile display -->
    <?php
    echo \helper\htmlElement("components/header", Array(
        "community" => $community,
        "component" => $components["header"][0],
        "vars" => $vars,
        "tab" => $tab,
        "hl_sub" => $hl_sub,
        "ol_sub" => $ol_sub,
    ));

    //Body

    if (count($components['breadcrumbs']) > 0 && !$community->rinStyle()) {
        $component = $components['breadcrumbs'][0];
        if (!$component->disabled) {
            ob_start();
            include $_SERVER['DOCUMENT_ROOT'] . '/components/body/parts/blocks/breadcrumbs.php';
            $breadcrumb_component = ob_get_clean();
        }
    }

    ob_start();
    if ($vars['mode'] == 'edit') {
        include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/registry.edit.php';
        $rin_breadcrumbs = Array(
            Array("text" => "Home", "url" => $community->fullURL()),
            Array("text" => $community->shortName . " Registry", "url" => $community->fullURL() . "/about/registry"),
            Array("text" => "View Resource", "active" => true),
        );
        $rin_title = "View Resource";
    } elseif ($vars['id']) {
        include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/registry.view.php';
        $rin_breadcrumbs = Array(
            Array("text" => "Home", "url" => $community->fullURL()),
            Array("text" => $community->shortName . " Registry", "url" => $community->fullURL() . "/about/registry"),
            Array("text" => "View Resource", "active" => true),
        );
        $rin_title = "View Resource";
    } else {
        include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/registry.search.php';
        $rin_breadcrumbs = Array(
            Array("text" => "Home", "url" => $community->fullURL()),
            Array("text" => $community->shortName . " Registry", "active" => true),
        );
        $rin_title = "Search through " . $community->shortName;
    }
    $html_body = ob_get_clean();

    if($community->rinStyle()) {
        echo \helper\htmlElement("rin-style-page", Array(
            "title" => $rin_title,
            "breadcrumbs" => $rin_breadcrumbs,
            "html-body" => $html_body,
        ));
    } else {
        echo $breadcrumb_component;
        echo $html_body;
    }

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
    } else echo '<div style="background:#fff;height:20px"></div>';
    ?>
    <!--=== End Copyright ===-->
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
<!--/End Wrapepr-->

<!-- JS Global Compulsory -->
<script type="text/javascript" src="/assets/plugins/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="/assets/plugins/summernote/summernote.js"></script>
<script src="/assets/plugins/sky-forms/version-2.0.1/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=true"></script>

<script type="text/javascript" src="/assets/plugins/gmap/gmap.js"></script>
<script src="/assets/plugins/scrollbar/src/perfect-scrollbar.js"></script>
<script type="text/javascript" src="/js/main.js"></script>
<script type="text/javascript" src="/js/jquery.truncate.js"></script>
<!-- JS Implementing Plugins -->
<script type="text/javascript" src="/assets/plugins/back-to-top.js"></script>
<!-- JS Page Level -->
<script type="text/javascript" src="/assets/js/app.js"></script>
<script type="text/javascript" src="/js/jquery.joyride-2.0.3.js"></script>
<script src='https://www.google.com/recaptcha/api.js'></script>
<script type="text/javascript">
    jQuery(document).ready(function () {
        App.init();
        <?php if(isset($_SESSION['user'])){?>
        setTimeout(updateLogin, <?php if($_SESSION['user']->last_check > time()) echo 1000*($_SESSION['user']->last_check-time()); else echo 1000 ?>);
        <?php } ?>
    });
    $('.inner-results p').truncate({max_length: 500});
</script>
<script>
    $('.truncate-desc').truncate({max_length: 500});
    $('.map').each(function () {
        var _this = $(this);
        var map, marker, infoWindow;
        map = new GMaps({
            div: '#' + $(_this).attr('id'),
            scrollwheel: false,
            lat: $(_this).attr('lat'),
            lng: $(_this).attr('lng')
        });
        infoWindow = new google.maps.InfoWindow({
            content: '<div style="height:40px">' + $(_this).attr('point') + '</div>'
        });
        marker = map.addMarker({
            lat: $(_this).attr('lat'),
            lng: $(_this).attr('lng'),
            title: $(_this).attr('point'),
            infoWindow: infoWindow
        });
        infoWindow.open(map, marker);
    });
</script>
<script src="/js/GA-timing.js"></script>
<script>
    $(function() {
        if(typeof GATiming !== "function") return;
        GATiming("community-registry");
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
