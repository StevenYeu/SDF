<?php

//require_once __DIR__ . "/../../../api-classes/labs.php";
//require_once __DIR__ . "/../../../classes/classes.php";
$community = $data['community'];

$community_labs = Lab::loadArrayBy(Array("cid", "curated"), Array($community->id, Lab::CURATED_STATUS_APPROVED));
foreach ($community_labs as $lab) {
    if ($lab->id == $_GET['labid']) {
        $lab_name = $lab->name;
    }
}

$position = 0;
$dataset_id = $_GET['datasetid'];

// Get Dataset fields
$dataset = Dataset::loadBy(Array("id"), Array($dataset_id));
if (!$dataset)  
    die("No dataset found");

?>
<style>
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
<h2>DOI:<span ng-if="parentObj.overview[0].doi"> {{ parentObj.overview[0].doi }}</span></h2>


<h2 style="color: #242e5c">DATASET CITATION</h2>

<h3>{{ citation_authors }}. <?php echo date("Y"); ?>. {{ parentObj.overview[0].title }}. Open Data Commons for Spinal Cord Injury. <?php echo strtoupper($community->portalName); ?>:<?php echo $_GET['datasetid']; ?>. <span ng-if="parentObj.overview[0].doi">doi: {{ parentObj.overview[0].doi }}</span></h3>

    <div ng-controller="doiAbstractController as ctrl">
        <h2 style="color: #242e5c;">ABSTRACT</h2>
        <h3><u>STUDY PURPOSE:</u></h3>     <span ng-bind-html="parentObj.abstract[0].study_purpose" style="white-space: pre-line"></span>
        <h3><u>DATA COLLECTED:</u></h3>     <span ng-bind-html="parentObj.abstract[0].data_collected" style="white-space: pre-line"></span>
        <h3><u>CONCLUSIONS:</u></h3>     <span ng-bind-html="parentObj.abstract[0].conclusions" style="white-space: pre-line"></span>
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
<a target="_self" href="<?php echo $community->fullURL() ?>/lab/datasetoverview?labid=<?php echo $_GET['labid']; ?>&datasetid=<?php echo $_GET['datasetid'] ?>"><button class="btn btn-primary">Metadata Editor</button></a>
<a target="_self" href="<?php echo $community->fullURL() ?>/lab/dataset?labid=<?php echo $_GET['labid']; ?>&datasetid=<?php echo $_GET['datasetid'] ?>"><button class="btn btn-primary">Back to Dataset</button></a>

<h2 style="color: #242e5c">DATASET INFO</h2>

<h3>Contact: {{ contact_authors }}</h3>
<div style="height:2px;"><br></div>
<h3>Lab: <?php echo $lab_name;?><div style="height:2px;"><br></div>
<?php echo strtoupper($community->portalName); ?> Accession: <?php echo $_GET['datasetid']; ?></h3>
<h3>Records in Dataset: <?php echo $dataset->record_count; ?><div style="height:2px;"><br></div>
Fields per Record: <?php echo sizeof($dataset->field_set); ?></h3>

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
                <dt>{{contributor.lastname}}, {{contributor.firstname}} <span ng-if="contributor.middleinitial">{{contributor.middleinitial}}.</span><span ng-if="contributor.orcid">[ORCID:<a href="https://orcid.org/{{removeHTTPS(contributor.orcid)}}">{{removeHTTPS(contributor.orcid)}}</a>]</span></dt>
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

<?php //buildModal('doi'); ?>

<?php
    function showModalButton($array, $section, $position=0, $status) {
        if (($section == 'publications') || ($section == 'funding') || ($section == 'abstract'))
            $use_array = $array;
        else
            $use_array = $array[$section];
            
        if (($status == 'danger') || (sizeof($use_array) < 1))
            $buttonclass = 'danger';
        else
            $buttonclass = 'primary';

        echo ' &nbsp;<button type="button" class="' . $section . 'Modal btn btn-' . $buttonclass . ' btn-sm" data-toggle="modal" data-target="#' . $section . 'Modal" data-position="' . $position . '" id="' . $section . 'Modal_' . $position . '">Edit</button>';
        
        return;
    }

    
?>