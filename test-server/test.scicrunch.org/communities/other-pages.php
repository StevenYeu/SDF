<?php

if($vars["type"] == "lab") {
    if($vars["subtype"] == "create") {
        $hl_sub = -7;
    } elseif($vars["subtype"] == "list") {
        $hl_sub = -8;
    }
}

elseif($vars["type"] == "rrids") {
    $tab = 1;
}

elseif($_SESSION["user"] && $vars["type"] == "community-labs" && $vars["subtype"] == "main") {
    $main_lab = Lab::getUserMainLab($_SESSION["user"],$community->id);
    if(is_null($main_lab)) {
        $location = $community->fullURL() . "/community-labs/lab-join";
    } else {
        $location = $community->fullURL() . "/lab?labid=" . $main_lab->id;
    }
    header("location: " . $location);
    exit;
}

$theTitle = $vars["type"];
if(strtolower($theTitle) == "rin") {
    $theTitle = "Resource Information Network";
}

if($vars["type"] == "rin") {
    if($vars["subtype"] == "rrids") {
        $tab = "resource-reports";
        $rin_element = \helper\htmlElement("rin/rrids", Array("community" => $community));
    }
    if($vars["subtype"] == "rigor-reproducibility-about") {
        $tab = "rigor-reproducibility";
        $rin_element = \helper\htmlElement("rin/rigor-reproducibility-about", Array("community" => $community));
    }
    if($vars["subtype"] == "research-data-management") {
        $tab = "rigor-reproducibility";
        $rin_element = \helper\htmlElement("rin/research-data-management", Array("community" => $community));
    }
    if($vars["subtype"] == "comply-with-nih-mandates") {
        $tab = "rigor-reproducibility";
        $rin_element = \helper\htmlElement("rin/comply-with-nih-mandates", Array("community" => $community));
    }
    if($vars["subtype"] == "hypothesis-center") {
        $tab = "hypothesis-center";
        if(!$vars["arg1"]) {
            $rin_element = \helper\htmlElement("about/hypothesis_center", Array("community" => $community));
        } elseif($vars["arg1"] == "preview") {
            $rin_element = \helper\htmlElement("about/hypothesis_center", Array("community" => $community, "preview-id" => $vars["arg2"]));
        }
    }
    if($vars["subtype"] == "rrid-report") {
        $tab = "rigor-reproducibility";
        if(!$vars["arg1"]) {
            $rin_element = \helper\htmlElement("rin/rrid-report-intro", Array("community" => $community, "user" => $_SESSION["user"], "error" => $_GET["error"]));
        } else if ($vars["arg1"] == "overview" ) {
            $rin_element = \helper\htmlElement("rin/rrid-report-overview", Array("community" => $community, "user" => $_SESSION["user"], "error" => $_GET["error"]));
        } elseif($vars["arg1"] && !$vars["arg2"]) {
            $rin_element = \helper\htmlElement("rin/rrid-report-single-report", Array("community" => $community, "user" => $_SESSION["user"], "report-id" => $vars["arg1"]));
        } elseif($vars["arg1"] && $vars["arg2"]) {
            if($vars["arg2"] == "preview") {
                $rin_element = \helper\htmlElement("rin/rrid-report-single-report-preview", Array("community" => $community, "user" => $_SESSION["user"], "report-id" => $vars["arg1"]));
            } elseif($vars["arg2"] == "snapshot") {
                $rin_element = \helper\htmlElement("rin/rrid-report-single-report-snapshot", Array("community" => $community, "user" => $_SESSION["user"], "report-id" => $vars["arg1"], "snapshot-id" => $_GET["id"]));
            } else {
                $rin_element = \helper\htmlElement("rin/rrid-report-single-report-item", Array("community" => $community, "user" => $_SESSION["user"], "report-id" => $vars["arg1"], "uuid" => $vars["arg2"]));
            }
        }
    }
    if($vars["subtype"] == "suggested-data-repositories") {
        $tab = "rigor-reproducibility";
        $rin_element = \helper\htmlElement("rin/suggested-data-repositories", Array("community" => $community, "user" => $_SESSION["user"]));
    }
    if($vars["subtype"] == "suggested-software") {
        $tab = "rigor-reproducibility";
        $rin_element = \helper\htmlElement("rin/suggested-software", Array("community" => $community, "user" => $_SESSION["user"]));
    }
    if($vars["subtype"] == "sources") {
        $tab = "resource-reports";
        if($vars["arg1"]) {
            $rin_element = \helper\htmlElement("rin-individual-source", Array("community" => $community, "user" => $_SESSION["user"], "source_rrid" => $vars["arg1"]));
        } else {
            $rin_element = \helper\htmlElement("rin-sources", Array("community" => $community, "user" => $_SESSION["user"]));
        }
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
    <title><?php echo $community->shortName ?> | <?php echo $theTitle ?></title>

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

    <!-- CSS Page Style -->
    <link rel="stylesheet" href="/assets/css/pages/page_clients.css">

    <!-- CSS Theme -->
    <link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">
    <link rel="stylesheet" href="/assets/css/shop/shop.blocks.css">
    <link rel="stylesheet" href="/assets/plugins/sky-forms/version-2.0.1/css/custom-sky-forms.css">

    <!-- CSS Customization -->
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/css/community-search.css">
    <link rel="stylesheet" href="/assets/css/pages/page_log_reg_v2.css">
    <link rel="stylesheet" href="/css/joyride-2.0.3.css">
    <link href="/assets/css/pages/blog_masonry_3col.css" rel="stylesheet">
    <link href="/css/main.css" rel="stylesheet">

    <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="/js/main.js"></script>
</head>

<body>
<?php echo \helper\topPageHTML(); ?>
<input type="hidden" id="portal-name" value="<?php echo $community->portalName ?>" />
<input type="hidden" id="cid" value="<?php echo $community->id ?>" />

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
    if (count($components['breadcrumbs']) > 0) {
        $component = $components['breadcrumbs'][0];
        include $_SERVER['DOCUMENT_ROOT'] . '/components/body/parts/blocks/breadcrumbs.php';
    }


    // Content
    if($vars["type"] == "lab" || $vars["type"] == "community-labs" || $vars["type"] == "data") {
        include $GLOBALS["DOCUMENT_ROOT"] . "/communities/ssi/lab.php";
    }
    elseif($vars["type"] == "rrid-report" && $vars["subtype"] == "snapshot") {
        include $GLOBALS["DOCUMENT_ROOT"] . "/communities/ssi/rrid-report-snapshot.php";
    }

    elseif($vars["type"] == "rin") {
        echo $rin_element;
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

<script src="/js/Highcharts-6.0.7/code/js/highcharts.js"></script>
<script src="/js/Highcharts-6.0.7/code/js/highcharts-more.js"></script>
<link rel="stylesheet" href="/js/Highcharts-6.0.7/code/css/highcharts.css" />

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
    $('.truncate-long').truncate({max_length: 300});
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
