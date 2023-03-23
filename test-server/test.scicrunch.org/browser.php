<?php

$type = isset($_GET["type"]) ? filter_var($_GET['type'], FILTER_SANITIZE_STRING) : NULL;
$filter = isset($_GET["filter"]) ? filter_var($_GET['filter'], FILTER_SANITIZE_STRING) : NULL;
$id = isset($_GET["id"]) ? filter_var($_GET['id'], FILTER_SANITIZE_STRING) : NULL;
$vars['article'] = isset($_GET["article"]) ? filter_var($_GET['article'],FILTER_SANITIZE_NUMBER_INT) : NULL;
$vars['use'] = isset($_GET["rse"]) ? filter_var($_GET['use'],FILTER_SANITIZE_STRING) : NULL;
$page = isset($_GET["page"]) ? filter_var($_GET['page'], FILTER_SANITIZE_NUMBER_INT) : NULL;
$mode = isset($_GET["mode"]) ? filter_var($_GET['mode'], FILTER_SANITIZE_STRING) : NULL;
$statusVar = isset($_GET["status"]) ? filter_var($_GET['status'],FILTER_SANITIZE_STRING) : NULL;
$notif = isset($_GET["notif"]) ? filter_var($_GET["notif"], FILTER_SANITIZE_STRING) : NULL;
$notif_email = isset($_GET["notif_email"]) ? true : NULL;


$facets = filter_var_array($_GET['column']);

if(!is_null($notif) && isset($_SESSION["user"])){
    Subscription::clearNotification($notif, $_SESSION["user"]);
}

if (!$page)
    $page = 1;

if ($_GET['query'] == 'undefined') {
   $_GET['query'] = $_GET['l'];

   if (strpos($_GET['query'],'{')) {
       $bracketStartPos = strpos($_GET['query'],'{') + 1;
       $bracketEndPos = strpos($_GET['query'],'}') - 1;

       $_GET['query'] = substr($_GET['query'], $bracketStartPos, ($bracketEndPos - $bracketStartPos + 1));
   }

}

$query = isset($_GET["query"]) ? filter_var(rawurldecode($_GET['query']), FILTER_SANITIZE_STRING) : "";
$display_query = isset($_GET["l"]) ? filter_var(rawurldecode($_GET["l"]), FILTER_SANITIZE_STRING) : $query;
$holder = new Component();

// Manu - modified the below 
//$components = $holder->getByCommunity(0);
$community = new Community();
$community->getByID(56);
$components = $community->components;
error_log("*************************************************** $community->shortName");

$vars['editmode'] = filter_var($_GET['editmode'],FILTER_SANITIZE_STRING);
if($vars['editmode']){
    if(!isset($_SESSION['user']) || $_SESSION['user']->role<1)
        $vars['editmode'] = false;
}

$vars['errorID'] = filter_var($_GET['errorID'],FILTER_SANITIZE_NUMBER_INT);
if($vars['errorID']){
    $errorID = new Error();
    $errorID->getByID($vars['errorID']);
    if(!$errorID->id){
        $errorID = false;
    }
}

if($type == "resources" && strlen($id) > 0){
    include_once $_SERVER["DOCUMENT_ROOT"] . "/browsing/old-resources-redirect.php";
}

$show_sidebar = true;
switch ($type) {
    case 'communities':
        $tab = 2;
        $title = 'Browse Communities';
        $searchText = 'Search for communities related to your interests';
        $hl_sub = 0;
        break;
    case 'resources':
        $tab = 4;
        $title = 'Curator Dashboard';
        $searchText = 'Search for resources that meet your needs';
        $hl_sub = 1;
        $holder = new Resource_Fields();
        $fields = $holder->getPage1();
        break;
    case 'resourcedashboard':
        $tab = 1;
        $title = "Resources Dashboard";
        $searchText = "Search for resources that meet your needs";
        break;
    case 'datadashboard':
        $holder = new Sources();
        $sources = $holder->getAllSources();
        $tab = 5;
        $title = "Data Dashboard";
        $searchText = "Search across " . count($sources) . " data sources";
        break;
    case 'rrid-mentions':
        $tab = 1;
        $title = "RRID Mentions";
        $show_sidebar = false;
        break;
    case 'resourcesedit':
    case 'curator':
        $tab = 1;
        $title = "Edit Resource";
        $searchText = "Search for resources that meet your needs";
        $show_sidebar = false;
        break;
    case 'content':
        if ($filter) {
            switch ($filter) {
                case 'questions':
                    $title = 'Browse Questions';
                    $searchText = 'Search for previously asked questions';
                    break;
                case 'tutorials':
                    $title = 'Browse Tutorials';
                    $searchText = 'Search for tutorials to help you navigate SciCrunch';
                    break;
                default:
                    $holds = new Component();
                    $holds->getPageByType(0, $filter);
                    $title = 'Browse ' . $holds->text1;
                    $searchText = 'Search against all ' . $holds->text1;
                    break;
            }
        } else {
            $title = 'Browse SciCrunch Content';
            $locationPage = '/browse/content';
            $searchText = 'Search across all SciCrunch articles';
        }
        $hl_sub = 2;
        break;
    case 'search':
        $title = "Search";
        $searchText = "Search for anything";
        $show_sidebar = false;
        break;
    case "terminology":
        $title = "SciGraph Search";
        $show_sidebar = false;
        $tab = 0;
        $hl_sub = -5;
        break;
    case "resourcementionupload":
        $title = "Mention upload";
        $show_sidebar = false;
        $tab = 0;
        break;
    default:
        \helper\errorPage("");
}

// make sure user has rights to edit a resource
if($type == "resources"){
    $resource = new Resource();
    $resource->getByRID($id);
    $can_edit_resource = isset($_SESSION['user']) && ($_SESSION['user']->role > 0 || ($_SESSION['user']->id == $resource->uid));
    if($mode == "edit" && !$can_edit_resource){
        $previous_page = $_SERVER['HTTP_REFERER'];
        header("location:" . $previous_page);
        exit;
    }
}


$search_bar_type = "simple";
$search_banner_type = NULL;
if($type == "resourcedashboard"){
    $search_message = "Search for Resources";
    $search_bar_type = "autocomplete";
    $search_banner_type = "resources-dashboard";
}
elseif($type == "datadashboard"){
    $search_message = "Search for Data";
    $search_bar_type = "autocomplete";
    $search_banner_type = "data-dashboard";
} elseif($type == "search"){
    $search_message = "Search Again";
    $search_bar_type = "autocomplete";
    $search_banner_type = "mainpage";
} elseif($type == "terminology" || $type == "resourcementionupload"){
    $search_bar_type = "none";
} elseif($type == "rrid-mentions") {
    $search_bar_type = "none";
} else{
    $search_message = "Search Again";
    $search_action = "/browse/" . $type;
    $query_label = "query";
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
    <title> <?php /* Manu */echo $community->shortName ?>| <?php echo $title ?></title>

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
    <link rel="stylesheet" href="/assets/plugins/owl-carousel/owl-carousel/owl.carousel.css">

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
    <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
</head>

<body>
<?php echo \helper\topPageHTML(); ?>

<div class="wrapper">
    <!-- Brand and toggle get grouped for better mobile display -->
    <?php /* Manu include 'ssi/header.php' */ ?>
    <?php
       //Header
       include "components/header/header-normal.php"
     ?>

    <!--=== Breadcrumbs v3 ===-->
    <div class="breadcrumbs-v3">
        <div class="container">
            <ul class="pull-left breadcrumb">
	    <li><a href="/<?php /* Manu */echo $community->shortName ?>">Home</a></li>
                <?php if ($id) { ?>
                    <li><a href="/<?php /* Manu */echo $community->shortName ?>/browse/<?php echo $type ?>"><?php echo $title ?></a></li>
                    <li class="active">View Resource</li>
                <?php } else { ?>
                    <li class="active"><?php echo $title ?></li>
                <?php } ?>
            </ul>
            <h1 class="pull-right"><?php echo $title ?></h1>
        </div>
    </div>
    <!--=== End Breadcrumbs v3 ===-->

    <?php echo \helper\htmlElement("browse-search-bar", Array(
        "id" => $id,
        "mode" => $mode,
        "search_bar_type" => $search_bar_type,
        "search_message" => $search_message,
        "search_action" => $search_action,
        "query_label" => $query_label,
        "searchText" => $searchText,
        "query" => $query,
        "filter" => $filter,
        "search_banner_type" => $search_banner_type,
        "docroot" => $GLOBALS["DOCUMENT_ROOT"],
        "type" => $type,
        "display_query" => $display_query,
    )); ?>

    <!--=== Search Results ===-->
    <div class="container s-results margin-bottom-50">
        <div class="row">

            <?php if (!$id && (!$mode || $mode != 'edit') && $show_sidebar){ ?>
            <div class="col-md-2 hidden-xs related-search">
                <div class="row">
                    <?php if($type=='resources'){?>
                    <div class="col-md-12 col-sm-4">
                        <h3>Curation Status</h3>
                        <ul class="list-unstyled">
                            <?php
                            $holder = new Connection();
                            $holder->connect();
                            $curate = $holder->select('resources',array('status','count(status)'),null,array(),'where status is not null group by status order by status asc');
                            $holder->close();
                            foreach ($curate as $row) {
                                if($statusVar && $statusVar==$row['status'])
                                    echo '<li class="active"><a href="/browse/resources?status=' . $row['status'] . '">' . $row['status'].' ('.$row['count(status)'] . ')</a></li>';
                                else
                                    echo '<li><a href="/browse/resources?status=' . $row['status'] . '">' . $row['status'].' ('.$row['count(status)'] . ')</a></li>';
                            }
                            ?>
                        </ul>
                        <hr>
                    </div>
                    <?php } ?>
                    <div class="col-md-12 col-sm-4">
                        <?php if($type=='resources' && isset($_SESSION['user']) && $_SESSION['user']->role>0): ?>
                            <a href="/forms/clear-cache.php"><button class="btn btn-danger">Clear search cache</button></a>

                            <hr/>

                            <h3>Column Weighting</h3>
                            <form method="post" action="/forms/resource-forms/updateWeighting.php">
                            <?php foreach($fields as $field): ?>
                                <div class="row" style="padding:3px 0">
                                    <div class="col-md-8"><?php echo $field->name ?></div>
                                    <div class="col-md-4"><input maxlength="2" size="2" style="width:30px;font-size:11px" name="<?php echo $field->id ?>" value="<?php echo $field->weight ?>"/></div>
                                </div>
                            <?php endforeach ?>
                            <button type="submit" class="btn-u">Update</button>
                            </form>
                            <hr/>
                        <?php endif?>
                        <!--
                        <h3>All Types</h3>
                        <ul class="list-unstyled">
                            <li <?php if ($type == 'communities') echo 'class="active"' ?>><a
                                    href="/browse/communities?query=<?php echo $query ?>">Communities</a></li>
                            <li <?php if ($type == 'resources') echo 'class="active"' ?>><a
                                    href="/browse/resources?query=<?php echo $query ?>">Resources</a></li>
                            <li <?php if ($type == 'content' && !$filter) echo 'class="active"' ?>><a
                                    href="/browse/content?query=<?php echo $query ?>">All Content</a></li>
                            <?php
                            foreach ($components['page'] as $compon) {
                                if ($type == 'content' && $filter == $compon->text2)
                                    echo '<li class="active"><a href="/browse/content?query=' . $query . '&filter=' . $compon->text2 . '">' . $compon->text1 . '</a></li>';
                                else
                                    echo '<li><a href="/browse/content?query=' . $query . '&filter=' . $compon->text2 . '">' . $compon->text1 . '</a></li>';
                            }
                            ?>
                        </ul>
                        <hr>
                        -->
                    </div>
                    <?php if($type == 'content'){?>
                    <div class="col-md-12 col-sm-4">
                        <h3>Most Used Tags</h3>
                        <ul class="list-unstyled">
                            <?php
                            $holder = new Tag();
                            $tags = $holder->getPopularTags(false, 0, 0, 5);
                            foreach ($tags as $tag) {
                                echo '<li><a href="/browse/content?query=tag:' . $tag->tag . '"><i class="fa fa-tags"></i> ' . $tag->tag . '</a></li>';
                            }
                            ?>
                        </ul>
                        <hr>
                    </div>
                    <?php } ?>
                    <?php if($type == "communities"): ?>
                        <div class="col-md-12 col-sm-4">
                            <h3>Create your own community</h3>
                            <p> Communities allow researchers to share and customize data from <a href="/scicrunch/data/search">over 200 data resources</a>.</p>
                            <a mailto:"info@scicrunch.org">Contact Us</a>
                             <?php $is_user = isset($_SESSION["user"]) ?>
                            <?php $user_id = $_SESSION['user']->id ?>
      	      	      	    <?php if($user_id == 247 || $user_id == 36111): ?>
                                <p>&nbsp;</p>
                                <h3>Create Community</h3>
				                        <a href="/create/community" class="btn-u btn-u-lg">
                                    <i class="fa fa-users"></i>
                                    Create
                                </a>
                            <?php endif ?>
                        </div>
                    <?php endif ?>
                    <?php if($type == "resourcedashboard"): ?>
                        <?php $is_user = isset($_SESSION["user"]) ?>
                        <div class="col-md-12">
                            <?php if($is_user): ?>
                                <h3>Submit your own resource</h3>
                                <p>You can contribute to the SciCrunch registry by submitting your own resource.  Each resource is manually curated before being added to the registry.</p>
                                <a href="/create/resource" class="btn btn-success">Create a resource</a>
                            <?php else: ?>
                                <h3>Suggest a resource</h3>
                                <p>You can contribute to the SciCrunch registry by suggesting a resource.  Each resource is manually curated before being added to the registry.</p>
                                <a href="/create/resourcesuggestion" class="btn btn-success">Suggest a resource</a>
                            <?php endif ?>
                        </div>
                    <?php endif ?>
                    <?php if($type == "datadashboard"): ?>
                        <div class="col-md-12">
                            <h3>Share your data</h3>
                            <p>You can share your Data through SciCrunch. Whether your data is contained in simple spreadsheets, a web page, or a queryable database, SciCrunch has many tools available to help you share your data.</p>
                            <a href="mailto:info@scicrunch.org" class="btn btn-success">Contact us</a>
                        </div>
                    <?php endif ?>
                </div>
            </div>
            <!--/col-md-2-->

            <div class="col-md-10">
                <?php
                } else {

                    echo '<div class="col-md-12" style="margin-top:30px">';
                }?>
                <?php
                switch ($type) {
                    case 'communities':
                        include 'browsing/communities.php';
                        break;
                    case 'content':
                        include 'browsing/content.php';
                        break;
                    case 'resources':
                        if($mode && $mode=='edit')
                            include 'browsing/registry-edit.php';
                        elseif ($id)
                            include 'browsing/curator.php';
                        else
                            include 'browsing/resources.php';
                        break;
                    case 'resourcedashboard':
                        include 'browsing/resource-dashboard.php';
                        break;
                    case 'datadashboard':
                        include 'browsing/data-dashboard.php';
                        break;
                    case 'rrid-mentions':
                        include 'browsing/rrid-mentions.php';
                        break;
                    case "resourcesedit":
                    case "curator":
                        include "browsing/curator.php";
                        break;
                    case "search":
                        include "browsing/mainpage-search.php";
                        break;
                    case "terminology":
                        include "browsing/scigraph-vocab.php";
                        break;
                    case "resourcementionupload":
                        include "communities/ssi/resource-mention-upload.php";
                        break;
                }
                ?>
            </div>
            <!--/col-md-10-->
        </div>
    </div>
    <!--/container-->
    <!--=== End Search Results ===-->

<?php
        // Manu added below line and commented the rest
        include "components/footer/footer-normal.php"
/*		
        $holder = new Component();
        $components = $holder->getByCommunity(56);  // changed from 0 to 56
        echo Component::footerHTML($components);
 */
 ?>
    <!--=== End Copyright ===-->
</div>
<!--/End Wrapepr-->

<!-- JS Global Compulsory -->
<script type="text/javascript" src="/assets/plugins/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="https://maps.google.com/maps/api/js?sensor=true"></script>

<script type="text/javascript" src="/assets/plugins/gmap/gmap.js"></script>
<script type="text/javascript" src="/js/main.js"></script>
<script type="text/javascript" src="/js/jquery.truncate.js"></script>
<script type="text/javascript" src="/assets/plugins/jquery-ui.min.js"></script>
<!-- JS Implementing Plugins -->
<script type="text/javascript" src="/assets/plugins/back-to-top.js"></script>
<!-- JS Page Level -->
<script type="text/javascript" src="/assets/js/app.js"></script>
<script type="text/javascript" src="/assets/plugins/owl-carousel/owl-carousel/owl.carousel.js"></script>
<script type="text/javascript" src="/assets/js/plugins/owl-carousel.js"></script>
<script src='https://www.google.com/recaptcha/api.js'></script>
<script type="text/javascript">
    jQuery(document).ready(function () {
        App.init();
        <?php if(isset($_SESSION['user'])){?>
        setTimeout(updateLogin, <?php if($_SESSION['user']->last_check > time()) echo 1000*($_SESSION['user']->last_check-time()); else echo 1000 ?>);
        <?php } ?>

        $('.truncate-desc').truncate({max_length: 500});
        $('.truncate-desc-short').truncate({max_length: 200});
        $('.inner-results p').truncate({max_length: 500});
    });
</script>
<script>
    $('.map').each(function(){
        var _this = $(this);
        var map,marker,infoWindow;
        map = new GMaps({
            div: '#'+$(_this).attr('id'),
            scrollwheel: false,
            lat: $(_this).attr('lat'),
            lng: $(_this).attr('lng')
        });
        infoWindow = new google.maps.InfoWindow({
            content: '<div style="height:40px">'+$(_this).attr('point')+'</div>'
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
        GATiming("browsing");
    });
</script>
<!--[if lt IE 9]>
<script src="/assets/plugins/respond.js"></script>
<script src="/assets/plugins/html5shiv.js"></script>
<![endif]-->

</body>
</html>
