<?php

//error_reporting(E_ALL);
//ini_set("display_errors", 1);
if ($vars['title'] == 'sources') {
    $tab = 0;
    $hl_sub = 1;
} elseif ($vars['category'] == 'literature') {
    $tab = 3;
} else {

}

$components = $community->components;
$search = new Search();
$search->community = $community;

$holder = new Sources();
$sources = $holder->getAllSources();

?>
<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<head>
    <title><?php echo $community->shortName ?> | Sources</title>

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

    <!-- CSS Page Style -->
    <link rel="stylesheet" href="/assets/css/pages/page_clients.css">

    <!-- CSS Theme -->
    <link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">
    <link rel="stylesheet" href="/assets/css/shop/shop.blocks.css">
    <link rel="stylesheet" href="/css/main.css"/>

    <!-- CSS Customization -->
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/css/community-search.css">
    <link rel="stylesheet" href="/assets/css/pages/page_log_reg_v2.css">
    <link rel="stylesheet" href="/css/joyride-2.0.3.css">
    <link href="/assets/css/pages/blog_masonry_3col.css" rel="stylesheet">

    <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
</head>

<body>
<?php echo \helper\topPageHTML(); ?>

<div class="wrapper" <?php if ($vars['editmode']) echo 'style="margin-top:32px;"' ?>>
    <!-- Brand and toggle get grouped for better mobile display -->
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
        ob_start();
        include $_SERVER['DOCUMENT_ROOT'] . '/components/body/parts/blocks/breadcrumbs.php';
        $breadcrumb_component = ob_get_clean();
    }

    ob_start();
    if ($vars['title'] == 'sources') {
        if (isset($vars['id'])) {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/individual.source.page.php';
            $rin_breadcrumbs = Array(
                Array("text" => "Home", "url" => $community->fullURL()),
                Array("text" => "Our Sources", "url" => $community->fullURL() . "/about/sources"),
                Array("text" => "Source", "active" => true),
            );
            $rin_title = "Source";
        } else {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/source.page.php';
            $rin_breadcrumbs = Array(
                Array("text" => "Home", "url" => $community->fullURL()),
                Array("text" => "Our Sources", "active" => true),
            );
            $rin_title = "Our Sources";
        }
    } elseif($vars["title"] == "keyaction") {
        include $_SERVER["DOCUMENT_ROOT"] . "/communities/ssi/keyaction.page.php";
        $rin_breadcrumbs = Array(
            Array("text" => "Home", "url" => $community->fullURL()),
            Array("text" => "Update", "active" => true),
        );
        $rin_title = "Update";
    }
    $html_body = ob_get_clean();

    if($community->rinStyle()) {
        $rin_data = Array(
            "title" => $rin_title,
            "breadcrumbs" => $rin_breadcrumbs,
            "html-body" => $html_body,
        );
        echo \helper\htmlElement("rin-style-page", $rin_data);
    } else {
        echo $breadcrumb_component;
        echo $html_body;
    }

    // Footer
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
    <!--=== Breadcrumbs v3 ===-->
    <!--=== End Copyright ===-->
</div>
<!--/End Wrapepr-->

<!-- JS Global Compulsory -->
<script type="text/javascript" src="/assets/plugins/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/js/jquery.truncate.js"></script>
<script src="/assets/plugins/summernote/summernote.js"></script>
<script src="/assets/plugins/sky-forms/version-2.0.1/js/jquery.validate.min.js"></script>
<script type="text/javascript" src="/js/main.js"></script>
<script type="text/javascript" src="/js/extended-circle-master.js"></script>
<script type="text/javascript" src="/js/circle-master.js"></script>
<script type="text/javascript" src="/assets/plugins/masonry/jquery.masonry.min.js"></script>
<!-- JS Implementing Plugins -->
<script type="text/javascript" src="/assets/js/pages/blog-masonry.js"></script>
<!-- JS Implementing Plugins -->
<script type="text/javascript" src="/assets/plugins/back-to-top.js"></script>
<!-- JS Page Level -->
<script type="text/javascript" src="/assets/js/app.js"></script>
<script type="text/javascript" src="/js/jquery.joyride-2.0.3.js"></script>
<script type="text/javascript">
    jQuery(document).ready(function () {
        App.init();
        CirclesMaster.initCirclesMaster1();
        <?php if(isset($_SESSION['user'])){?>
        setTimeout(updateLogin, <?php if($_SESSION['user']->last_check > time()) echo 1000*($_SESSION['user']->last_check-time()); else echo 1000 ?>);
        <?php } ?>
    });
</script>
<script type="text/javascript">
    $('.inner-results p').truncate({max_length: 500});
    $('td').truncate({max_length: 200});
    $('.specifies-list span').truncate({max_length: 100});
</script>
<script src="/js/GA-timing.js"></script>
<script>
    $(function() {
        if(typeof GATiming !== "function") return;
        GATiming("community-pages");
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
