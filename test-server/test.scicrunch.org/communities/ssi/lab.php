<?php
$element_data = Array("user" => $_SESSION["user"], "community" => $community);
$type = $_GET["type"];
$subtype = $_GET["subtype"];

if (isset($vars['dataset_id']) && filter_var($vars['dataset_id'], FILTER_VALIDATE_INT))
    $element_data["dataset_id"] = $vars['dataset_id'];

$main_lab = Lab::getUserMainLab($_SESSION["user"], $community->id);

// note: general_members will not have a labid
if (isset($_GET["labid"]) && ($_GET['labid'] != '')) {
    $labid = $_GET["labid"];
    $lab = Lab::loadBy(Array("id"), Array($labid), '');
    $element_data["lab"] = $lab;
} elseif ($main_lab) {
    $lab = $main_lab;
}

$community_labs = Lab::loadArrayBy(Array("cid", "curated"), Array($community->id, Lab::CURATED_STATUS_APPROVED));
$user_labs = Array();
$nonuser_labs = Array();
$my_labs = Array();

foreach($community_labs as $cl) {
    if(isset($_SESSION['user']) && ($cl->isMember($_SESSION['user']))) {
        $user_labs[] = $cl;
        $my_labs[] = $cl->id;
    }
}    

$element_data["user_labs"] = $user_labs;
$element_data["main_lab"] = $main_lab;
$element_data["my_labs"] = $my_labs;

if((!$_SESSION["user"]) && ($type != 'data')) {
    $include_file = "must-be-logged-in";
} elseif ((!$community->isMember($_SESSION["user"])) && ($type != 'data')) {
    // general member
    $include_file = "not-a-community-member";
} elseif($type == "community-labs") {
    if ($subtype == "dashboard") {
        $include_file = "dashboard";
    } elseif($subtype == "lab-join") {
        $include_file = "join";
    } elseif($subtype == "list") {
        $include_file = "list";
    } elseif($subtype == "create") {
        $include_file = "create";
    } elseif($subtype == "datasets") {
        $element_data["page"] = $_GET["page"];
        $include_file = "community-datasets";
    } elseif($subtype == "dataset") {
        $element_data["datasetid"] = $_GET["datasetid"];
        $include_file = "community-single-dataset";
    } elseif($subtype == "info") {
        $include_file = "community-info";
    }
} elseif($type == "data") {
    if($subtype == "list") {
        $include_file = "public_datasets";
    } elseif($subtype == "metadata") {
        $include_file = "public_metadata";
    }
} else {
    $labid = $_GET["labid"];
    $lab = Lab::loadBy(Array("id"), Array($labid));
    
    if (is_null($lab)) {
        $include_file = "lab-not-found";
    } else {
        $element_data["lab"] = $lab;

        if(!$lab->isMember($_SESSION["user"])) {
            $include_file = "not-a-lab-member";
        } elseif ($subtype=="datasetoverview")
            $include_file = "overview";
        elseif ($subtype=="doi_preview")
            $include_file = "doi_preview";
        elseif($lab->curated !== Lab::CURATED_STATUS_APPROVED) {
            $element_data["submitted"] = isset($_GET["submitted"]);
            $include_file = "lab-not-approved";
        } elseif($subtype == "add-data") {
            $include_file = "add-data";
        } elseif($subtype == "create-dataset") {
            $include_file = "create-dataset";
        } elseif($subtype == "publish-datasets") {
            $include_file = "publish-datasets";
        } elseif($subtype == "create-associated-files") {
            $include_file = "create-associated-files";
        } elseif($subtype == "create-template") {
            $include_file = "create-template";
        } elseif($subtype == "add-to-dataset") {
            $include_file = "add-to-dataset";
        } elseif($subtype == "update-dataset") {
            $include_file = "update-dataset";
        } elseif($subtype == "dataset") {
            $include_file = "dataset";
        } elseif($subtype == "template") {
            $include_file = "template";
        } elseif($subtype == "admin") {
            $include_file = "admin";
        } elseif($subtype == "edit-lab-info") {
            $include_file = "edit-lab-info";
        } elseif($subtype == "published-datasets") {
            $include_file = "published-datasets";
        } elseif($subtype == "all-datasets") {
            $include_file = "all-lab-datasets";
        } elseif($subtype == "view-dataset") {
            $element_data["datasetid"] = $_GET["datasetid"];
            $element_data["lab-mode"] = true;
            $include_file = "community-single-dataset";
        } elseif($subtype == "my-datasets") {
            $include_file = "my-datasets";
        } elseif($subtype == "my-templates") {
            $include_file = "mytemplates";
        } else {
            $include_file = "lab-dashboard";
        }
    }
}

if($type == "lab" && !$subtype) {
    $old_breadcrumb = Connection::createBreadCrumbs(
        "Labs Home",
        Array("Home"),
        Array($community->fullURL()),
        "Labs Home"
    );
} else {
    $breadcrumb_page = ucfirst(str_replace("-", " ", $subtype));
    $old_breadcrumb = Connection::createBreadCrumbs(
        $breadcrumb_page,
        Array('Home', 'Labs'),
        Array($community->fullURL(), $community->fullURL() . "/community-labs/main"),
        $breadcrumb_page
    );
}

$crumb = array("Home"=>$community->fullURL() . "/community-labs/dashboard");

if ($lab->id)
    $crumb["Home"] .= "?labid=" . $lab->id;
    
// echo "<strong>" . ucfirst($breadcrumb_page) . "</strong>";

if ($type == "lab" && !$subtype) {
    $breadcrumb_page = 'lab';
}

if ($type == 'data') {
    $breadcrumb_page = 'public';
}

if (isset($_GET['datasetid'])) {
    $dataset = Dataset::loadBy(Array("id"), Array($_GET['datasetid']));

    // add to logs
    ScicrunchLogs::createNewObj($community->id, $_SESSION['user']->id, $_GET['datasetid'], 'dataset', 'pageview', $_SERVER['REQUEST_URI']);    
} elseif (($type == "data") && ($subtype == "metadata")) {
    // log pageview for public data
    $pattern = '/.*\/(\d*)$/';
    preg_match($pattern, $_SERVER['REQUEST_URI'], $matches);

    if ($matches[1]) {
        if ($_SESSION['user']->id)
            ScicrunchLogs::createNewObj($community->id, $_SESSION['user']->id, $matches[1], 'dataset', 'pageview', $_SERVER['REQUEST_URI']);
        else {
            ScicrunchLogs::createNewObj($community->id, 0, $matches[1], 'dataset', 'pageview', $_SERVER['REQUEST_URI']);
        }
    }
}

    switch (ucfirst($breadcrumb_page)) {
        case 'Public':
            $crumb["Home"] = $community->fullURL();
            if ($subtype == 'metadata')
                $crumb["Public Data Sets"] = $community->fullURL() . '/data/public';

            $crumb[''] = '';
            break;

        case 'Dashboard':
            break;

        // lab member sees some more details of commons data
        case 'All datasets':
            $crumb['Commons Data'] = '';
            break;

        // registered user sees less info about commons data
        case 'Datasets':
            $crumb['Commons Data'] = '';
            break;

        // registered user sees less info about commons data
        case 'Dataset':
            $crumb["Dataset (" . shortenDatasetName($dataset->name) . ")"] = '';
            break;

        case 'My datasets':
            $crumb["Lab (" . $lab->name . ")"] = $community->fullURL() . "/community-labs/main";
            $crumb['My Datasets'] = '';
            break;        
        
        case 'My templates':
            $crumb["Lab (" . $lab->name . ")"] = $community->fullURL() . "/community-labs/main";
            $crumb['My Templates'] = '';
            break;        
        
        case 'List':
            $crumb['Labs'] = $community->fullURL() . "/community-labs/main";
            $crumb[ucfirst($breadcrumb_page)] = '';
            break;

        case "View dataset":
            $dataset = Dataset::loadBy(Array("id"), Array($_GET['datasetid']));
            $crumb["Dataset (" . shortenDatasetName($dataset->name) . ")"] = 'javascript:history.back()';
            $crumb['View'] = '';
            break;

        case "Dataset":
            $dataset = Dataset::loadBy(Array("id"), Array($_GET['datasetid']));
            $crumb["Lab (" . $lab->name . ")"] = $community->fullURL() . "/community-labs/list?labid=" . $_GET['labid'];
            $crumb["Dataset (" . shortenDatasetName($dataset->name) . ")"] = $community->fullURL() . "/lab/dataset?labid=" . $_GET['labid'] . "&datasetid=" . $_GET['datasetid'];
            $crumb['Edit'] = '';
            break;

        case "Lab":
            $crumb["Lab (" . $lab->name . ")"] = '';
            break;

        case "Admin":
            $crumb["Lab (" . $lab->name . ")"] = $community->fullURL() . "/community-labs/list?labid=" . $_GET['labid'];
            $crumb['Manage Lab'] = '';
            break;

        case "Edit lab info":
            $crumb["Lab (" . $lab->name . ")"] = $community->fullURL() . "/community-labs/list?labid=" . $_GET['labid'];
            $crumb['Edit Lab Info'] = '';
            break;

        default:
            if (!in_array($_GET['labid'], $my_labs)) {
                $crumb["Labs Home"] = $community->fullURL() . "/community-labs/list?labid=" . $_GET['labid'];
            } else                     
                $crumb["My Lab (" . $lab->name . ")"] = $community->fullURL() . "/community-labs/main";

            if (isset($_GET['datasetid'])) {
                $crumb["Dataset (" . shortenDatasetName($dataset->name) . ")"] = $community->fullURL() . "/lab/dataset?labid=" . $_GET['labid'] . "&datasetid=" . $_GET['datasetid'];
                $element_data['dataset'] = shortenDatasetName($dataset->name);
                if (strtolower($breadcrumb_page) == 'datasetoverview') {
                    $crumb['Metadata Editor'] = '';
                } elseif (strtolower($breadcrumb_page) == 'doi_preview') {
                    $crumb['Metadata Editor'] = $community->fullURL() . "/lab/datasetoverview?labid=" . $_GET['labid'] . "&datasetid=" . $_GET['datasetid'];
                    $crumb['Preview Metadata'] = '';
                }
            }
            break;
    }

/* 
    need to put into an array to pass onto next page ...
    since $element_data is already being used, 

*/

$breadcrumb = '';
foreach ($crumb as $label => $link) {
    if (empty($link))
        $breadcrumb .= "<li>" . $label . "</li>\n";
    else
        $breadcrumb .= "<li><a href='" . $link . "'>" . $label . "</a></li>\n";
        
}

$old_breadcrumb = str_replace('        <div class="breadcrumbs-v3">
            <div class="container">
                <ul class="pull-left breadcrumb">
                                                                        ', '', $old_breadcrumb);
$old_breadcrumb = str_replace('</div>
        </div>', '', $old_breadcrumb);
$element_data['old_breadcrumb'] = $old_breadcrumb;

$element_data['crumb'] = $breadcrumb;
$element_data['labid'] = $_GET['labid'];
$element_data['dataset'] = $_GET['datasetid'];
//var_dump($_GET);
?>

<?php echo \helper\htmlElement("special-ilx", Array("ilx" => $GLOBALS["config"]["dataset-config"]["term"]["ilx"])) ?>

<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>
<script src="/js/node_modules/angular/sortable.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>

<script src="/js/module-error.js"></script>
<script src="/js/module-resource-directives.js"></script>
<script src="/js/module-datasets.js"></script>
<script src="/js/module-datasets-update.js"></script>
<script src="/js/papaparse.min.js"></script>
<link rel="stylesheet" href="/css/labs.css">

<div class="container-fluid margin-bottom-20">
    <?php if(isset($element_data["lab"])): ?>
        <input id="labid" type="hidden" value="<?php echo $element_data["lab"]->id ?>" />
    <?php endif ?>

    <?php if ((isset($_SESSION['user'])) && ($community->isMember($_SESSION["user"])))
        echo \helper\htmlElement("labs/leftnav", $element_data);
    elseif ($type == 'data')
        echo \helper\htmlElement("labs/leftnav_nonmember", $element_data);
    else
        echo "<div>";

    echo \helper\htmlElement("labs/" . $include_file, $element_data); ?>

    </div>
</div>

<script type="text/javascript">
     $(function(){
        var current_page_URL = location.href;
        $( "a" ).each(function() {
            if ($(this).attr("href") !== "#") {
                var target_URL = $(this).prop("href");
                if (target_URL == current_page_URL) {
                    $('nav a').parents('li, ul').removeClass('active');
                    $(this).parent('li').addClass('active');

                    return false;
                }
            }
        }); 
        if (current_page_URL.indexOf("lab/dataset?") != -1) {
            $("#viewedit").addClass("active");
        } else if (current_page_URL.indexOf("lab?") != -1) {
            $("#labhome").addClass("active");
        } else if (current_page_URL.indexOf("community-labs/dashboard") != -1) {
            $("#mydashboard").addClass("active");
        }

        $('[data-toggle="tooltip"]').tooltip();   
    }); 
</script>
	</div>

<?php 
    function shortenDatasetName($name, $newlength=20) {
        if (strlen($name) > $newlength)
            return substr($name, 0, $newlength) . " ...";
        elseif (strlen($name) == $newlength)
            return substr($name, 0, $newlength);
        else
            return $name;
    }
?>    
