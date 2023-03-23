<?php
//error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
//ini_set("display_errors", 1);

require_once __DIR__ . "/../../classes/classes.php";
require_once __DIR__ . "/../../classes/connection.class.php";

\helper\scicrunch_session_start();

// abel, mike, austin, jeff, romana, michael, karim
if (!in_array($_SESSION['user']->id, array(34206, 31651, 35258, 247, 35485, 36968, 33464)))
    die("access denied");

$position = 0;
$dataset_id = $_GET['datasetid'];

// Get Dataset fields
$dataset = Dataset::loadBy(Array("id"), Array($dataset_id));
if (!$dataset)  
    die("No dataset found");

$dataset_owner = new User();
$dataset_owner->getByID($dataset->uid);
?>   

<!DOCTYPE html>
<!--[if IE 8]>
<html lang="en" class="ie8"> <![endif]-->
<!--[if IE 9]>
<html lang="en" class="ie9"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en"> <!--<![endif]-->
<head>
    <title><?php echo $dataset->lab()->community()->portalName; ?> | lab</title>

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

    <!-- CSS Theme -->
    <link rel="stylesheet" href="/assets/css/themes/default.css" id="style_color">
    <link rel="stylesheet" href="/assets/css/shop/shop.blocks.css">
    <link rel="stylesheet" href="/assets/plugins/sky-forms/version-2.0.1/css/custom-sky-forms.css">

    <!-- CSS Customization -->
    <link rel="stylesheet" href="/assets/css/custom.css">
    <link rel="stylesheet" href="/css/community-search.css">
    <link href="/css/main.css" rel="stylesheet">

    <script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>
    <script type="text/javascript" src="/js/main.js"></script>
    


<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>
<script src="/js/node_modules/angular/sortable.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>

<script src="/js/module-error.js"></script>
<script src="/js/module-resource-directives.js"></script>
<script src="/js/module-datasets.js"></script>

<link rel="stylesheet" href="/css/labs.css">    

<style>
body {
padding:20px;
}
    #doifun td , #doifun h3{ font-size: 1.1em; }
    .modal-title { font-weight: bold; font-size: 1.2em; }


.left-nav,.right-nav{
         float:left; 
      }
      .right-nav{
        margin-left:20px;
      }
      a{
        cursor:pointer; 
      }


.modal-header {
  text-align: center;
}

.list {
	list-style: none outside none;
	margin: 10px 0px 30px;
}

.item {
	width: 400px;
	padding: 5px 10px;
	margin: 5px 0;
	border: 1px solid #444;
	border-radius: 5px;
	background-color: #fff;
}
.wide_item {
	width: 600px;
	padding: 5px 10px;
	margin: 5px 0;
	border: 1px solid #444;
	border-radius: 5px;
	background-color: #fff;
}
</style>

<div id="doi-management-app">
    <div ng-controller="doiController">
        <table id="doifun">
        <tbody>
        <tr>

    <td width="55%" class="ignore_shorten showing">
    <div ng-controller="doiOverviewController as ctrl">
        <h2 style="float: left">{{ parentObj.overview[0].title }}</h2>
    </div>        
<br clear="all" />                       
<h2>DOI:<span ng-if="parentObj.overview[0].hidden_doi"> {{ parentObj.overview[0].hidden_doi }}</span></h2>


<h2 style="color: #242e5c">DATASET CITATION</h2>

<h3>{{ citation_authors }}. <?php echo date("Y"); ?>. {{ parentObj.overview[0].title }}. {{ parentObj.overview[0].community }}. <?php echo strtoupper($dataset->lab()->community()->portalName); ?>:<?php echo $_GET['datasetid']; ?>. <span ng-if="parentObj.overview[0].hidden_doi">doi: {{ parentObj.overview[0].hidden_doi }}</span></h3>

    <div ng-controller="doiAbstractController as ctrl">
        <h2 style="color: #242e5c;">ABSTRACT</h2>
        <h3><u>STUDY PURPOSE:</u>     <span ng-bind-html="parentObj.abstract[0].study_purpose" style="white-space: pre-line"></span>
        <h3><u>DATA COLLECTED:</u>     <span ng-bind-html="parentObj.abstract[0].data_collected" style="white-space: pre-line"></span>
        <h3><u>CONCLUSIONS:</u>     <span ng-bind-html="parentObj.abstract[0].conclusions" style="white-space: pre-line"></span>
    </div>            

    <div ng-controller="TypeaheadCtrl as ctrl">
        <h2 style="color: #242e5c;">KEYWORDS</h2> 
        <span ng-bind-html="keywords"></span>
    </div>            


    <h2 style="color: #242e5c">PROVENANCE / ORIGINATING PUBLICATIONS</h2>
    <div ng-controller="doiPublicationsController as ctrl">
        <div class="page-body">
            <ul>
                <li ng-repeat="pub in parentObj.publication" class="ui-state-default">
                    <h3>{{ pub.publication }} <a href="http://doi.org/{{ pub.publication_doi }}">{{ pub.publication_doi }}</a>.</h3>
                    <h3><i>{{ pub.relevance }}</i></h3>
                </li>        
            </ul>                
        </div>
    </div>            

    <div ng-controller="doiNotesController as ctrl">
        <h2 style="color: #242e5c;">NOTES</h2>
        <span ng-bind-html="parentObj.notes[0].notes" style="white-space: pre-line"></span>
    </div> 
</td>

<td width="5%" class="ignore_shorten showing"></td>

<td width="40%" class="ignore_shorten showing">

<h2 style="color: #242e5c">DATASET INFO</h2>

<h3>Contact: {{ contact_authors }}</h3>
<div style="height:2px;"><br></div>
<h3>Lab: {{ parentObj.overview[0].lab }}<div style="height:2px;"><br></div>
<?php echo strtoupper($dataset->lab()->community()->portalName); ?> Accession: <?php echo $_GET['datasetid']; ?></h3>
<h3>Records in Dataset: <?php echo $dataset->record_count; ?><div style="height:2px;"><br></div>
Fields per Record: <?php echo sizeof($dataset->getRecordFieldSet()); ?></h3>

<h3>Files: 2</h3>
<div style="height:10px;"><br></div>

<h2 style="color: #242e5c">LICENSE</h2> 
Creative Commons Attribution License (CC-BY 4.0) [** once published]        

    <div ng-controller="doiFundingController as ctrl">
        <h2 style="color: #242e5c;">FUNDING AND ACKNOWLEDGEMENTS</h2>
        <span ng-bind-html="fund_string"></span>
    </div>            

    <div ng-controller="doiContributorsController as ctrl">
        <h2 style="color: #242e5c;">CONTRIBUTORS</h2>
        <h3 style="font-family: Helvetica Neue; letter-spacing: 1px;">
            <dl ng-repeat="contributor in listat['contributor']" >
                <dt>{{contributor.name}} <span ng-if="contributor.orcid">[ORCID:<a href="https://orcid.org/{{removeHTTPS(contributor.orcid)}}" target="_blank">{{removeHTTPS(contributor.orcid)}}</a>]</span></dt>
                <dd>{{ contributor.affiliation }}</dd>
            </dl>
        </h3>    
    </div>

</td>

</tr>

</tbody>

</table> 
</div>
</div>


<!-- JS Global Compulsory -->
<script type="text/javascript" src="/assets/plugins/jquery-migrate-1.2.1.min.js"></script>
<script type="text/javascript" src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>


</body>
</html>
