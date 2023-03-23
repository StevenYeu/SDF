<?php

//error_reporting(E_ALL);
//ini_set("display_errors", 1);
$tab = -1;
$hl_sub = 0;
$ol_sub = -1;

$components = $community->components;

$search = new Search();
$search->community = $community;


$body_include =  $_SERVER['DOCUMENT_ROOT'] . '/components/body/single-column.php';

if (!isset($vars['stripped']) || $vars['stripped'] != 'true') {
    if (count($components['footer']) > 0) {
        $component = $components['footer'][0];
        $footer_component = $component;
        if ($component->component == 92)
            $footer_include = $_SERVER['DOCUMENT_ROOT'] . '/components/footer/footer-normal.php';
        elseif ($component->component == 91)
            $footer_include = $_SERVER['DOCUMENT_ROOT'] . '/components/footer/footer-light.php';
        elseif ($component->component == 90)
            $footer_include = $_SERVER['DOCUMENT_ROOT'] . '/components/footer/footer-dark.php';
    } else
        $footer_include = $_SERVER['DOCUMENT_ROOT'] . '/components/footer/footer-normal.php';
} else $footer_string = '<div style="background:#fff;height:20px"></div>';


if(isset($_COOKIE['community_warning']) || strpos($_SERVER['HTTP_REFERER'], "/browse/search") === false || $community->id == 0){
    $community_warning_display = "false";
}else{
    $community_warning_display = "true";
}

/******************************************************************************************************************************************************************************************************/
?>

<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<head>
    <title><?php echo $community->shortName ?> | Welcome...</title>

    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo \helper\metaDescription($community->description) ?>">
    <meta name="author" content="">
    <meta name="google-site-verification" content="wvDSzr1T7sYwBhYfnBtZCGkG-1-QbVPSUaDKqzf0mRc" />
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
    <link rel="stylesheet" href="/assets/plugins/sky-forms/version-2.0.1/css/custom-sky-forms.css">
    <link rel="stylesheet" href="/assets/plugins/owl-carousel2/assets/owl.carousel.css">
    <link rel="stylesheet" href="/assets/plugins/revolution-slider/examples/rs-plugin/css/settings.css">

    <!-- CSS Theme -->
    <link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">
    <link rel="stylesheet" href="/assets/css/pages/page_log_reg_v2.css">
    <link rel="stylesheet" href="/assets/plugins/fancybox/source/jquery.fancybox.css">
    <link rel="stylesheet" href="/assets/css/pages/page_search.css">
    <link rel="stylesheet" href="/assets/css/pages/page_job.css">
    <link rel="stylesheet" href="/css/community-search.css">
    <link rel="stylesheet" href="/assets/css/pages/page_one.css">
    <link rel="stylesheet" href="/assets/plugins/summernote/summernote.css"/>
    <link rel="stylesheet" href="/css/main.css"/>
    <?php if($community->id == 0): ?><link rel="stylesheet" href="/css/main-page.css"/><?php endif ?>

    <!-- CSS Customization -->
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/css/joyride-2.0.3.css">

    <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>

    <style>
        .headline h2 {
            border-bottom-color: #<?php echo Component::getMainColor($community->id); ?>;
        }
        .tag-box.tag-box-v1 {
            border-top-color: #<?php echo Component::getMainColor($community->id); ?>;
        }
    </style>
</head>

<body>
<?php echo \helper\topPageHTML(); ?>
<div class="wrapper" <?php if ($vars['editmode']) echo 'style="margin-top:32px;"' ?>>
    <?php
    echo \helper\htmlElement("components/header", Array(
        "community" => $community,
        "component" => $components["header"][0],
        "vars" => $vars,
        "tab" => $tab,
        "hl_sub" => $hl_sub,
        "ol_sub" => $ol_sub,
    ));
    include $body_include;
    echo Component::footerHTML($components, $community, $vars);
    ?>
    <?php if($vars['editmode']): // not necessary to load these unless in editmode ?>
        <div class="background"></div>
        <div class="component-select-container back-hide">
            <div class="close dark">X</div>
            <div class="selection">
                <h2 align="center">Select a Component to Add</h2>

                <div class="components-select">
                    <?php
                    $holder = new Component();
                    echo $holder->getComponentSelectHTML($community->id);
                    ?>
                </div>
            </div>
        </div>
        <div class="component-add-load back-hide no-padding"></div>
        <div class="component-delete back-hide no-padding">
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
    <?php endif ?>

</div>
<!--/wrapper-->

<!-- JS Global Compulsory -->
<script type="text/javascript" src="/assets/plugins/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="/assets/plugins/jquery-ui.min.js"></script>
<script type="text/javascript" src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="/assets/plugins/scrollbar/src/perfect-scrollbar.js"></script>
<script type="text/javascript" src="/js/main.js"></script>
<script type="text/javascript" src="/js/profile.js"></script>
<!-- JS Implementing Plugins -->
<script type="text/javascript" src="/assets/plugins/back-to-top.js"></script>
<script type="text/javascript" src="/assets/plugins/flexslider/jquery.flexslider-min.js"></script>
<script type="text/javascript" src="/assets/plugins/parallax-slider/js/modernizr.js"></script>
<script type="text/javascript" src="/assets/plugins/parallax-slider/js/jquery.cslider.js"></script>
<script type="text/javascript" src="/assets/plugins/counter/waypoints.min.js"></script>
<script type="text/javascript" src="/assets/plugins/counter/jquery.counterup.min.js"></script>
<!-- JS Page Level -->
<script type="text/javascript" src="/js/jquery.joyride-2.0.3.js"></script>
<script src="/assets/plugins/summernote/summernote.js"></script>
<script src="/assets/plugins/sky-forms/version-2.0.1/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="/assets/js/app.js"></script>
<script type="text/javascript" src="/assets/js/pages/index.js"></script>
<script type="text/javascript" src="/assets/plugins/owl-carousel2/owl.carousel.js"></script>
<!-- SLIDER REVOLUTION 4.x SCRIPTS  -->
<script type="text/javascript"
        src="/assets/plugins/revolution-slider/examples/rs-plugin/js/jquery.themepunch.plugins.min.js"></script>
<script type="text/javascript"
        src="/assets/plugins/revolution-slider/examples/rs-plugin/js/jquery.themepunch.revolution.min.js"></script>
<script type="text/javascript" src="/assets/plugins/fancybox/source/jquery.fancybox.pack.js"></script>
<!-- JS Page Level -->
<script type="text/javascript" src="/assets/js/plugins/owl-carousel.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function () {
        App.init();
        App.initSliders();
        Index.initParallaxSlider();
        App.initCounter();
        Index.initRevolutionSlider();
        OwlCarousel.initOwlCarousel();
        <?php if(isset($_SESSION['user'])){?>
        setTimeout(updateLogin, <?php if($_SESSION['user']->last_check > time()) echo 1000*($_SESSION['user']->last_check-time()); else echo 1000 ?>);
        <?php } ?>

        if($("#community-warning-display").val() == "true"){
            $("#switching-to-comm").modal('show');
        }
        $("#dont-show-switching").click(function(){
            $("#switching-to-comm").modal('hide');
            document.cookie = encodeURIComponent("community warning") + "=" + encodeURIComponent("true") + "; expires=Fri, 31 Dec 9999 23:59:59 GMT";
        });
    });
</script>
<script src="/js/GA-timing.js"></script>
<script>
    $(function() {
        if(typeof GATiming !== "function") return;
        GATiming("community-home");
    });
</script>
<!--[if lt IE 9]>
<script src="/assets/plugins/respond.js"></script>
<script src="/assets/plugins/html5shiv.js"></script>
<![endif]-->

<input type="hidden" id="community-warning-display" value="<?php echo $community_warning_display ?>"/>
<div class="modal fade" id="switching-to-comm" tabindex="-1" role="dialog" aria-labelledby="switching-title">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="switching-title">Entering a community</h4>
            </div>
            <div class="modal-body">
                <p>You are entering a community.  Communities contain customized data.  Results may be different from the results in the main SciCrunch community.</p>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="dont-show-switching">Close and don't show again</button>
            </div>
        </div>
    </div>
</div>

<?php
if ($community_warning_display == "false") {
    if($c_pop_up_flag)
        echo \helper\htmlElement("community-pop-up", $c_pop_up_array_f);
    else
        echo \helper\htmlElement("community-pop-up", $c_pop_up_array_t);
}

?>


</body>
</html>
