<?php

/*
    if(isset($_SESSION["user"])) {
        $community = $_SESSION["user"]->mainCommunity();
    } elseif($_SESSION["nonuser"]) {
        $community = $_SESSION["nonuser"]->mainCommunity();
    } else {
        $community = new Community();
        $community->getByID(0);
    }
 */

// Manu -- commented above and added the below
$cid = $_GET['cid'];
$community = new Community();
$community->getByID(56);

// Manu: Dec 15, 2021
$components = $community->components; 
if (count($components['footer']) == 1) {
	$component = $components['footer'][0];
}
//echo "********************************* cid: $cid"

?>

<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<head>
   <title> <?php /* Manu -- commented below and added this*/ echo $community->shortName; ?> </title>
    <!-- title>SciCrunch | Verification</title -->

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

    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="/assets/plugins/line-icons/line-icons.css">
    <link rel="stylesheet" href="/assets/plugins/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/plugins/flexslider/flexslider.css">
    <link rel="stylesheet" href="/assets/plugins/parallax-slider/css/parallax-slider.css">
    <link rel="stylesheet" href="/assets/plugins/sky-forms/version-2.0.1/css/custom-sky-forms.css">
    <link rel="stylesheet" href="/assets/plugins/revolution-slider/examples/rs-plugin/css/settings.css">
    <link rel="stylesheet" href="/assets/plugins/owl-carousel/owl-carousel/owl.carousel.css">
    <!-- CSS Theme -->
    <link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">
    <link rel="stylesheet" href="/assets/css/pages/page_log_reg_v2.css">
    <link rel="stylesheet" href="/assets/css/pages/page_search.css">
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/css/community-search.css">
    <link rel="stylesheet" href="/assets/plugins/summernote/summernote.css"/>

    <!-- CSS Customization -->
    <link rel="stylesheet" href="/assets/css/custom.css">
</head>

<body>
<?php echo \helper\topPageHTML(); ?>
<div class="wrapper" <?php if($vars['editmode']) echo 'style="margin-top:30px;"'?>>

    <?php include $community->headerComponentFilePath(); ?>

    <div class="one-page">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <p style="font-size: 18px;padding:40px">
                    <?php echo $print_message; ?>
                    </p>
                </div>
            </div>
        </div>
    </div>


    <?php /*include $community->footerComponentFilePath(); */?>
    <?php  include $_SERVER['DOCUMENT_ROOT'] . '/components/footer/footer-normal.php'; ?>

    <div class="background"></div>
<!-- Manu
    <div class="component-select-container back-hide">
        <div class="close dark">X</div>
        <div class="selection">
            <h2 align="center">Select a Component to Add</h2>

            <div class="components-select">
                <?php /*
                $holder = new Component();
echo $holder->getComponentSelectHTML($community->id); */
                ?>
            </div>
        </div>
    </div>
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
-->

</div>
<!--/wrapper-->

<!-- JS Global Compulsory -->
<script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
<script type="text/javascript" src="/assets/plugins/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="/assets/plugins/jquery-ui.min.js"></script>
<script type="text/javascript" src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script src="/assets/plugins/scrollbar/src/perfect-scrollbar.js"></script>
<script type="text/javascript" src="/js/main.js"></script>
<script type="text/javascript" src="/js/profile.js"></script>
<script type="text/javascript" src="/assets/plugins/owl-carousel/owl-carousel/owl.carousel.js"></script>
<script type="text/javascript" src="/assets/js/plugins/owl-carousel.js"></script>

<script type="text/javascript"
        src="/assets/plugins/revolution-slider/examples/rs-plugin/js/jquery.themepunch.plugins.min.js"></script>
<script type="text/javascript"
        src="/assets/plugins/revolution-slider/examples/rs-plugin/js/jquery.themepunch.revolution.min.js"></script>
<!-- JS Implementing Plugins -->
<script type="text/javascript" src="/assets/plugins/back-to-top.js"></script>
<script type="text/javascript" src="/assets/plugins/flexslider/jquery.flexslider-min.js"></script>
<script type="text/javascript" src="/assets/plugins/parallax-slider/js/modernizr.js"></script>
<script type="text/javascript" src="/assets/plugins/parallax-slider/js/jquery.cslider.js"></script>
<script type="text/javascript" src="/assets/plugins/counter/waypoints.min.js"></script>
<script type="text/javascript" src="/assets/plugins/counter/jquery.counterup.min.js"></script>
<script src="/assets/plugins/summernote/summernote.js"></script>
<script src="/assets/plugins/sky-forms/version-2.0.1/js/jquery.validate.min.js"></script>
<!-- JS Page Level -->
<script type="text/javascript" src="/assets/js/app.js"></script>
<script type="text/javascript" src="/assets/js/pages/index.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function () {
        App.init();
        App.initSliders();
        Index.initParallaxSlider();
        App.initCounter();
        Index.initRevolutionSlider();
        OwlCarousel.initOwlCarousel();
        <?php if(isset($_SESSION['user'])){?>
        setTimeout(updateLogin,<?php if($_SESSION['user']->last_check > time()) echo 1000*($_SESSION['user']->last_check-time()); else echo 1000 ?>);
        <?php } ?>
    });
</script>
<script src="/js/GA-timing.js"></script>
<script>
    $(function() {
        if(typeof GATiming !== "function") return;
        GATiming("verification");
    });
</script>
<!--[if lt IE 9]>
<script src="/assets/plugins/respond.js"></script>
<script src="/assets/plugins/html5shiv.js"></script>
<![endif]-->

</body>
</html>
