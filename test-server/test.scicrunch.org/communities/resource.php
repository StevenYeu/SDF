<?php
/* buffer because header can be called inside page after HTML */
ob_start();

//error_reporting(E_ALL);
//ini_set("display_errors", 1);
$tab = 0;
$hl_sub = -4;
$ol_sub = -1;

$components = $community->components;
//print_r($components);

$search = new Search();
$search->community = $community;
if ($vars['editmode']) {
    if (!isset($_SESSION['user']) || $_SESSION['user']->levels[$community->id] < 2)
        $vars['editmode'] = false;
}

if ($vars['rel']) {
    $relationship = new Form_Relationship();
    $relationship->getByID($vars['rel']);
    if (!$relationship->id) {
        header('location:/' . $community->portalName . '/about/resource');
        exit;
    }
}

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

    <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
</head>

<body>
<?php echo \helper\topPageHTML(); ?>
<div class="wrapper" <?php if ($vars['editmode']) echo 'style="margin-top:32px;"' ?>>
    <input type="hidden" id="community-portal-name" value="<?php echo $community->portalName ?>" />
    <?php
    echo \helper\htmlElement("components/header", Array(
        "community" => $community,
        "component" => $components["header"][0],
        "vars" => $vars,
        "tab" => $tab,
        "hl_sub" => $hl_sub,
        "ol_sub" => $ol_sub,
    ));

    if (count($components['breadcrumbs']) > 0 && !$community->rinStyle()) {
        $component = $components['breadcrumbs'][0];
        if (!$component->disabled)
            ob_start();
            include $_SERVER['DOCUMENT_ROOT'] . '/components/body/parts/blocks/breadcrumbs.php';
            $breadcrumb_component = ob_get_clean();
    }

    ob_start();
    if ($vars["title"] === "resourcesedit") {
        $bc_title = "Resources edit";
        include $_SERVER["DOCUMENT_ROOT"] . "/browsing/curator.php";
    } elseif ($vars["title"] === "resourcementionupload") {
        $bc_title = "Resource mentions upload";
        include $_SERVER["DOCUMENT_ROOT"] . "/communities/ssi/resource-mention-upload.php";
    } elseif (isset($vars["resource_suggestion"])) {
        $bc_title = "Resource suggestion";
        if($vars["resource_suggestion"] == "finish") include $_SERVER["DOCUMENT_ROOT"] . "/create-pages/resource-suggest-finish.php";
        else include $_SERVER["DOCUMENT_ROOT"] . "/create-pages/resource-suggest.php";
    } elseif (isset($vars['form'])) {
        $bc_title = "Resource submit";
        if(isset($_SESSION["user"])) {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/resource-form.php';
        } else {
            header("location:" . $_SERVER["REQUEST_URI"] . "&resource_suggestion");
            exit;
        }
    } elseif ($vars['submit']) {
        $bc_title = "Resource submit";
        include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/resource-finish.php';
    } else {
        $bc_title = "Add a resource";   ##replaced "Type select" with "Add a resource" -- Vicky-2018-12-5
        include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/type-select.php';
    }
    $html_body = ob_get_clean();

    if($community->rinStyle()) {
        $rin_data = Array(
            "html-body" => $html_body,
            "breadcrumbs" => Array(
                Array("text" => "Home", "url" => $community->fullURL()),
                //Array("text" => "Resources", "url" => $community->fullURL() . "/account/resources"),
                ## Change Resource link (/account/resources) to Tools (/data/source/nlx_144509-1/search) -- Vicky-2018-11-13
                Array("text" => "Tools", "url" => $community->fullURL() . "/data/source/nlx_144509-1/search"),
                Array("text" => $bc_title, "active" => true),
            ),
            "title" => $bc_title,
        );
        echo \helper\htmlElement("rin-style-page", $rin_data);
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
<script>
    Validation.initValidation();
    jQuery.validator.addMethod("exists", function (value, element, param) {
        var status;
        $.ajax({
            url: param,
            data: 'name=' + value,
            async: false,
            dataType: 'json',
            success: function (j) {
                if (j != '0') {
                    status = true;
                } else
                    status = false;
            }
        });
        return status;
    }, $.format("That is not available."));
    jQuery.validator.addMethod("accept", function (value, element, param) {
        return value.match(new RegExp(param));
    }, $.format("You have used an invalid character for this type."));
    jQuery.validator.addClassRules('portal', {
        required: false,
        accept: "[0-9a-fA-F\-\.]*",
        exists: "/validation/community-name.php"
    });
    jQuery.validator.addClassRules('Resource_Name', {
        required: false,
        accept: "[0-9a-fA-F\-\.]*"
        //exists: "/validation/resource-name.php"
    });
</script>
<script src="/js/GA-timing.js"></script>
<script>
    $(function() {
        if(typeof GATiming !== "function") return;
        GATiming("community-resource");
    });
</script>
<!--[if lt IE 9]>
<script src="/assets/plugins/respond.js"></script>
<script src="/assets/plugins/html5shiv.js"></script>
<![endif]-->
<script>
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

<?php

if($c_pop_up_flag)
    echo \helper\htmlElement("community-pop-up", $c_pop_up_array_f);
else
    echo \helper\htmlElement("community-pop-up", $c_pop_up_array_t);

?>


</body>
</html>

<?php
/* buffer because header can be called inside page after HTML */
echo ob_get_clean();
?>
