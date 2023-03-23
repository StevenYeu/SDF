<?php

include 'process-elastic-search.php';

ob_start();

$tab = 0;
$hl_sub = -4;
$page = filter_var($_GET['page'], FILTER_SANITIZE_NUMBER_INT);
$rid = filter_var($_GET['id'], FILTER_SANITIZE_STRING);
$docID = $_GET["i"];

if(\helper\startsWith(strtolower($rid), "pmid:")) {
    $pmid_url = "/" . str_replace("pmid_", "", str_replace("pmid:", "", strtolower($rid)));
    header("location: " . $pmid_url);
    exit;
}

if(\helper\endsWith($rid, ".xml") || \helper\endsWith($rid, ".XML")) $xml_type =true;
if(\helper\endsWith($rid, ".json") || \helper\endsWith($rid, ".JSON")) $json_type = true;

if($xml_type && $rid){
    $rid = substr($rid, 0, strlen($rid) - 4);
}

if($json_type && $rid){
    $rid = substr($rid, 0, strlen($rid) - 5);
}

$rrid_mapping = RRIDMap::loadBy(Array("issued_rrid", "active"), Array($rid, 1));
if(!is_null($rrid_mapping)){
    $issued_rrid = $rid;
    $rid = $rrid_mapping->replace_by;
}

if (!$page)
    $page = 1;

$direct_flag = false;
if(isset($_GET["direct"])) {
    $direct_flag = true;
}

$no_direct_flag = false;
if(isset($_GET["nodirect"]) || (isset($_GET["i"]) && $docID != "")) {
    $no_direct_flag = true;
}

$protocol_flag = false;
if(strpos($rid, '$U+002F;') !== false) $protocol_flag = true;

if(isset($_GET["mentions"])) {
    $tab = "mentions";
}else if(isset($_GET["co-mentions"])) {
    $tab = "co-mentions";
}else {
    $tab = "info";
}

$query = filter_var(rawurldecode($_GET['query']), FILTER_SANITIZE_STRING);
if (!$query) {
    $query = '*';
}
$holder = new Component();
$components = $holder->getByCommunity(0);

$vars['editmode'] = filter_var($_GET['editmode'], FILTER_SANITIZE_STRING);
if ($vars['editmode']) {
    if (!isset($_SESSION['user']) || $_SESSION['user']->role < 1)
        $vars['editmode'] = false;
}

$vars['errorID'] = filter_var($_GET['errorID'], FILTER_SANITIZE_NUMBER_INT);
if ($vars['errorID']) {
    $errorID = new Error();
    $errorID->getByID($vars['errorID']);
    if (!$errorID->id) {
        $errorID = false;
    }
}

$query = trim(str_replace('RRID:', '', $rid));

// $badcurie = 'IMSR_';
// $curiepos = stripos($query,$badcurie);  #Find the position of the first occurrence of $badcurie inside the string ($query)
// if ($curiepos === false) {
//     $query = str_replace(':', '_', $query);
// }
if(\helper\startsWith($query, "IMSR_MMRRC")) $query = str_replace("IMSR_", "", $query);
if(\helper\startsWith($query, "WB_")) $query = str_replace("WB_", "WB-STRAIN_", $query);
if(\helper\startsWith($query, "WB:")) $query = str_replace("WB:", "WB-STRAIN_", $query);
if(\helper\startsWith($query, "CGC_")) $query = str_replace("CGC_", "WB-STRAIN_", $query);
// $display_query = str_replace("MGI_", "MGI:", $query);
if(strpos($query, ")") !== false) $query = str_replace(")", "", $query);
$rrid = $query;

if($protocol_flag) {
    $view = "protocol";
    if($xml_type || $json_type) {
        $search_manager = ElasticRRIDManager::managerByViewID("protocol");
        $query = str_replace('$U+002F;', '/', $query);
        $results = $search_manager->searchResolverDOI($query);
    }
} else {
    $views = Array(
        "antibody" => "nif-0000-07730-1",
        "resource" => "nlx_144509-1",
        "tool" => "nlx_144509-1",
        "organization" => "nlx_144509-1",
        "cell line" => "SCR_013869-1",
        "organism" => "nlx_154697-1",
        "plasmid" => "nif-0000-11872-1",
        "biosample" => "nlx_143929-1",
    );

    $types = Array(
        "antibody" => Array(
                          "AB",
                      ),
        "Resource" => Array(
                          "SCR",
                      ),
        "cell line" => Array(
                          "CVCL",
                      ),
        "organism" => Array(
                        "IMSR",
                        "MGI",
                        "WB-STRAIN",
                        "BDSC",
                        "MMRRC",
                        "FlyBase",
                        "ZIRC",
                        "ZFIN",
                        "DGGR",
                        "RGD",
                        "TSC",
                        "NXR",
                        "XGSC",
                        "BCBC",
                        "NSRRC",
                        "SSCLBR",
                        "AGSC",
                        "EXRC",
                        "XEP",
                        "CWRU",
                    ),
        "plasmid" => Array(
                        "Addgene",
                    ),
        "biosample" => Array(
                          "SAMN",
                      ),
    );
    $viewid = "";

    foreach ($types as $type => $list) {
        foreach ($list as $item) {
            if(\helper\startsWith($query, $item)) {
                $viewid = $views[strtolower($type)];
                break;
            }
        }
        if($viewid != "") break;
    }

    $search_manager = ElasticRRIDManager::managerByViewID($viewid);

    if(is_null($search_manager)) {
        if($viewid == "") return;
        else {
            $search_manager = ElasticRRIDManager::managerByViewID("");
            if(is_null($search_manager)) return;
        }
    }
    $results = $search_manager->searchResolverRRID($query);
    $results_count = $results["es"]->hitCount();

    $resolver_error = false;
    if($results_count == 0) {
        $resolver_error = true;
        // echo \helper\errorPage("noresource", NULL, false);
        // return;
    } else if(!$protocol_flag) {

        $result = findResult($results["es"], $docID);
        $docID = $result->getRRIDField("id");
        $view = $views[strtolower(explode(", ", $result->getRRIDField("type"))[0])];
        $rrid = str_replace("RRID:", "", $result->getRRIDField("curie"));

        $actual_link = "{$_SERVER['REQUEST_URI']}";   //get url without home
        if(strpos($actual_link, "?")) $actual_query = "&".explode("?", $actual_link)[1];
        else $actual_query = "";

        $rrid_url = "";
        if(!$xml_type && !$json_type) {
            if(\helper\startsWith($actual_link, "/dknetbeta")) {
                $rrid_url = "/dknetbeta/data/record/$view/$rrid/resolver?q=$rrid&l=$rrid" . $actual_query;
            } else if(\helper\startsWith($actual_link, "/dknet")) {
                $rrid_url = "/dknet/data/record/$view/$rrid/resolver?q=$rrid&l=$rrid" . $actual_query;
            } else if(!$no_direct_flag && ($direct_flag || RRIDPrefixRedirect::shouldRedirect($view, $rrid))) {
                $rrid_url = $result->getRRIDField("url");
                include 'ssi/pages/external-view.php';
                exit;
            }

            if($rrid_url != "") {
                header("location: " . $rrid_url);
                exit;
            }
        }

        if (strtolower($query) != strtolower($rrid) && $query != "") {
            $_SESSION["resolver_alternated"] = [true, $query];
            if($xml_type) header("location:/resolver/" . $rrid . ".xml", true, "301");
            else if($json_type) header("location:/resolver/" . $rrid . ".json");
            else header("location:/resolver/" . $rrid);
            exit;
        }
    }
}

## added status header for json & xml -- Vicky-2019-8-23
if($xml_type || $json_type) {
    if(preg_match("/^[4-5]\d{2}$/", $results["json"]["status"])) header("X-PHP-Response-Code: 500", true, "500");
    //else if($_SESSION["resolver_alternated"][0] == true) header("X-PHP-Response-Code: 301", true, "301");
    else if($resolver_error == true) header("X-PHP-Response-Code: 404", true, "404");
    else header("X-PHP-Response-Code: 200", true, "200");
}

?>
<?php if($xml_type && $rrid): ?>
<?php
    header("Content-type: text/xml");
    include 'ssi/pages/rrid-item.php';
?>
<?php elseif($json_type && $rrid): ?>
<?php
    header("Content-type: application/json");
    include 'ssi/pages/rrid-item.php';
?>
<?php else: ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <title>SciCrunch | Research Resource Resolver</title>

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
            <link rel="stylesheet" href="/assets/css/shop/shop.blocks.css">

            <!-- CSS Theme -->
            <link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">

            <!-- CSS Customization -->
            <link rel="stylesheet" href="/assets/css/custom.css">
            <link rel="stylesheet" href="/js/Highcharts-6.0.7/code/css/highcharts.css">

            <!-- JS Global Compulsory -->
            <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
            <script type="text/javascript" src="/assets/plugins/jquery-migrate-1.2.1.min.js"></script>
            <script type="text/javascript" src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
            <script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=true"></script>

            <script type="text/javascript" src="/assets/plugins/gmap/gmap.js"></script>
            <script type="text/javascript" src="/js/main.js"></script>
            <script type="text/javascript" src="/js/jquery.truncate.js"></script>
            <!-- JS Implementing Plugins -->
            <script type="text/javascript" src="/assets/plugins/back-to-top.js"></script>
            <!-- JS Page Level -->
            <script type="text/javascript" src="/assets/js/app.js"></script>
            <script src='https://www.google.com/recaptcha/api.js'></script>
            <!-- angular -->
            <script src="/js/angular-1.7.9/angular.min.js"></script>
            <script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
            <script src="/js/angular-1.7.9/angular-sanitize.js"></script>
            <script src="/js/module-error.js"></script>
            <script src="/js/resolver.js"></script>

            <script src="/js/module-resource.js"></script>
            <script type="text/javascript" src="/js/Highcharts-6.0.7/code/js/highcharts.js"></script>
            <script type="text/javascript" src="/js/Highcharts-6.0.7/code/js/modules/series-label.js"></script>
	</head>

        <body>
        <?php echo \helper\topPageHTML(); ?>

        <div class="wrapper">
            <!-- Brand and toggle get grouped for better mobile display -->
            <?php include 'ssi/header.php' ?>
            <!--=== Breadcrumbs v3 ===-->
            <?php
                if($rid) {
                    if($tab == "mentions")
                        echo Connection::createBreadCrumbs('Resource Mentions', array('Home', 'Resolver ID Search', 'Resource Report'), array('/scicrunch/', '/resolver/', '/resolver/'.$rid.'?i='.$docID), 'Resource Mentions');
                    else if($tab == "co-mentions")
                        echo Connection::createBreadCrumbs('Resource Co-Mentions', array('Home', 'Resolver ID Search', 'Resource Report', 'Resource Mentions'),
                            array('/scicrunch/', '/resolver/', '/resolver/'.$rid.'?i='.$docID, '/resolver/'.$rid.'/mentions?i='.$docID), 'Resource Co-Mentions');
                    else
                        echo Connection::createBreadCrumbs('Resource Report',array('Home', 'Resolver ID Search'), array('/scicrunch/', '/resolver/'), 'Resource Report');
                }
            ?>
            <!--=== End Breadcrumbs v3 ===-->

            <!--=== Search Block Version 2 ===-->
            <?php if (!$rid && (!$mode || $mode != 'edit')) { ?>
                <div class="search-block-v2" style="padding:30px 0 38px">
                    <div class="container">
                        <div class="col-md-6 col-md-offset-3">
                            <h2>Search again</h2>

                            <form method="get" action="/resolver">
                                <div class="input-group">
                                    <input type="text" class="form-control" name="query"
                                           placeholder="<?php echo $searchText ?>" value="<?php echo $query ?>">
                                    <input type="hidden" name="filter" value="<?php echo $filter ?>"/>
                            <span class="input-group-btn">
                                <button class="btn-u" type="search"><i class="fa fa-search"></i></button>
                            </span>
                                </div>
                            </form>
                            <?php if ($type == 'resources') { ?>
                                <p style="text-align: center;padding-top:10px;margin-bottom: 0">We support boolean queries, use
                                    +,-,<,>,~,* to alter the weighting of terms</p>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            <?php } ?>
            <!--/container-->
            <!--=== End Search Block Version 2 ===-->

                <?php
                if($rrid) {

                    $community = new Community();
                    $community->portalName = "scicrunch";

                    echo \helper\htmlElement("rin/search-single-item", Array(
                        "view" => $view,
                        "rrid" => $rrid,
                        "tab" => $tab,
                        "community" => $community,
                    ));
                    // include 'ssi/pages/rrid-item.php';
                } // else
                    // include 'ssi/pages/rrid-search.php';
                ?>

        </div>
        <!--/End Wrapper-->

        <?php
            $holder = new Component();
            $components = $holder->getByCommunity(0);
            echo Component::footerHTML($components);
        ?>

        <script type="text/javascript">
            jQuery(document).ready(function () {
                App.init();
                <?php if(isset($_SESSION['user'])){?>
                setTimeout(updateLogin, <?php if($_SESSION['user']->last_check > time()) echo 1000*($_SESSION['user']->last_check-time()); else echo 1000 ?>);
                <?php } ?>
            });
            $('.inner-results p').truncate({max_length: 100});
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
                GATiming("resolver-page");
            });
        </script>
        <!--[if lt IE 9]>
        <script src="/assets/plugins/respond.js"></script>
        <script src="/assets/plugins/html5shiv.js"></script>
        <![endif]-->

        </body>
        </html>
    <?php //endif ?>
<?php endif ?>
<?php
echo ob_get_clean();
?>

<?php
    function isResourcePrefix($rid) {
        $rid = trim(str_replace("rrid:", "", strtolower($rid)));
        if(
            \helper\startsWith($rid, "birnlex") ||
            \helper\startsWith($rid, "nif") ||
            \helper\startsWith($rid, "nlx") ||
            \helper\startsWith($rid, "omics") ||
            \helper\startsWith($rid, "rid") ||
            \helper\startsWith($rid, "sciex") ||
            \helper\startsWith($rid, "scires") ||
            \helper\startsWith($rid, "scr")
        ) return true;
        return false;
    }
?>
