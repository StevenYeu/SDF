<?php

//require_once __DIR__ . "/../../../api-classes/labs.php";
//require_once __DIR__ . "/../../../classes/classes.php";
$community = $data['community'];

$position = 0;
$dataset_id = $_GET['datasetid'];

// Get Dataset fields
$dataset = Dataset::loadBy(Array("id"), Array($dataset_id));
if (!$dataset)  
    die("No dataset found");

$dataset_owner = new User();
$dataset_owner->getByID($dataset->uid);

$community_labs = Lab::loadArrayBy(Array("cid", "curated"), Array($community->id, Lab::CURATED_STATUS_APPROVED));
foreach ($community_labs as $lab) {
    if ($lab->id == $_GET['labid']) {
        $lab_name = $lab->name;
    }
}

?>
<style>
    #doifun td , #doifun h3{ font-size: 1.1em; }
    .modal-title { font-weight: bold; font-size: 1.2em; }

  .typeahead-demo .custom-popup-wrapper {
    position: absolute;
    top: 100%;
    left: 0;
    z-index: 1000;
    display: none;
    background-color: #f9f9f9;
  }

  .typeahead-demo .custom-popup-wrapper > .message {
    padding: 10px 20px;
    border-bottom: 1px solid #ddd;
    color: #868686;
  }

  .typeahead-demo .custom-popup-wrapper > .dropdown-menu {
    position: static;
    float: none;
    display: block;
    min-width: 160px;
    background-color: transparent;
    border: none;
    border-radius: 0;
    box-shadow: none;
  }


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

<h2>Metadata Editor (In Development)</h2>
<h4>The Metadata Editor feature is still in beta testing. We appreciate any feedback and bug reports you submit. As a precaution, we recommend writing your information using the <a href="/upload/community-components/Metadata_Editor_Template_05122021.docx" >template</a> alongside filling out the Metadata Editor. </h4>
<h4>The <a href="<?php echo $data['community']->fullURL() ?>/about/tutorials#metadataEditor" target="_blank">Metadata Editor Tutorial</a> has information on how to use the Metadata Editor along with an explanation of the required metadata information. <br />We recommend writing your metadata in a text editor using the downloadable template and then copy/paste into the Metadata Editor once you're ready.</h4>
<hr />
<div id="doi-management-app">
    <div ng-controller="doiController">
        <table id="doifun">
        <tbody>
        <tr>

    <td width="55%" class="ignore_shorten showing">
    <div ng-controller="doiOverviewController as ctrl">
        <div><h2 style="float: left"><div ng-show="parentObj.overview[0].title">{{ parentObj.overview[0].title }}</div><div ng-show="!parentObj.overview[0].title"><?php echo $dataset->name; ?></div></h2>
            <button style="float: left; margin-top:11px; margin-left: 5px;" ng-click="showOverviewForm(parentObj.overview[0])"><i class="fa fa-edit"></i> Edit Title</button>
        </div>
        <br clear="all" />
    </div>        
                       
<h2>DOI:<span ng-if="parentObj.overview[0].doi"> {{ parentObj.overview[0].doi }}</span></h2>


<h2 style="color: #242e5c">DATASET CITATION</h2>


<h3>{{ citation_authors }}. <?php echo date("Y"); ?>. {{ parentObj.overview[0].title }}. <?php echo strtoupper($community->name); ?>. <?php echo strtoupper($community->portalName); ?>:<?php echo $_GET['datasetid']; ?>. <span ng-if="parentObj.overview[0].doi">doi: {{ parentObj.overview[0].doi }}</span></h3>

    <div ng-controller="doiAbstractController as ctrl">
        <div><h2 style="color: #242e5c; float: left">ABSTRACT</h2>
            <button style="float: left; margin-top:11px; margin-left: 5px;" ng-click="showAbstractForm(parentObj.abstract[0])"><i class="fa fa-edit"></i> Edit Abstract</button>
        </div>
        <br clear="all" />      

        <h3><u>STUDY PURPOSE:</u></h3>     <span ng-bind-html="parentObj.abstract[0].study_purpose" style="white-space: pre-line"></span>
        <h3><u>DATA COLLECTED:</u></h3>     <span ng-bind-html="parentObj.abstract[0].data_collected" style="white-space: pre-line"></span>
        <h3><u>CONCLUSIONS:</u></h3>     <span ng-bind-html="parentObj.abstract[0].conclusions" style="white-space: pre-line"></span>
    </div>            

    <div ng-controller="TypeaheadCtrl as ctrl">
        <div><h2 style="color: #242e5c; float: left;">KEYWORDS</h2> 
            <button style="float: left; margin-top:11px; margin-left: 5px;" ng-click="showKeywordsForm()"><i class="fa fa-edit"></i> Add keywords</button>
        </div>
        <br clear="all" />      
<!--<button class="btn btn-primary btn-sm" ng-click="showKeywordsForm()">Add keywords</button> -->
        <div class="page-body">
<!--            <span ng-bind-html="keywords"></span>  -->
            

            <span style="padding-left: 10px">* Drag/drop items to change sort order. Order is auto saved.<span>
            <ul ui-sortable="sortableOptions" ng-model="listat['keyword']" class="list" id="sortable_keyword">
              <li ng-repeat="word in parentObj.keyword" class="item ui-state-default" data-id="{{word.position}}">
                {{word.keyword}} <a style='color: blue' ng-click="deleteKeyValueById(word.position, <?php echo $_GET['datasetid']; ?>)"><i class="fa fa-trash"></i> Delete</a>
              </li>
            </ul>
        </div>
    </div>            


<h2 style="color: #242e5c">PROVENANCE / ORIGINATING PUBLICATIONS</h2>
    <div><span style="padding-left: 10px; float: left">To import an publication, enter a PMID or DOI. Leave the field empty to create a blank entry to edit manually. If you want to automatically add the publication's authors to this dataset's contributor list, check the box below. </span>
     <div class="form-group">
            <label>DOI/PMID (For DOI, just the 10.nnnn/identifier. For PMID, just the nnnnn )</label><br />
            <input ng-model="importbox" size="40" type="text" id="importbox"> 
    </div>
     <div class="form-group">
            <input type="checkbox" ng-model="include_authors" ng-init="include_authors=1" ng-true-value="1" ng-false-value="0" > Import authors as contributors</span>
        </div>
     <div class="form-group">
            <button style="float: left; margin-top:4px; margin-left: 5px;" ng-click="importPubs(pub)"><i class="fa fa-edit"></i> Import/Add Publication</button>
        </div>
        
</div>
<br clear="all" />
    
    <div ng-controller="doiPublicationsController as ctrl">
        <div class="page-body">
            <ul ui-sortable="sortableOptions" ng-model="listat['publication']" class="list" id="sortable_publication">
            
                <li ng-repeat="pub in parentObj.publication" class="wide_item ui-state-default" data-id="{{$index}}">
                    <h3>{{ pub.publication }} <br />
                    <span ng-if="pub.publication_doi"><br /><strong>DOI</strong>: {{ pub.publication_doi }}</span> 
                    <span ng-if="pub.publication_doi"><br /><strong>PMID</strong>: {{ pub.publication_pmid }}</span></h3>
                    <span ng-if="pub.relevance"><h3><i>{{ pub.relevance }}</i></h3></span>
                    <a style='color: blue' ng-click="showPublicationsForm(pub, $index)"><i class="fa fa-edit"></i> Edit</a>
                </li>        
            </ul>                
        </div>
    </div>            

    <div ng-controller="doiNotesController as ctrl">
        <div><h2 style="color: #242e5c; float: left">NOTES</h2>
            <button style="float: left; margin-top:11px; margin-left: 5px;" ng-click="showNotesForm(parentObj.notes[0])"><i class="fa fa-edit"></i> Edit Notes</button>
        </div>
        <br clear="all" />      

        <span ng-bind-html="parentObj.notes[0].notes"></span> 
    </div>            
</td>

<td width="5%" class="ignore_shorten showing"></td>

<td width="40%" class="ignore_shorten showing">
<a href="doi_preview?labid=<?php echo $_GET['labid']; ?>&datasetid=<?php echo $_GET['datasetid']; ?>" target="_self"><button class="btn btn-success">Preview DOI Metadata</button></a>
<a href="dataset?labid=<?php echo $_GET['labid']; ?>&datasetid=<?php echo $_GET['datasetid']; ?>" target="_self"><button class="btn btn-primary">Back to Dataset</button></a>

<h2 style="color: #242e5c">DATASET INFO</h2>

<h3>Contact: {{ contact_authors }}</h3>
<div style="height:2px;"><br></div>
<h3>Lab: <?php echo $dataset->lab()->name;?><div style="height:2px;"><br></div>
ODC-SCI Accession: <?php echo $_GET['datasetid']; ?></h3>
<h3>Records in Dataset: <?php echo $dataset->record_count; ?><div style="height:2px;"><br></div>
Fields per Record: <?php echo sizeof($dataset->field_set); ?></h3>

<h3>Files: 2</h3>
<div style="height:10px;"><br></div>



<!-- <?php showModalButton($build_keyvalues, 'license') ?></h3> -->
    <div ng-controller="doiLicenseController as ctrl">
    <div><h2 style="color: #242e5c; float: left;">LICENSE</h2> 
    <br clear="all" \>
Creative Commons Attribution License (CC-BY 4.0) [** once published]        
    </div>        



    <div ng-controller="doiFundingController as ctrl">
        <div><h2 style="color: #242e5c; float: left;">FUNDING AND ACKNOWLEDGEMENTS</h2>
            <button style="float: left; margin-top:11px; margin-left: 5px;" ng-click="showFundingForm()"><i class="fa fa-plus"></i> Add funding</button> 
        </div>
        <br clear="all" />      
        <span style="padding-left: 10px">* Drag/drop items to change sort order. Order is auto saved.<span>
    <ul ui-sortable="sortableOptions" ng-model="listat['funding']" class="list" id="sortable_funding">
      <li ng-repeat="fund in listat['funding']" class="item ui-state-default" data-id="{{$index}}">
        {{fund.agency}}<br />
        {{ fund.initials }} <a style='color: blue' ng-click="showFundingForm(fund, $index)"><i class="fa fa-edit"></i> Edit</a>
      </li>
    </ul>        <div class="page-body">
            <dl>
<!--                <dt ng-repeat-start="fund in parentObj.funding">{{fund.citation}}</dt>
                <dd style='padding-bottom: 5px' ng-repeat-end>{{ fund.agency }} [{{ fund.identifier }}]
                <a style='color: blue' ng-click="showFundingForm(fund)"><i class="fa fa-edit"></i> Edit</a></dd>
-->                
            </dl>
        </div>
    </div>            

    <div ng-controller="doiContributorsController as ctrl">
        <div><h2 style="color: #242e5c; float: left">Contributors/Authors</h2>
            <button style="float: left; margin-top:11px; margin-left: 5px;" ng-click="showContributorsForm()"><i class="fa fa-plus"></i> Add contributor/author</button> 
        </div>
        <br clear="all" />      

        <span style="padding-left: 10px">* Drag/drop items to change sort order. Order is auto saved. <br /><span style="color:red">Name</span> in red indicates incomplete name information...<span>
        <ul ui-sortable="sortableOptions" ng-model="listat['contributor']" class="list" id="sortable_contributor">
            <li ng-repeat="contributor in listat['contributor']" class="item ui-state-default" data-id="{{$index}}">
            <span ng-if="contributor.contact" style="color:#428bca;">Contact </span><span ng-if="contributor.author" style="color:#428bca;">Author<br /></span>
            <span style="color:red" ng-if="!contributor.lastname">{{contributor.name}}</span>
            {{contributor.lastname}}, {{contributor.firstname}} <span ng-if="contributor.middleinitial">{{contributor.middleinitial}}.</span>  <span ng-if="contributor.orcid">[ orcid: <a href="https://orcid.org/{{removeHTTPS(contributor.orcid)}}">{{removeHTTPS(contributor.orcid)}}</a> ]</span> <a style='color: blue' ng-click="showContributorsForm(contributor, $index)"><i class="fa fa-edit"></i> Edit</a><br />
            {{ contributor.affiliation }}
            </li>
        </ul>
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
