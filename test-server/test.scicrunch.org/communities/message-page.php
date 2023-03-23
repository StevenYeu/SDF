<?php

switch($vars["title"]) {
    case "join-request-confirm":
        $message = "A request to join " . $community->name . " has been sent to the owner of the community.";
        break;
    case "join-request-response":
        $message = "Thank you for responding to the request.";
        break;
    case "join-request-response-expired":
        $message = "Thank you for responding to the request, but the link has already expired.  Please go to your <a href=\"" . $communities->fullURL() . "/account/communities/" . $community->portalName . "/pending-user-requests\">community management</a> to reply to the request.";
        break;
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
    <link rel="stylesheet" href="/css/community-search.css">

    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="/assets/plugins/line-icons/line-icons.css">
    <link rel="stylesheet" href="/assets/plugins/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/plugins/flexslider/flexslider.css">
    <link rel="stylesheet" href="/assets/plugins/parallax-slider/css/parallax-slider.css">
    <link rel="stylesheet" href="/assets/css/pages/page_log_reg_v1.css">

    <!-- CSS Theme -->
    <link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">
    <link rel="stylesheet" href="/assets/plugins/sky-forms/version-2.0.1/css/custom-sky-forms.css">
    <link rel="stylesheet" href="/assets/css/pages/page_log_reg_v2.css">
    <link rel="stylesheet" href="/css/main.css"/>

    <!-- CSS Customization -->
    <link rel="stylesheet" href="/css/joyride-2.0.3.css">
    <link rel="stylesheet" href="/assets/css/custom.css">

    <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
</head>


<body>
<?php echo \helper\topPageHTML(); ?>
<div class="wrapper" <?php if ($vars['editmode']) echo 'style="margin-top:32px;"' ?>>
    <!--=== Header ===-->

    <?php
    //Header
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
    }?>


    <?php ob_start(); ?>
    <div class="row content">
        <div class="col-md-1"></div>
        <div class="col-md-10">
            <h3><?php echo $message ?></h3>
        </div>
        <div class="col-md-1"></div>
    </div>

    <?php
    $html_body = ob_get_clean();

    if($community->rinStyle()) {
        echo \helper\htmlElement("rin-style-page", Array(
            "title" => "Join request",
            "breadcrumbs" => Array(
                Array("text" => "Home", "url" => $community->fullURL()),
                Array("text" => "Join request", "active" => true),
            ),
            "html-body" => $html_body,
        ));
    } else {
        echo $breadcrumb_component;
        echo $html_body;
    }
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


<script type="text/javascript" src="/assets/plugins/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="/assets/plugins/jquery-ui.min.js"></script>
<script type="text/javascript" src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/js/jquery.truncate.js"></script>
<script src="/assets/plugins/scrollbar/src/jquery.mousewheel.js"></script>
<script src="/assets/plugins/scrollbar/src/perfect-scrollbar.js"></script>
<script src="/assets/plugins/summernote/summernote.js"></script>
<script src="/assets/plugins/sky-forms/version-2.0.1/js/jquery.validate.min.js"></script>
<script src="/assets/plugins/scrollbar/src/perfect-scrollbar.js"></script>
<script type="text/javascript" src="/js/main.js"></script>
<script type="text/javascript" src="/js/profile.js"></script>
<script src='https://www.google.com/recaptcha/api.js'></script>
<!-- JS Implementing Plugins -->

<script type="text/javascript" src="/assets/plugins/back-to-top.js"></script>
<!-- JS Page Level -->
<script type="text/javascript" src="/assets/js/app.js"></script>
<script type="text/javascript" src="/js/jquery.joyride-2.0.3.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function () {
        App.init();
        <?php if(isset($_SESSION['user'])){?>
        setTimeout(updateLogin, <?php if($_SESSION['user']->last_check > time()) echo 1000*($_SESSION['user']->last_check-time()); else echo 1000 ?>);
        <?php } ?>
    });
</script>
<script src="/js/GA-timing.js"></script>
<script>
    $(function() {
        if(typeof GATiming !== "function") return;
        GATiming("community-join-request-confirm");
    });
</script>
</body>
</html>
