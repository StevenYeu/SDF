<?php
ob_start();

/* get category for data-v2 search */
if(isset($vars["category-filter"])) {
    $category_filter = \helper\sanitizeHTMLString($vars["category-filter"]);
    unset($vars["category-filter"]);
} else {
    $category_filter = NULL;
}

/* get referer for breadcrumbs */
$referer_query = parse_url($_SERVER["HTTP_REFERER"], PHP_URL_QUERY);
$referer_query_array = Array();
parse_str($referer_query, $referer_query_array);
if(isset($referer_query_array["category-filter"]) && !isset($vars["category-filter"])) {
    $referer_category_filter = $referer_query_array["category-filter"];
} else {
    $referer_category_filter = NULL;
}

/* if uuid, redirect to rrid page */
if(isset($vars["view"]) && isset($vars["uuid"]) && !isset($vars["rrid"])) {
    $vars["rrid"] = \helper\uuidToRRID($vars["uuid"]);
    if(!is_null($vars["rrid"])) {
        $search = new Search();
        $search->community = $community;
        $url = $search->generateURL($vars);
        if($vars["notif"]) {
            $url .= \helper\urlGetAppendChar($url) . "notif=" . $vars["notif"];
            if(isset($vars["notif_email"])) {
                $url .= "&notif_email";
            }
        }
        header("location: " . $url);
        exit;
    } else {
        \helper\errorPage("noresource");
    }
}

/* using communities/ssi/single-item.php */
$single_item_search = (isset($vars['view']) && isset($vars['rrid']));

## generated pdf Report by unique id -- Vicky-2018-12-21
$tmp = explode("&i=", $vars["resolvertab"]);
$vars["resolvertab"] = $tmp[0];
$id = $tmp[1];
if($single_item_search && $vars["resolvertab"] == "pdf") {
    echo \helper\htmlElement("rin/search-single-item-pdf", Array("view" => $vars["view"], "rrid" => $vars["rrid"], "id" => $id, "community" => $community));
    return;
}

$vars = \helper\sanitizeHTMLString($vars);
$vars["q"] = str_replace("&amp;", "&", $vars["q"]);
for($i = 0; $i < count($vars["filter"]); $i++) {
    $vars["filter"][$i] = str_replace("v_lastmodified_epoch:&gt;", "v_lastmodified_epoch:>", $vars["filter"][$i]);
    $vars["filter"][$i] = str_replace("v_lastmodified_epoch:&lt;", "v_lastmodified_epoch:<", $vars["filter"][$i]);
}

if ($vars['q'] == '*') {
    $theTitle = 'Searching';
} else {
    $theTitle = 'Searching for ';
    if(isset($vars["l"])) {
        $theTitle .= $vars["l"];
    } else {
        $theTitle .= $vars["q"];
    }
}

$meta_description = "Results for the query '" . $vars["q"] . "' in " . $community->portalName;
if ($vars['category'] == 'data') {
    $meta_description .= " data sources.";
    if($community->id == 0){
        $tab = 5;
        $theTitle .= " in Data Sources";
    }else{
        $tab = 2;
        $theTitle .= ' in More Resources';
    }
} elseif ($vars['category'] == 'literature') {
    $tab = (!$community->id) ? 4 : 3;   // if scicrunch community, tab is 4 else 3
    $theTitle .= ' in Literature';
    $meta_description .= " literature.";
} else {
    $tab = 1;
    $num = 0;
    foreach ($community->urlTree as $cat => $array) {
        if ($cat == $vars['category']) {
            $hl_sub = $num;
            if ($vars['subcategory']) {
                $newNum = 0;
                foreach ($array['subcategories'] as $sub => $other) {
                    if ($sub == $vars['subcategory']) {
                        $ol_sub = $newNum;
                        $meta_description .= " " . $cat . ": " . $sub . " across " . count($other["urls"]) . " data views.";
                        break;
                    }
                    $newNum++;
                }
                $theTitle .= ' in ' . $vars['subcategory'];
            } else {
                $theTitle .= ' in ' . $vars['category'];
                $ol_sub = -1;
                $url_count = count($array["urls"]);
                foreach($array["subcategories"] as $sub => $other) $url_count += count($other["urls"]);
                $meta_description .= " " . $cat . " across " . $url_count . " data views.";
            }
            break;
        }
        $num++;
    }
    if ($vars['category'] == 'Any') {
        $url_count = 0;
        foreach($community->urlTree as $cat => $array) {
            $url_count += count($array["urls"]);
            foreach($array["subcategories"] as $sub => $other) {
                $url_count += count($other["urls"]);
            }
        }
        $meta_description .= " across " . $url_count . " data views.";
        $theTitle .= ' through all Categories';
        $hl_sub = -1;
        $ol_sub = -1;
    }
}

/* this block needs to be redone */
if($vars["rrid"]) {
    $resource = new Resource();
    $resource->getByRID($vars["rrid"]);
    $vars["uuid"] = $resource->uuid;
}
if($resource) {
    $theTitle = $resource->columns["Resource Name"] . " (" . $resource->rid . ")";
}

if(isset($vars["litref"]) && $vars["category"] == "literature") {
    $lit_refs = getLiteratureReferencesData($vars["litref"], $community);
    if(!is_null($lit_refs)) {
        $vars["litref_pmids"] = $lit_refs["pmids"];
        $vars["per_page"] = 10; // hard coded into nif-services
    }
}

$subscription_data = notificationData($_GET);
if(!is_null($subscription_data) && $subscription_data["type"] === "filter-time") {
    $filtertime = "v_lastmodified_epoch:>" . (string) $subscription_data["modified_time"];
    if(isset($vars["filter"])) $vars["filter"][] = $filtertime;
    else $vars["filter"] = Array($filtertime);
}

if(preg_match("/^\/?node\/.*/", $vars['q'])) \helper\errorPage("drupal");

scicrunchRegSort($vars, $community);    // for sorting scicrunch registry results

$search = new Search();
$vars['community'] = $community;
$search->create($vars, !isset($_COOKIE["old-interface-resources"]));    // only reformat the categories if it's the new interface
$holder = new Sources();

// if searching the data summary page from a subscription, only show updated views
if(!is_null($subscription_data) && $subscription_data["type"] === "filter-nif") {
    $allSources = $holder->getByViews($subscription_data["modified_nifids"]);
} else {
    $allSources = $holder->getAllSources();
}
$search->allSources = $allSources;
if(isset($vars["nif"])) {
    $meta_description = "Results for the query '" . $vars["q"] . "' in " . $community->portalName;
    if(isset($allSources[$vars["nif"]])) {
        $meta_description .= " across " . $allSources[$vars["nif"]]->getTitle();
    }
}
if($single_item_search || isset($vars["pmid"])) {
    if($single_item_search) {
        $meta_description = "RRID";
    } elseif(isset($vars["pmid"])) {
        $meta_description = "Literature publication PMID:" . htmlentities($vars["pmid"]);
    }
}

$components = $community->components;

if ($search->category == 'literature') {
    if(isset($vars['pmid'])){
        ob_start();
    }
}

if($search->source) {
    $is_rrid_view = ElasticRRIDManager::managerExists($vars["nif"]);
    if($search->category == "discovery") $is_source_view = ElasticRRIDManager::esManagerByViewID($search->source, false);
}

/* header highlighting */
if($community->rinStyle()) {
    if($single_item_search) {
        $tab = "resource-reports";
    } elseif($search->category == "data") {
        if($search->source) {
            if($is_rrid_view) {
                $tab = "resource-reports";
            } else {
                $tab = "discovery-portal";
            }
        } else {
            $tab = "discovery-portal";
        }
    }
}

if (count($components['breadcrumbs']) > 0 && !$community->rinStyle() && $search->category != 'discovery') {
    $component = $components['breadcrumbs'][0];
    if (!$component->disabled) {
        ob_start();
        include $_SERVER['DOCUMENT_ROOT'] . '/components/body/parts/blocks/breadcrumbs.php';
        $breadcrumb_component = ob_get_clean();
    }
}

?>

<?php
    if ( substr($search->source,0,11) == "scr_017041-" || substr($search->source,0,11) == "SCR_017041-" ) {
        if ( $community->shortName == "SPARC" ) {

        }
        if ( $community->shortName != "SPARC" ) {

echo("<!DOCTYPE html>");
echo("<html>");
echo("  <head>");
echo("    <meta http-equiv=\"Refresh\" content=\"7; url=https://scicrunch.org/sparc/data/source/SCR_017041-1/search?q=\" />");
echo("  </head>");
echo("  <body>");
echo("    <p><H1>You are being re-directed to a SPARC community dataset. Please follow <a href=\"https://scicrunch.org/sparc/data/source/SCR_017041-1/search?q=\"\
>this link if you are not automatically re-directed</a>.</H1></p>");
echo("  </body>");
echo("</html>");

exit;
        }
    }
?>

<?php
    if ( substr($search->source,0,11) == "nlx_152175-" ) {
        if ( $community->shortName == "SPARC" ) {

        }
        if ( $community->shortName != "SPARC" ) {

echo("<!DOCTYPE html>");
echo("<html>");
echo("  <head>");
echo("    <meta http-equiv=\"Refresh\" content=\"7; url=https://scicrunch.org/sparc/data/source/nlx_152175-1/search?q=\" />");
echo("  </head>");
echo("  <body>");
echo("    <p><H1>You are being re-directed to a SPARC community dataset. Please follow <a href=\"https://scicrunch.org/sparc/data/source/nlx_152175-1/search?q=\"\
>this link if you are not automatically re-directed</a>.</H1></p>");
echo("  </body>");
echo("</html>");

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
  <?php ## modified resource report title -- Vicky-2019-1-16 ?>
  <?php if(strpos($theTitle, " (SCR_") !== false || $theTitle == " ()"): ?>
      <title><?php echo $community->shortName ?> | Resource Report (
      <?php if($vars["view"] != "protocol"): ?>
          RRID:
      <?php endif ?>
      <?php echo str_replace('$U+002F;', '/', $vars["rrid"]) ?>)</title>
  <?php else: ?>
      <title><?php echo $community->shortName ?> | <?php echo $theTitle ?></title>
  <?php endif ?>

    <!-- Meta -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo \helper\metaDescription($meta_description) ?>">
    <meta name="author" content="">
    <meta name="google-site-verification" content="vhe7FXQ5uQHNwM10raiS4rO23GgbFW6-iyRfapxGPJc" />

    <!-- Favicon -->
    <link rel="shortcut icon" href="/favicon.ico">

    <!-- CSS Global Compulsory -->
    <link rel="stylesheet" href="/css/main.css">
    <link rel="stylesheet" href="/assets/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/plugins/sky-forms/version-2.0.1/css/custom-sky-forms.css">
    <link rel="stylesheet" href="/assets/css/style.css">

    <!-- CSS Implementing Plugins -->
    <link rel="stylesheet" href="/assets/plugins/line-icons/line-icons.css">
    <link rel="stylesheet" href="/assets/plugins/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="/assets/plugins/owl-carousel/owl-carousel/owl.carousel.css">

    <!-- CSS Page Style -->
    <link rel="stylesheet" href="/assets/css/pages/page_search_inner.css">
    <link rel="stylesheet" href="/assets/css/pages/page_search_inner_tables.css">
    <link rel="stylesheet" href="/assets/plugins/scrollbar/src/perfect-scrollbar.css">

    <!-- CSS Theme -->
    <link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">
    <link rel="stylesheet" href="/assets/css/pages/page_log_reg_v2.css">
    <link rel="stylesheet" href="/assets/css/shop/shop.blocks.css">

    <!-- CSS Customization -->
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/css/community-search.css">
    <link rel="stylesheet" href="/css/joyride-2.0.3.css">
    <link rel="stylesheet" href="/js/Highcharts-6.0.7/code/css/highcharts.css">

    <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="/js/main.js"></script>
    <script src="/js/angular-1.7.9/angular.min.js"></script>
    <script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
    <script src="/js/angular-1.7.9/angular-sanitize.js"></script>
    <script src="/js/module-error.js"></script>
    <script src="/js/module-resource.js"></script>

    <!-- loading screen -->
    <style>
        #loading {
            background: #F7F7FF;
            display: none;
        }

        .page-overlay {
            width: 100%;
            height: 100%;
            z-index: 1000;
            top: 0;
            left: 0;
            position: fixed;
        }

        #restarting-warning {
            background: #FFF7F7;
            display: none;
        }

        #try-again-later-warning {
            display: none;
        }

        .center-style-1 {
            display: table;
            position: absolute;
            height: 100%;
            width: 100%;
        }
        .center-style-2 {
            display: table-cell;
            vertical-align: middle;
        }
        .center-style-3 {
            margin-left: auto;
            margin-right: auto;
        }
    </style>
    <script>
        window.page_loaded = false;
        function pageLoad() {
            document.getElementById("loading").style.display = "none";
            document.getElementById("full-page").style.display = "block";
            window.page_loaded = window.okSearchStatus;
            clearTimeout(window.reloadTimeout);
            checkReloadSearchPage();
        }
        function checkReloadSearchPage() {
            document.getElementById("loading").style.display = "none";
            document.getElementById("full-page").style.display = "block";
            if(!window.page_loaded) {
                if(window.location.search.indexOf("restarted") === -1) {
                    document.getElementById("restarting-warning").style.display = "block";
                    setTimeout(function() {
                        var sep = window.location.search.indexOf("?") === -1 ? "?" : "&";   // check if other get query params exist
                        window.location = window.location.href + sep + "restarted";
                    }, 5000);
                } else {
                    document.getElementById("try-again-later-warning").style.display = "block";
                }
            }
        }
        window.reloadTimeout = setTimeout(checkReloadSearchPage, 65000);
    </script>
</head>

<body onload="pageLoad()">
<?php echo \helper\topPageHTML(); ?>
<div id="loading" class="page-overlay">
    <div class="center-style-1">
        <div class="center-style-2">
            <div class="center-style-3">
                <center>
                    <h2>Searching across hundreds of databases <i class="fa fa-cog fa-spin"></i></h2>
                    <img src="/images/scicrunch.png" style="height: 100px" />
                </center>
            </div>
        </div>
    </div>
</div>
<div id="restarting-warning" class="page-overlay">
    <div class="center-style-1">
        <div class="center-style-2">
            <div class="center-style-3">
                <center>
                    <h2>Our searching services are busy right now.  Your search will reload in five seconds.</h2>
                </center>
            </div>
        </div>
    </div>
</div>
<div id="try-again-later-warning" class="alert alert-warning" role="alert">
    Our searching services are busy right now.  Please try again later.
</div>
<script>document.getElementById("loading").style.display = "block";</script>

<div id="full-page">
<script>document.getElementById("full-page").style.display = "none";</script>
<?php
// flush so that loading screen can be pushed before the time consuming search

if (!(($search->category == 'literature') && (isset($vars['pmid'])))){
    // not searching for a literature with pmid
    ob_flush();
    flush();
}


// run the search
Search::newRecentSearch($vars, $community->id);
if(!$single_item_search && !isset($vars["pmid"]) && (!$is_rrid_view || !$community->rinStyle())) {
    error_log("before doSearch *********************************************************************",0); // Manu	
    $results = $search->doSearch();
}
?>
<script>
    window.okSearchStatus =
        <?php
            echo ($results["return_status_code"] >= 400 || $results["return_status_code"] < 200) &&
                 ($results && !$single_item_search && !isset($vars["pmid"])) ? "false" : "true"
        ?>
</script>
<?php
if ($search->fullscreen) {
    include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/full.table.view.php';
} else {
    ?>
    <div class="wrapper-background" style="overflow:auto <?php if ($vars['editmode']) echo ' margin-top:32px;' ?>">
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

        if ($single_item_search) {
            if($community->rinStyle()) {
                echo \helper\htmlElement("rin/search-single-item", Array(
                    "view" => $vars["view"],
                    "rrid" => $vars["rrid"],
                    "tab" => $vars["resolvertab"],
                    "community" => $community,
                ));
            } else {
                include $_SERVER["DOCUMENT_ROOT"] . "/communities/ssi/single-item.php";
            }
            $ga_suffix = "single-item";
        } elseif ($search->category == 'data') {
            if ($search->source) {
                if($is_rrid_view && $community->rinStyle()) {
                    if($vars["table-view"] || isset($_COOKIE["search-table-view"])) {
                        echo \helper\htmlElement("rin/search-table", Array(
                            "search" => $search,
                            "nifid" => $vars["nif"],
                            "vars" => $vars,
                            "community" => $community,
                            "user" => $_SESSION["user"],
                            "results" => $results,
                            "recent-searches" => $_SESSION["recent-searches"],
                        ));
                        $ga_suffix = "table";
                    } else {
                        echo \helper\htmlElement("rin/search-resource-snippet-view", Array(
                            "search" => $search,
                            "nifid" => $vars["nif"],
                            "vars" => $vars,
                            "community" => $community,
                            "user" => $_SESSION["user"],
                            "results" => $results,
                            "recent-searches" => $_SESSION["recent-searches"],
                        ));
                        $ga_suffix = "snippet-single-view";
                    }
                } else {
                    ob_start();
                    include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/table.view.php';
                    $html_body = ob_get_clean();
                    if($community->rinStyle()) {
                        $title = "";
                        if(isset($sources[$vars["nif"]])) {
                            $title = $sources[$vars['nif']]->getTitle();
                        }
                        $rin_data = Array(
                            "title" => $title,
                            "html-body" => $html_body,
                            "breadcrumbs" => Array(
                                Array("text" => "Home", "url" => $community->fullURL()),
                                Array("text" => "Discovery Portal", "url" => $community->fullURL() . "/data/search"),
                                Array("text" => $title, "active" => true),
                            ),
                        );
                        echo \helper\htmlElement("rin-style-page", $rin_data);
                    } else {
                        echo $breadcrumb_component;
                        /*echo \helper\htmlElement("components/search-block-slim", Array(
                            "community" => $community,
                            "user" => $_SESSION["user"],
                            "vars" => $vars,
                            "search" => $search,
                            "expansion" => $results["expansion"],
                        ));*/
                        echo $html_body;
                    }
                    $ga_suffix = "table";
                }
            } else {
                if(isset($_COOKIE["old-interface-data"])) {
                    include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/data.view.php';
                    $ga_suffix = "data-v1";
                } else {
                    ob_start();
                    include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/data-v2.view.php';
                    $html_body = ob_get_clean();
                    if($community->rinStyle()) {
                        $rin_data = Array(
                            "title" => "Discovery Portal",
                            "html-body" => $html_body,
                            "breadcrumbs" => Array(
                                Array("text" => "Home", "url" => $community->fullURL()),
                                Array("text" => "Discovery Portal", "active" => true),
                            ),
                        );
                        echo \helper\htmlElement("rin-style-page", $rin_data);
                    } else {
                        echo $breadcrumb_component;
                        echo '<div class="container">' . $html_body . '</div>';
                    }
                    $ga_suffix = "data-v2";
                }
            }
        } elseif ($search->category == 'discovery') {
            if ($search->subcategory == 'knowledge-base') {
                if(isset($vars['id'])) {
                    echo \helper\htmlElement("discovery/discovery-search-single-item", Array(
                        "view" => $vars["nif"],
                        "itemID" => $vars["id"],
                        "vars" => $vars,
                        // "tab" => $vars["resolvertab"],
                        "community" => $community,
                    ));
                    $ga_suffix = "single-item";
                }
            } elseif ($search->source) {
                if($is_rrid_view || $is_source_view) {
                    if(!$community->rinStyle()) {
                        $data_sources_list = file_get_contents("ssi/elements/discovery/json/discovery_data_sources.json");
                        $data_sources = json_decode($data_sources_list, true);
                        $data_sources_list = file_get_contents("ssi/elements/discovery/json/index.json");
                        $data_sources += json_decode($data_sources_list, true);

                        if($search->source == "all")
                            echo Connection::createBreadCrumbs('Discovery Sources',array('Home'),array('/'.$community->portalName),'Discovery Sources');
                        else if(strpos($search->source, ',') !== false)
                            echo Connection::createBreadCrumbs("Multiple Sources",array('Home', 'Discovery Sources'),array('/'.$community->portalName, '/'.$community->portalName . '/discovery/source/all/search'),"Multiple Sources");
                        else
                            echo Connection::createBreadCrumbs($data_sources[$search->source]['plural_name'],array('Home', 'Discovery Sources'),array('/'.$community->portalName, '/'.$community->portalName . '/discovery/source/all/search'),$data_sources[$search->source]['plural_name']);
                    }

                    if($vars["table-view"] || isset($_COOKIE["search-table-view"]) || isset($_GET["table"])) {
                        echo \helper\htmlElement("discovery/discovery-search-table", Array(
                            "search" => $search,
                            "nifid" => $vars["nif"],
                            "vars" => $vars,
                            "community" => $community,
                            "user" => $_SESSION["user"],
                            "results" => $results,
                            "recent-searches" => $_SESSION["recent-searches"],
                        ));
                        $ga_suffix = "table";
                    } else if(isset($_COOKIE["search-discovery-view"])) {
                        $nifid = $vars["nif"];

                        echo \helper\htmlElement("discovery/discovery-search-discovery-view", Array(
                            "search" => $search,
                            "nifid" => $nifid,
                            "vars" => $vars,
                            "community" => $community,
                            "user" => $_SESSION["user"],
                            "results" => $results,
                            "recent-searches" => $_SESSION["recent-searches"],
                        ));
                        $ga_suffix = "discovery-view";
                    }else {
                        echo \helper\htmlElement("discovery/discovery-search-snippet-view", Array(
                            "search" => $search,
                            "nifid" => $vars["nif"],
                            "vars" => $vars,
                            "community" => $community,
                            "user" => $_SESSION["user"],
                            "results" => $results,
                            "recent-searches" => $_SESSION["recent-searches"],
                        ));
                        $ga_suffix = "snippet-single-view";
                    }
                } else {
                    ob_start();
                    include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/table.view.php';
                    $html_body = ob_get_clean();
                    if($community->rinStyle()) {
                        $title = "";
                        if(isset($sources[$vars["nif"]])) {
                            $title = $sources[$vars['nif']]->getTitle();
                        }
                        $rin_data = Array(
                            "title" => $title,
                            "html-body" => $html_body,
                            "breadcrumbs" => Array(
                                Array("text" => "Home", "url" => $community->fullURL()),
                                Array("text" => "Discovery Portal", "url" => $community->fullURL() . "/data/search"),
                                Array("text" => $title, "active" => true),
                            ),
                        );
                        echo \helper\htmlElement("rin-style-page", $rin_data);
                    } else {
                        echo $breadcrumb_component;
                        /*echo \helper\htmlElement("components/search-block-slim", Array(
                            "community" => $community,
                            "user" => $_SESSION["user"],
                            "vars" => $vars,
                            "search" => $search,
                            "expansion" => $results["expansion"],
                        ));*/
                        echo $html_body;
                    }
                    $ga_suffix = "table";
                }
            } else {
                if(isset($_COOKIE["old-interface-data"])) {
                    include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/data.view.php';
                    $ga_suffix = "data-v1";
                } else {
                    ob_start();
                    include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/data-v2.view.php';
                    $html_body = ob_get_clean();
                    if($community->rinStyle()) {
                        $rin_data = Array(
                            "title" => "Discovery Portal",
                            "html-body" => $html_body,
                            "breadcrumbs" => Array(
                                Array("text" => "Home", "url" => $community->fullURL()),
                                Array("text" => "Discovery Portal", "active" => true),
                            ),
                        );
                        echo \helper\htmlElement("rin-style-page", $rin_data);
                    } else {
                        echo $breadcrumb_component;
                        echo '<div class="container">' . $html_body . '</div>';
                    }
                    $ga_suffix = "data-v2";
                }
            }
        } elseif ($search->category == 'literature') {
            if(isset($vars['pmid'])) {
                $html_body = \helper\htmlElement("search-paper-view", Array(
                    "community" => $community,
                    "vars" => $vars,
                    "server_https" => isset($_SERVER["https"]),
                    "search" => $search,
                ));
                if($community->rinStyle()) {
                    $rin_data = Array(
                        "title" => "PMID:" . $vars["pmid"],
                        "breadcrumbs" => Array(
                            Array("text" => "Home", "url" => $community->fullURL()),
                            Array("text" => "Discovery Portal", "url" => $community->fullURL() . "/data/search"),
                            Array("text" => "Literature", "url" => $community->fullURL() . "/literature/search"),
                            Array("text" => "PMID:" . $vars["pmid"], "active" => true),
                        ),
                        "html-body" => $html_body,
                    );
                    echo \helper\htmlElement("rin-style-page", $rin_data);
                } else {
                    echo $breadcrumb_component;
                    echo '<div class="container">' . $html_body . '</div>';
                }
                $ga_suffix = "paper";
            } else {
                $html_body = \helper\htmlElement("search-literature-view", Array(
                    "search" => $search,
                    "vars" => $vars,
                    "results" => $results,
                    "recent-searches" => $_SESSION["recent-searches"],
                    "community" => $community,
                    "lit-refs" => $lit_refs,
                    "user" => $_SESSION["user"],
                ));
                if($community->rinStyle()) {
                    $rin_data = Array(
                        "title" => "Literature",
                        "breadcrumbs" => Array(
                            Array("text" => "Home", "url" => $community->fullURL()),
                            Array("text" => "Discovery Portal", "url" => $community->fullURL() . "/data/search"),
                            Array("text" => "Literature", "active" => true),
                        ),
                        "html-body" => $html_body,
                    );
                    echo \helper\htmlElement("rin-style-page", $rin_data);
                } else {
                    echo $breadcrumb_component;
                    echo '<div class="container">' . $html_body . '</div>';
                }
                $ga_suffix = "literature";
            }
        } elseif ($search->source) {
            ob_start();
            include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/table.view.php';
            $html_body = ob_get_clean();
            if($community->rinStyle()) {
                $title = "";
                if(isset($sources[$vars["nif"]])) {
                    $title = $sources[$vars['nif']]->getTitle();
                }
                $rin_data = Array(
                    "title" => $title,
                    "html-body" => $html_body,
                    "breadcrumbs" => Array(
                        Array("text" => "Home", "url" => $community->fullURL()),
                        Array("text" => "Discovery Portal", "url" => $community->fullURL() . "/data/search"),
                        Array("text" => $title, "active" => true),
                    ),
                );
                echo \helper\htmlElement("rin-style-page", $rin_data);
            } else {
                echo $breadcrumb_component;
                /*echo \helper\htmlElement("components/search-block-slim", Array(
                    "community" => $community,
                    "user" => $_SESSION["user"],
                    "vars" => $vars,
                    "search" => $search,
                    "expansion" => $results["expansion"],
                ));*/
                echo $html_body;
            }
            $ga_suffix = "table";
        } else {
            echo $breadcrumb_component;
            if(isset($_COOKIE["old-interface-resources"])) {
                include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/resource.view.php';
                $ga_suffix = "resource-v1";
            } else {
                echo \helper\htmlElement("components/search-block-slim", Array(
                    "community" => $community,
                    "user" => $_SESSION["user"],
                    "vars" => $vars,
                    "search" => $search,
                    "expansion" => $results["expansion"],
                ));
                include $_SERVER['DOCUMENT_ROOT'] . '/communities/ssi/resource.view.php';
                $ga_suffix = "resource-v2";
            }
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
                                       value="<?php echo $search->category ?>">
                                <input type="text" disabled="disabled" placeholder="Focus to view the tooltip"
                                       value="<?php if ($search->category == 'data') echo 'More Resources'; else echo $search->category ?>">
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

        <!--Recommendations-->
        <input type="hidden" value="<?php echo ($community->portalName) ? ($community->portalName) : 'scicrunch'?>" id="community-portal">
        <recommendations-chip>
        </recommendations-chip>
        <script type="text/javascript" src="/js/recommendations.js"></script>
    </div>
<?php } ?>
<!--/End Wrapepr-->

<!-- JS Global Compulsory -->
<script type="text/javascript" src="/assets/plugins/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="/assets/plugins/jquery-ui.min.js"></script>
<script type="text/javascript" src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/js/jquery.truncate.js"></script>
<script src="/assets/plugins/scrollbar/src/jquery.mousewheel.js"></script>
<script src="/assets/plugins/scrollbar/src/perfect-scrollbar.js"></script>
<script src="/assets/plugins/summernote/summernote.js"></script>
<script type="text/javascript" src="/js/extended-circle-master.js"></script>
<script type="text/javascript" src="/js/circle-master.js"></script>
<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=true"></script>

<script type="text/javascript" src="/assets/plugins/gmap/gmap.js"></script>
<script src="/assets/plugins/sky-forms/version-2.0.1/js/jquery.validate.min.js"></script>
<script type='text/javascript' src='https://d1bxh8uas1mnw7.cloudfront.net/assets/embed.js'></script>

<script type="text/javascript" src="/assets/plugins/d3/d3.min.js"></script>
<script type="text/javascript" src="/js/d3.tip.js"></script>
<script type="text/javascript" src="/js/graph.js"></script>
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-580942bee4418f97"></script>
<script type="text/javascript" src="/assets/plugins/owl-carousel/owl-carousel/owl.carousel.js"></script>
<script type="text/javascript" src="/assets/js/plugins/owl-carousel.js"></script>
<!-- JS Implementing Plugins -->
<?php



if (!$search->fullscreen) {
    ?>
    <script type="text/javascript" src="/assets/plugins/back-to-top.js"></script>
<?php } ?>
<!-- JS Page Level -->
<script type="text/javascript" src="/assets/js/app.js"></script>

<script type="text/javascript" src="/js/jquery.joyride-2.0.3.js"></script>
<script type="text/javascript" src="/js/Highcharts-6.0.7/code/js/highcharts.js"></script>
<script type="text/javascript" src="/js/Highcharts-6.0.7/code/js/modules/series-label.js"></script>
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
    $('.tutorial-btn').click(function () {
        $("body").joyride();
        $("body").joyride("destroy");   // joyride bug, when multiple joyrides on a page
        $('.joyride-next-tip').show();
        $('#joyRideTipContent').joyride({postStepCallback: function (index, tip) {

        }, 'startOffset': 0, 'tip_class': false});
    });
    $('.inner-results p').truncate({max_length: 500});
    $('.search-table-record-td').truncate({max_length: 200});
    $(window).scroll(function (event) {
        $(".fixed-header").css("margin-left", -$(document).scrollLeft());
    });
    $('.full-side-close').click(function () {
        $('.full-screen-left').hide();
        $('.full-screen-closed').show();
        $('.fixed-header').css('left', '40px');
        $('.full-screen-right').css('margin-left', '40px');
    });
    $('.full-side-open').click(function () {
        $('.full-screen-closed').hide();
        $('.full-screen-left').show();
        $('.fixed-header').css('left', '260px');
        $('.full-screen-right').css('margin-left', '260px');
    });
</script>
<script type="text/javascript">
    $('#column-select').change(function () {
        var column = $('th[column="' + $(this).val() + '"');
        var position = column.position();

        window.scrollTo(position.left, window.y);

        $(column).css('background', '#ffd9d9');
        setTimeout(function () {
            $(column).css('background', '#d9d9f2');
        }, 2000)
    });
</script>
<script>
    $('.truncate-desc').truncate({max_length: 500});
    $('.truncate-column').truncate({max_length: 200});
    $('.truncate-small').truncate({max_length: 50});
    $('.truncate-medium').truncate({max_length: 150});
    $('.truncate-long').truncate({max_length: 300});
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
        GATiming("community-search-<?php echo $ga_suffix ?>");
        ga('send', 'event', "search-page", "<?php echo $ga_suffix ?>");
    });
</script>
<!--[if lt IE 9]>
<script src="/assets/plugins/respond.js"></script>
<script src="/assets/plugins/html5shiv.js"></script>
<![endif]-->
</div>


<?php

if($c_pop_up_flag)
    echo \helper\htmlElement("community-pop-up", $c_pop_up_array_f);
else
    echo \helper\htmlElement("community-pop-up", $c_pop_up_array_t);

?>


</body>
</html>
<?php

function scicrunchRegSort(&$vars, $community){
    // sets settings so that resource_name is automatically sorted only when scicrunch registry is the only resource and there was no search query
    if($vars['q'] != '*' || $vars['sort'] || $vars['nif'] != "") return;
    $scicrunch_nif = "nlx_144509-1";
    $resource = $community->urlTree[$vars['category']];
    $used_resource = $vars['subcategory'] ? $resource['subcategories'][$vars['subcategory']] : $resource;	// use subcategory if it was queried
    if(count($used_resource['nif']) > 1 || $used_resource['nif'][0] != $scicrunch_nif) return;

    $vars['sort'] = "asc";
    $vars['column'] = 'Proper Citation';
}

function notificationData($get) {
    $return_data = Array();
    $GLOBALS["notif_id"] = isset($get["notif"]) ? \helper\aR($get["notif"], "s") : NULL;
    $GLOBALS["notif_email"] = isset($get["notif_email"]);
    if(!isset($get["notif"])) return NULL;
    $subscription = Subscription::loadBy(Array("id"), Array($GLOBALS["notif_id"]));
    Subscription::clearNotification($GLOBALS["notif_id"], $_SESSION["user"]);
    if(is_null($subscription) || ($subscription->type !== "saved-search-data" && $subscription->type !== "saved-search-summary")) return NULL;
    $data = $GLOBALS["notif_email"] ? $subscription->getNewDataEmail() : $subscription->getNewDataScicrunch();
    if($subscription->type === "saved-search-data") {
        $return_data["modified_time"] = $data;
    } elseif($subscription->type === "saved-search-summary") {
        $return_data = $data;
    }
    if(($get["category"] !== "data" || !!$get["nif"]) && $get["category"] !== "literature") {
        $return_data["type"] = "filter-time";
    } elseif($get["category"] === "data") {
        $return_data["type"] = "filter-nif";
    } else {
        $return_data["type"] = "none";
    }
    return $return_data;
}

function getLiteratureReferencesData($litref, $community) {
    // split to get [0] => viewid [1] => uuid and [2] => reference-column
    $litref_split = explode(":", $litref);
    if(count($litref_split) != 3) return NULL;
    $viewid = $litref_split[0];
    $uuid = $litref_split[1];
    $refcol = $litref_split[2];

    // run the single view search
    $search = new BaseSearch(Array("filters" => Array("v_uuid" => $uuid), "source" => Array($viewid)));
    $results = $search->doSearch("single_source");

    $return_array = Array();
    $return_array["pmids"] = getLiteratureReferencesPMIDs($viewid, $uuid, $refcol, $results);
    $return_array["view_snippet"] = getLiteratureReferencesViewSnippet($community, $viewid, $uuid, $results);

    return $return_array;
}

function getLiteratureReferencesPMIDs($viewid, $uuid, $refcol, $services_results) {
    // get the references
    if($services_results["count"] != 1) return Array();
    if(!isset($services_results["results"][0][$refcol])) return Array();
    $references = strip_tags($services_results["results"][0][$refcol]);

    // regex match to references
    $matches = Array();
    preg_match_all("/PMID:(\d+)\b/i", $references, $matches);
    $pmids = $matches[1];

    return $pmids;
}

function getLiteratureReferencesViewSnippet($community, $viewid, $uuid, $services_results) {
    $snippet = new Snippet();
    $snippet->getSnippetByView($community->id, $viewid);
    $snippet->resetter();
    foreach($services_results["results"][0] as $key => $val) {
        $snippet->replace($key, $val);
    }
    $snippet->splitParts();
    $real_snippet = $snippet->snippet;
    if(strpos($real_snippet["title"], "href") === false) $real_snippet["title"] = strip_tags($real_snippet["title"]);
    return $real_snippet;
}

?>
