
    <link rel="stylesheet" href="/css/main.css">

    <!-- CSS Global Compulsory -->
    <link rel="stylesheet" href="/assets/plugins/bootstrap/css/bootstrap.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">

    <link rel="stylesheet" href="/assets/plugins/font-awesome/css/font-awesome.min.css">
<script type="text/javascript" src="/assets/plugins/jquery-3.4.1.min.js"></script>

<script src="https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js"></script>
<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/angular-1.7.9/angular-sanitize.js"></script>
<script src="/js/node_modules/angular/sortable.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>

<script src="/js/module-error.js"></script>
<script src="/js/module-resource-directives.js"></script>
<script src="/js/module-datasets.js"></script>


<script type="text/javascript" src="/assets/plugins/bootstrap/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/js/jquery.truncate.js"></script>
<?php

require_once __DIR__ . "/../../classes/classes.php";
require_once __DIR__ . "/../../classes/connection.class.php";
require_once "odc_config.php";
\helper\scicrunch_session_start();

//var_dump(\helper\isODCAdmin($_SESSION['user']->id));

// abel, mike, austin, jeff, romana, michael
if (!in_array($_SESSION['user']->id, array(34206, 31651, 35258, 247, 35485, 36968, 33464)))
    die("access denied");

// if not abel, mike, austin, jeff, then community is only tbi
/*
if (!in_array($_SESSION['user']->id, array(34206, 31651, 35258, 247)))
    $sci_only = ' c.id = 501 ';
else
*/
    $sci_only = ' 1 = 1 ';

    $connection = new Connection();
    $connection->connect();

    // Get uuid and name from the db
    $results = $connection->select("datasets d 
        inner join dataset_fields_templates dft on d.dataset_fields_template_id = dft.id 
        inner join labs l on dft.labid = l.id 
        inner join users u on u.guid = dft.uid 
        inner join communities c on c.id = l.cid
        left outer join (select dataset_id, max(text) as csv from dataset_doi_keyvalues where subtype='csv' group by dataset_id) doikeys on d.id = doikeys.dataset_id
        left outer join (select dataset_id, max(filename) as dictionary from dataset_associated_files where type='dictionary' group by dataset_id) daf1 on d.id = daf1.dataset_id
        left outer join (select dataset_id, max(filename) as methodology from dataset_associated_files where type='methodology' group by dataset_id) daf2 on d.id = daf2.dataset_id
        ", array("d.name", "u.firstname", "u.lastname", "u.email", "d.id", "c.shortName as cname", "d.lab_status", "d.editor_status", "d.curation_status", "doikeys.csv", "daf1.dictionary", "daf2.methodology"), "s", Array("request-doi"), "WHERE d.active = 1 AND {$sci_only} AND (lab_status = ? OR d.id IN (select distinct dataset_id as did from dataset_doi_keyvalues)) ORDER BY d.curation_status desc");
    $connection->close();

?>
<style>
    table {margin: 10px;}
    td {font-size: .9em; padding: 5px;}
    button {margin: 1px;}
    .zebra {background-color: #E7F4FF;}
    .disabled_color {color: #aaa;}
</style>
<p>The following are a list of datasets where the user has entered metadata OR a doi has been requested. Click the dataset name for more details.</p>
<div id="curation-tools-app" ng-controller="curationToolsController as ctrl">

<table cellspacing='0' cellpadding='5'>
    <thead>
    <tr>
        <th>ID</th>
        <th width="20%">Dataset</th>
        <th>Owner</th>
        <th>Community&nbsp;&nbsp;</th>
        <th>Lab Status</th>
        <th>Editorial Status&nbsp;&nbsp;</th>
        <th>Curation Status</th>
        <th>Tools</th>
    </tr>
    </thead>
    
    <tbody>
<?php
    $i=0;
    foreach ($results as $row) {
        if ($i%2)
            $zebra='zebra';
        else
            $zebra='';

        // editorial status options ... submitted, under-review, approved, rejected
        
        // should changeStatus include id, status, AND type (lab, editorial, curation)??
        //$dataset->labStatusColor(); $dataset->labStatusPretty();


        if ($row['editor_status']) {
            if ($row['editor_status'] != 'approved') {
                $editor_toggle = "<button ng-click='changeEditorStatus(" . $row['id'] . ", \"" . $row['editor_status'] . "\", \"" . urlencode($row['name']) . "\")'>" . $row['editor_status'] . "</button>";
            } else 
                $editor_toggle = "<strong>Approved</strong>";
        }
        else
            $editor_toggle = "";

        if ($row['curation_status']) {
            if ($row['curation_status'] == 'request-doi-locked') {
                $icon = 'lock';
                $opposite = 'unlock';
                $curation_toggle = "<button ng-click='changeStatus(" . $row['id'] . ", \"request-doi-" . $opposite . "ed\")' data-toggle='tooltip' title='Click to " . $opposite . "'>DOI Requested <i class='fa fa-" . $icon . "'></i></button>";
            } elseif ($row['curation_status'] == 'request-doi-unlocked') {
                $icon = 'unlock';
                $opposite = 'lock';
                $curation_toggle = "<button ng-click='changeStatus(" . $row['id'] . ", \"request-doi-" . $opposite . "ed\")' data-toggle='tooltip' title='Click to " . $opposite . "'>DOI Requested <i class='fa fa-" . $icon . "'></i></button>";
            } elseif ($row['curation_status'] == 'curation-approved')
                $curation_toggle = "<strong>Curation Approved</strong>";
            else 
                $curation_toggle = "<strong>" . $row['curation_status'] . "</strong>";
        }
        else
            $curation_toggle = "";

        if ($row['csv'])
            $csv_toggle = "<a href='/php/file-download.php?type=curator_csv&filename=" . $row['csv'] . "' target='_blank'><button class='lab-link'>CSV</button></a>";
        else
            $csv_toggle = '<button disabled class="disabled_color">CSV</button>';;

        if ($row['dictionary'])
            $dictionary_toggle = "<a href='/php/file-download.php?type=associated&filename=" . $row['dictionary'] . "' target='_blank'><button class='lab-link'>Dictionary</button></a>";
        else 
            $dictionary_toggle = '<button disabled class="disabled_color">Dictionary</button>';

        if ($row['methodology'])
            $methodology_toggle = "<a href='/php/file-download.php?type=associated&filename=" . $row['methodology'] . "' target='_blank'><button class='lab-link'>Methodolody</button></a>";
        else 
            $methodology_toggle = '<button disabled class="disabled_color">Methodology</button>';

        echo "<tr class='" . $zebra . "'>\n";
        echo "<td>" . $row['id'] . "</td>\n";
        echo "<td>";
        echo "<a href='javascript:void(0)' ng-click='expandCurationRow(" . $row['id'] . ")' data-toggle='tooltip' title='Click to see details' class='lab-link'>" . $row['name'] . "</a>";
        echo "<div id='showID_" . $row['id'] . "' style='display: none' ><strong>Extra Information</strong><br />";
        echo "Email: " .  $row['email'] . "</div>";
        echo "</td>\n";
        echo "<td>" . $row['firstname'] . " " . $row['lastname'] . "</td>\n";
        echo "<td>" . $row['cname'] . "</td>\n";
        echo "<td>";
        if ($row['lab_status'] == 'request-doi')
            echo "<span style='color:red'>" . $row['lab_status'] . "</span></td>\n";
        else {
            echo "<span style='color: " . Dataset::$pretty_lab_colors[$row['lab_status']] . "'>" . Dataset::$pretty_lab_statuses[$row['lab_status']] . "</span></td>\n";
        }
        
        echo "<td>" . $editor_toggle . "</td>\n";
        echo "<td>" . $curation_toggle . "</td>\n";
        echo "<td><a href='doi_preview.php?datasetid=" . $row['id'] . "' target='_blank'><button class='lab-link'>Metadata Preview</button></a>";

        echo $csv_toggle . $dictionary_toggle . $methodology_toggle;
//        echo "<a onClick='generateReload(" . $row['id'] . "); return false;'><button>Generate files</button></a>";
        // instead of running 'doi_xml' in background, run in new window so that we can return some error message.
        echo "<a href='doi_xml.php?dataset_id=" . $row['id'] . "' target='_blank'><button class='lab-link'>Generate files</button></a>";

        if (($row['curation_status']=='request-doi-locked') || ($row['curation_status'] == 'request-doi-unlocked')) {
            if (checkGeneratedFiles($row['id']))
                echo "<button " . checkGeneratedFiles($row['id']) . " ng-click='changeStatus(" . $row['id'] . ", \"curation-approved\")' data-toggle='tooltip' title='Click to approve'>Approve <i class='fa fa-check'></i></button>";
        }
        echo "</td>\n\n</tr>";
        $i++;
    }

    function checkGeneratedFiles($id) {
        $all_good = false;
        $dir = __DIR__ . "/../../../doi-datasets/dataset_" . $id;

        foreach (array('json_' . $id . ".json", 'stub_' . $id . ".html", 'metadata_' . $id . ".html", 'xml_' . $id . ".xml") as $file) {
            if (!is_file($dir . "/" . $file))
                return false;
        }
        return true;
    }
?>
    </tbody>
</table>    

    <script type="text/ng-template" id="change-editor-status-modal.html">
        <div class="modal-header">
            <h3 class="modal-title">Change Editorial Status</h3>
            <strong>Dataset:</strong> {{ dataset_name }}<br />
            <strong>Current Status:</strong> {{ current_status }}
        </div>
        <div class="modal-body">
            <form ng-submit="qchangeEditorStatus()" >
            <div class="row">
                <div class="col-md-12">
                    <div ng-init="status=current_status">
                        <ul style="list-style-type:none; padding-inline-start: 0px;">
                            <li><input type="radio" ng-model="status" value="submitted"> Submitted</li>
                            <li><input type="radio" ng-model="status" value="under-review"> Under Review</li>
                            <li><input type="radio" ng-model="status" value="approved"> Approved</li>
                            <li><input type="radio" ng-model="status" value="rejected"> Rejected</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-7">
                    <button type="submit" class="btn btn-success" ng-disabled="current_status == status" ng-click="done(status)">Update Status</button>
                    <button class="btn btn-danger" ng-click="cancel()">Cancel</button>
                </div>
            </div>    
        </div>
            </form>
        <div class="modal-footer">
        </div>
    </script>

</div>

<script type="text/javascript">
    async function generateReload(id) {
        let response = await fetch('doi_xml.php?dataset_id=' + id);
        //window.location.reload()
    }

    $('[data-toggle="tooltip"]').tooltip();

</script>
