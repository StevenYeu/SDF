<?php
include 'process-elastic-search.php';
$components = $community->components;
$vars['community'] = $community;

$search = new Search();
$search->community = $community;
$search->create($vars, !isset($_COOKIE["old-interface-resources"]));

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
    if($vars['title'] === 'view') {
        if($term->id) {
            echo $term->label;
            foreach($term->existing_ids as $ei) {
                if($ei->preferred === '1') {
                    echo " " . $ei->curie;
                    break;
                }
            }
            echo " (" . $term->ilx . ")";
        } else {
            echo "Term not found";
        }
    } else {
        echo $community->shortName . " | InterLex | ";
        if($vars['title'] === 'search') {
            echo "Search";
        }
        elseif($vars['title'] === 'create') {
            echo "Create";
        }
        elseif($vars['title'] === 'upload') {
            echo "Upload";
        }
        elseif($vars['title'] === 'create-annotation') {
            echo "Create Annotation";
        }
        elseif($vars['title'] === 'create-relationship') {
            echo "Create Relationship";
        }
        elseif($vars['title'] === 'test') {
            echo "Test";
        }
        elseif($vars['title'] === 'edit') {
            echo "Edit";
        }
        elseif($vars['title'] === 'edit-annotation') {
            echo "Edit Annotation";
        }
        elseif($vars['title'] === 'edit-relationship') {
            echo "Edit Relationship";
        }
        elseif($vars['title'] === 'curate-mapping') {
            echo "Curate Mappings";
        }
        elseif($vars['title'] === 'dashboard') {
            echo "Dashboard";
        }
        elseif($vars['title'] === 'dashboard-history') {
            echo "History Dashboard";
        }
        elseif($vars['title'] === 'release-notes') {
            echo "Release  Notes";
        }
        elseif($vars['title'] === 'dashboard-mappings') {
            echo "Mappings Dashboard";
        }
        elseif($vars['title'] === 'dashboard-comments') {
            echo "Comments Dashboard";
        }
        elseif($vars['title'] === 'search-index') {
            echo "Index";
        }
    }
    ?>

    </title>

    <?php if($vars['title'] === 'view' && $term->id)
        include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/view/term.json-ld.php';
    ?>

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
</head>

<body>
      <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>

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

<?php echo \helper\topPageHTML(); ?>
<div class="wrapper">
    <input type="hidden" id="community-portal-name" value="<?php echo $community->portalName ?>" />
    <input type="hidden" id="community" name="community" value="<?= $community->portalName ?>" >
    <?php
    echo \helper\htmlElement("components/header", Array(
        "community" => $community,
        "component" => $components["header"][0],
        "vars" => $vars,
        "tab" => $tab,
        "hl_sub" => $hl_sub,
        "ol_sub" => $ol_sub,
    ));

    if ($vars['type'] === 'interlex') {
        if($vars['title'] === 'view') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/view/term.view.php';
        }
        elseif($vars['title'] === 'search') {
            // include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/term.search.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/term.snippet.view.php';
            // include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/term.table.view.php';
        }
        elseif($vars['title'] === 'table') {
            // include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/term.search.php';
            // include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/term.snippet.view.php';
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/term.table.view.php';
        }
        elseif($vars['title'] === 'create') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/create/term.create.php';
        }
        elseif($vars['title'] === 'upload') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/create/term.upload.php';
        }
        elseif($vars['title'] === 'create-annotation') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/create/term.annotation.php';
        }
        elseif($vars['title'] === 'create-relationship') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/create/term.relationship.php';
        }
        elseif($vars['title'] === 'test') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/create/term.test.php';
        }
        elseif($vars['title'] === 'edit') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/edit/term.edit.php';
        }
        elseif($vars['title'] === 'edit-annotation') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/edit/term.annotation.php';
        }
        elseif($vars['title'] === 'edit-relationship') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/edit/term.relationship.php';
        }
        elseif($vars['title'] === 'curate-mapping') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/edit/term.mapping.curate.php';
        }
        elseif($vars['title'] === 'dashboard') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/term.dashboard.php';
        }
        elseif($vars['title'] === 'dashboard-history') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/term.dashboard-history.php';
        }
        elseif($vars['title'] === 'dashboard-mappings') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/term.dashboard-mappings.php';
        }
        elseif($vars['title'] === 'dashboard-comments') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/term.dashboard-comments.php';
        }
        elseif($vars['title'] === 'search-index') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/term.search-index.php';
        }
        elseif($vars['title'] === 'release-notes') {
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/term/term.release-notes.php';
        }
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
    <!-- <div class="background"></div> -->
    <!--=== End Copyright ===-->
    <div class="invis-background"></div>
    <div class="background"></div>
    <?php if (isset($_SESSION['user'])) { ?>
        <div class="saved-this-search back-hide no-padding">
            <div class="close dark less-right">X</div>
            <form method="post" action="/forms/other-forms/add-saved-search.php"
                  id="header-component-form" class="sky-form" enctype="multipart/form-data">
                <header>Save This Search</header>
                <fieldset>
                    <section>
                        <label class="label">Name</label>
                        <label class="input">
                            <i class="icon-append fa fa-question-circle"></i>
                            <input type="text" name="name" placeholder="Focus to view the tooltip">
                            <b class="tooltip tooltip-top-right">The name of your saved search.</b>
                        </label>
                    </section>
                    <section>
                        <label class="label">Community</label>
                        <label class="input">
                            <i class="icon-append fa fa-question-circle"></i>
                            <input type="hidden" name="cid" placeholder="Focus to view the tooltip"
                                   value="<?php echo $community->id ?>">

                            <input type="text" disabled="disabled" placeholder="Focus to view the tooltip"
                                   value="<?php echo $community->name ?>">
                            <b class="tooltip tooltip-top-right">The community you are in.</b>
                        </label>
                    </section>
                    <section>
                        <label class="label">Category</label>
                        <label class="input">
                            <i class="icon-append fa fa-question-circle"></i>
                            <input type="hidden" name="category" placeholder="Focus to view the tooltip"
                                   value="interlex">
                            <input type="text" disabled="disabled" placeholder="Focus to view the tooltip"
                                   value="interlex">
                            <b class="tooltip tooltip-top-right">The category you are on.</b>
                        </label>
                    </section>
                    <?php if ($search->subcategory) { ?>
                        <section>
                            <label class="label">Subcategory</label>
                            <label class="input">
                                <i class="icon-append fa fa-question-circle"></i>
                                <input type="hidden" name="subcategory" placeholder="Focus to view the tooltip"
                                       value="<?php echo $search->subcategory ?>">
                                <input type="text" disabled="disabled" placeholder="Focus to view the tooltip"
                                       value="<?php echo $search->subcategory ?>">
                                <b class="tooltip tooltip-top-right">The subcategory you are on.</b>
                            </label>
                        </section>
                    <?php } ?>
                    <?php if ($search->source) {
                        $source = new Sources();
                        //echo $search->source;
                        $source->getByView($search->source);
                        ?>

                        <section>
                            <label class="label">Source View</label>
                            <label class="input">
                                <i class="icon-append fa fa-question-circle"></i>
                                <input type="hidden" name="nif" placeholder="Focus to view the tooltip"
                                       value="<?php echo $search->source ?>">
                                <input type="text" disabled="disabled" placeholder="Focus to view the tooltip"
                                       value="<?php echo $source->getTitle() ?>">
                                <b class="tooltip tooltip-top-right">The subcategory you are on.</b>
                            </label>
                        </section>
                    <?php } ?>
                    <section>
                        <label class="label">Query</label>
                        <label class="input">
                            <i class="icon-append fa fa-question-circle"></i>
                            <input type="hidden" name="query" placeholder="Focus to view the tooltip"
                                   value="<?php echo htmlentities($search->query) ?>">
                            <input type="hidden" name="display" placeholder="Focus to view the tooltip"
                                   value="<?php echo htmlentities($search->display) ?>">
                            <input type="text" disabled="disabled" placeholder="Focus to view the tooltip"
                                   value="<?php if ($search->display && $search->display != '') echo $search->display; else echo $search->query ?>">
                            <b class="tooltip tooltip-top-right">The query you searched for</b>
                            <input type="hidden" name="params" value="<?php echo $search->getParams() ?>"/>
                        </label>
                    </section>
                </fieldset>

                <footer>
                    <button type="submit" class="btn-u btn-u-default" style="width:100%">Save Search</button>
                </footer>
            </form>
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
    <?php } ?>
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
