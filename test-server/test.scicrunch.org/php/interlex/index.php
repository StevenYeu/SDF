<?php
    include('../../classes/classes.php');
    \helper\scicrunch_session_start();

$components = $community->components;

$search = new Search();
$search->community = $community;

$tab = 0;
$hl_sub = 10;

if($vars['title'] === 'view') {
    $url_path = parse_url(explode("?", $_SERVER["REQUEST_URI"])[0], PHP_URL_PATH);
    $ilx = array_pop(explode("/", $url_path));
    $ilx = preg_replace("/\?.+$/", '', $ilx);

    $dbObj = new DbObj();
    $term = new Term($dbObj);
    $term->getByIlx($ilx);
    $term->getExistingIds();
    $term->getSynonyms();
    $term->getSuperclasses();
    $term->getOntologies();
    if ($term->type == 'annotation') {
        $term->getAnnotationType();
    }
    if($term->id) {
        $schema = SchemaGeneratorTerm::generate($term);
    }
}

ob_start();
?>

<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<head>
    <title>
    <?php

        echo $community->shortName . " | InterLex | ";
            echo "Dashboard";
    ?>

    </title>

    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="google-site-verification" content="vhe7FXQ5uQHNwM10raiS4rO23GgbFW6-iyRfapxGPJc" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="favicon.ico">

    <!-- CSS Global Compulsory -->
    <link rel="stylesheet" href="/assets/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="/assets/plugins/line-icons/line-icons.css">
    <link rel="stylesheet" href="/assets/plugins/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/plugins/flexslider/flexslider.css">
    <link rel="stylesheet" href="/assets/plugins/parallax-slider/css/parallax-slider.css">
    <link rel="stylesheet" href="/assets/plugins/sky-forms/version-2.0.1/css/custom-sky-forms.css">

    <!-- CSS Theme -->
    <link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">
    <link rel="stylesheet" href="/assets/css/pages/page_log_reg_v2.css">
    <link href="/assets/css/pages/blog_masonry_3col.css" rel="stylesheet">
    <link rel="stylesheet" href="/assets/css/pages/page_search.css">
    <link rel="stylesheet" href="/css/community-search.css">
    <link rel="stylesheet" href="/assets/plugins/jquery-steps/css/custom-jquery.steps.css">
    <link rel="stylesheet" href="/css/main.css"/>

    <!-- CSS Customization -->
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/css/joyride-2.0.3.css">
    <link rel="stylesheet" href="/css/term.css">

    <link href="static/css/main.ae4451f2.css" rel="stylesheet">
    <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
</head>

<body>
<?php echo \helper\topPageHTML(); ?>
<div class="wrapper">
    <input type="hidden" id="community-portal-name" value="<?php echo $community->portalName ?>" />
    <input type="hidden" id="community" name="community" value="<?= $community->portalName ?>" >
    <?php
    if (!isset($vars['stripped']) || $vars['stripped'] != 'true') {
        if ($community->id == 0) {
            include $_SERVER['DOCUMENT_ROOT'] . '/ssi/header.php';
        } elseif (count($components['header']) == 1) {
            $component = $components['header'][0];
            if ($component->component == 0)
                include $_SERVER['DOCUMENT_ROOT'] . '/components/header/header-normal.php';
            elseif ($component->component == 1)
                include $_SERVER['DOCUMENT_ROOT'] . '/components/header/header-boxed.php';
            elseif ($component->component == 2)
                include $_SERVER['DOCUMENT_ROOT'] . '/components/header/header-float.php';
            elseif ($component->component == 3)
                include $_SERVER['DOCUMENT_ROOT'] . '/components/header/header-flat.php';
            elseif ($component->component == 4)
                include $_SERVER['DOCUMENT_ROOT'] . '/components/header/header-float-no-logo.php';
        } else
            include $_SERVER['DOCUMENT_ROOT'] . '/components/header/header-normal.php';
    }

    if ($vars['type'] === 'interlex') {
        if($vars['title'] === 'view') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/view/term.view.php';
        }
        elseif($vars['title'] === 'search') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/term.search.php';
        }
        elseif($vars['title'] === 'dashboard') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/term.dashboard.php';
        }
    }

echo Connection::createBreadCrumbs('Term Dashboard',array('Home'),array("/".$community->portalName),'Term Dashboard');

?>
    <div id="root"></div>
    <script type="text/javascript" src="static/js/main.702d1482.js"></script>

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
    } else echo '<div style="background:#fff;height:20px"></div>';
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
<!--/wrapper-->

<!-- JS Global Compulsory -->
<script type="text/javascript" src="/assets/plugins/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="/assets/plugins/jquery-ui.min.js"></script>
<script type="text/javascript" src="/assets/plugins/jquery-steps/build/jquery.steps.js"></script>
<script src="/assets/plugins/summernote/summernote.js"></script>
<script src="/assets/plugins/scrollbar/src/perfect-scrollbar.js"></script>
<script type="text/javascript" src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="/assets/plugins/sky-forms/version-2.0.1/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="/js/main.js"></script>
<!-- JS Implementing Plugins -->
<script type="text/javascript" src="/assets/plugins/back-to-top.js"></script>
<script type="text/javascript" src="/assets/plugins/flexslider/jquery.flexslider-min.js"></script>
<script type="text/javascript" src="/assets/plugins/parallax-slider/js/modernizr.js"></script>
<script type="text/javascript" src="/assets/plugins/parallax-slider/js/jquery.cslider.js"></script>
<script type="text/javascript" src="/assets/plugins/counter/waypoints.min.js"></script>
<script type="text/javascript" src="/assets/plugins/counter/jquery.counterup.min.js"></script>
<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=true"></script>
<script type="text/javascript" src="/js/jquery.truncate.js"></script>

<script type="text/javascript" src="/assets/plugins/gmap/gmap.js"></script>
<script type="text/javascript" src="/assets/js/app.js"></script>
<!-- JS Page Level -->
<script type="text/javascript" src="/js/jquery.joyride-2.0.3.js"></script>
<script type="text/javascript" src="/assets/plugins/masonry/jquery.masonry.min.js"></script>
<!-- JS Implementing Plugins -->
<script type="text/javascript" src="/assets/js/pages/blog-masonry.js"></script>
<script src='https://www.google.com/recaptcha/api.js'></script>
<script type="text/javascript">
    jQuery(document).ready(function () {
        App.init();
        App.initSliders();
        App.initCounter();
        <?php if(isset($_SESSION['user'])){?>
        setTimeout(updateLogin, <?php if($_SESSION['user']->last_check > time()) echo 1000*($_SESSION['user']->last_check-time()); else echo 1000 ?>);
        <?php } ?>
    });
</script>

<script src="/js/GA-timing.js"></script>
<script>
    $(function() {
        if(typeof GATiming !== "function") return;
        GATiming("interlex");
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

<?php echo ob_get_clean(); ?>
