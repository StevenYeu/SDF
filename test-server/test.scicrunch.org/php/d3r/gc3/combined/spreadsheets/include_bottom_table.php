<style>
table.dataTable thead th {
        font-color: purple;
        text-align: left;
        padding-left: 10px;
        padding-bottom: 0px;
    }
</style>

<table id="gc3results" class="display" cellpadding="2" cellspacing="0">

<?php
    $sub_data = array("965"=>"p38a", "966"=>"VEGFR2", "967"=>"TIE2", "968"=>"CatS1","1009"=>"CatS2", "969"=>"JAK2SC2", "970"=>"JAK2SC3", "971"=>"ABL1");
    if (isset($include_method)) {
        switch(strtolower($include_method)) {
            case "free_energy":
                $title = "Free Energy";
                $file_fragment = "FreeEnergyProtocol_4_methods.csv";

                if ($_GET['component'] == "967") {
                    // can be confusing, so will clarify ..
                    // the label "Set 1" = free_energy4 radio button
                    // the label "Set 2" = free_energy5 radio button
                    if ($set == 1)
                        $set = 4;
                    else
                        $set = 5;

                    $file_fragment = $sub_data[$_GET['component']] . "_" . str_replace("Protocol_4_", "Protocol_" . $set . "_", $file_fragment);
                } else
                    $file_fragment = $sub_data[$_GET['component']] . "_" . $file_fragment;
                break;
                
            case "scoring":
                $title = "Affinity Ranking";
                $file_fragment = $sub_data[$_GET['component']] . "_" . "LigandScoringProtocol_23_methods.csv";

                break;
                
            case "pose968":
                $title = "Pose Prediction";
                $file_fragment = 'Stage1a_PosePredictionProtocol_Website.csv';
                $id = "968";
                $index = 1;
                break;
                
            case "pose972":
                $title = "Pose Prediction";
                $file_fragment = 'Stage1b_PosePredictionProtocol_Website.csv';
                $id = "972";
                $index = 1;
                break;

            case "cats1_xray_free":
                $title = "Free Energy";
                $file_fragment = $sub_data[$_GET['component']] . '_FreeEnergyProtocol_4_methods.csv';
                break;

            case "cats2_xray_free":
                $title = "Free Energy";
                $file_fragment = $sub_data[$_GET['component']] . '_FreeEnergyProtocol_4_methods.csv';
                break;

            case "cats1_xray_scoring":
                $title = "Affinity Ranking";
                $file_fragment = $sub_data[$_GET['component']] . '_LigandScoringProtocol_23_methods.csv';
                break;

            case "noties_scoring":
                $title = "Affinity Ranking";
                $file_fragment = $sub_data[$_GET['component']] . "_LigandScoringProtocol_23_methods.csv";
                break;

            case "noties_freeenergy":
                $title = "Free Energy";
                $file_fragment = $sub_data[$_GET['component']] . "_FreeEnergyProtocol_4_methods.csv";

                if ($_GET['component'] == "967") {
                    // can be confusing, so will clarify ..
                    // the label "Set 1" = free_energy4 radio button
                    // the label "Set 2" = free_energy5 radio button
                    if ($set == 1)
                        $set = 4;
                    else
                        $set = 5;

                    $file_fragment = str_replace("Protocol_4_", "Protocol_" . $set . "_", $file_fragment);
                }

                break;
        }
    }

    /* new code to use csv instead of json 
    if (strpos($include_method, "xray"))
        $file = "../spreadsheets/CatS_evaluations_XrayCatSOnly/" . $sub_data[$_GET['component']] . "/" . $file_fragment;
    elseif (substr($include_method, 0, 6) == "noties")
        $file = "../spreadsheets/noties-output/" . $file_fragment;
    else
        $file = '../spreadsheets/csv_name_pi/' . $file_fragment;
    */

    if (strpos($include_method, "xray"))
        $csvdir = "CatS_XrayStructOnly";
    elseif (substr($include_method, 0, 6) == "noties")
        $csvdir = "Kinases_noTIES";
    else {
        if (($_GET['component'] == 968) || ($_GET['component'] == 1009) || ($_GET['component'] == 972))
            $csvdir = "CatS";
        else
            $csvdir = "kinases";
    }

    $file = '../spreadsheets/newcsvs/' . $csvdir . "/" . $file_fragment;

    if ($_GET['partial'])
        $file = str_replace(".csv", "_partial.csv", $file);
    else
        $file = str_replace(".csv", "_complete.csv", $file);

    $csv_array = array_map('str_getcsv', file($file));
    $header = array_shift($csv_array);  // grabs first line
    $header = array_map('trim', $header); // trims the header fields, just in case!

    array_walk($csv_array, '_combine_array', $header);  // walk thru array and create new associated array with header field as key

    function _combine_array(&$row, $key, $header) {
      $row = array_combine($header, $row);
    }
    /* end csv parser */

// FE example - filename,firstname,lastname,email,organization, Number of Ligands, Kendalls &tau;, Kendalls &tau; Error, Spearman's Rho, Spearman's Rho Error, Pearson's r, Pearson's r Error, RMSE, RMSE Error,Method Name,Software,Method Type,isAnonymous
// Scoring example - filename,firstname,lastname,email,organization, Number of Ligands, Kendalls &tau;, Kendalls &tau; Error, Spearman's Rho, Spearman's Rho Error,Method Name,Software,method_type,isAnonymous
// Pose example - submission id, median rmsd , mean rmsd, std rmsd,number of ligands,Method Name,Software

// header line was extracted, so output header as <th> 
    echo "<thead>\n";

if (($csvdir == 'CatS') && ($include_method == 'scoring')): ?>
<tr class="tabs">
    <th>Receipt ID</th>
    <th>Submitter Name</th>
    <th>PI/Group Name</th>
    <th>Number of Ligands</th>
    <th class="tab-link current" data-tab="kendall">Kendall's <span style="font-weight: normal">&tau;</span> </th>
    <th class="tab-link" data-tab="kendall">Kendall's <span style="font-weight: normal">&tau;</span> Error</th>
    <th class="tab-link" data-tab="spearman">Spearman's <span style="font-weight: normal">&rho;</span></th>
    <th class="tab-link" data-tab="spearman">Spearman's <span style="font-weight: normal">&rho;</span> Error</th>
    <th>Method Name</th>
    <th>Software</th>
    <th>Method Type</th>
</tr>
</thead>

<?php elseif (($csvdir == 'CatS') && ($include_method == 'free_energy')): ?>
<tr class="tabs">
    <th>Receipt ID</th>
    <th>Submitter Name</th>
    <th>PI/Group Name</th>
    <th>Number of Ligands</th>
    <th class="tab-link current" data-tab="kendall">Kendall's <span style="font-weight: normal">&tau;</span></th>
    <th class="tab-link" data-tab="kendall">Kendall's <span style="font-weight: normal">&tau;</span> Error</th>
    <th class="tab-link" data-tab="spearman">Spearman's <span style="font-weight: normal">&rho;</span></th>
    <th class="tab-link" data-tab="spearman">Spearman's <span style="font-weight: normal">&rho;</span> Error</th>
    <th class="tab-link" data-tab="pearson">Pearson's r</th>
    <th class="tab-link" data-tab="pearson">Pearson's r Error</th>
    <th class="tab-link" data-tab="rmsd">RMSE<sub>c</sub></th>
    <th class="tab-link" data-tab="rmsd">RMSE<sub>c</sub> Error</th>
    <th>Method Name</th>
    <th>Software</th>
    <th>Method Type</th>
</tr>
</thead>

<?php elseif (($include_method == 'scoring') || ($include_method == 'free_energy')): ?>
<tr class="tabs">
    <th>Receipt ID</th>
    <th>Submitter Name</th>
    <th>PI/Group Name</th>
    <th>Number of Ligands</th>
    <th class='tab-link' data-tab='matthews'>Matthews Correlation Coefficient</th>
    <th>Method Name</th>
    <th>Software</th>
    <th>Method Type</th>
</tr>
</thead>

<?php elseif ((strpos($include_method, "xray_free")) || ($include_method == 'noties_freeenergy')):  ?>
<tr class="tabs">
    <th>Receipt ID</th>
    <th>Submitter Name</th>
    <th>PI/Group Name</th>
    <th>Number of Ligands</th>
    <th class="tab-link current" data-tab="kendall">Kendall's <span style="font-weight: normal">&tau;</span></th>
    <th class="tab-link" data-tab="kendall">Kendall's <span style="font-weight: normal">&tau;</span> Error</th>
    <th class="tab-link" data-tab="spearman">Spearman's <span style="font-weight: normal">&rho;</span></th>
    <th class="tab-link" data-tab="spearman">Spearman's <span style="font-weight: normal">&rho;</span> Error</th>
    <th class="tab-link" data-tab="pearson">Pearson's r</th>
    <th class="tab-link" data-tab="pearson">Pearson's r Error</th>
    <th class="tab-link" data-tab="rmsd">RMSE<sub>c</sub></th>
    <th class="tab-link" data-tab="rmsd">RMSE<sub>c</sub> Error</th>
    <th>Method Name</th>
    <th>Software</th>
    <th>Method Type</th>
</tr>
</thead>

<?php
elseif ((strpos($include_method, "xray_scoring")) || ($include_method == 'noties_scoring')): ?>

<tr class="tabs">
    <th>Receipt ID</th>
    <th>Submitter Name</th>
    <th>PI/Group Name</th>
    <th>Number of Ligands</th>
    <th class="tab-link current" data-tab="kendall">Kendall's <span style="font-weight: normal">&tau;</span> </th>
    <th class="tab-link" data-tab="kendall">Kendall's <span style="font-weight: normal">&tau;</span> Error</th>
    <th class="tab-link" data-tab="spearman">Spearman's <span style="font-weight: normal">&rho;</span></th>
    <th class="tab-link" data-tab="spearman">Spearman's <span style="font-weight: normal">&rho;</span> Error</th>
    <th>Method Name</th>
    <th>Software</th>
    <th>Method Type</th>
</tr>
</thead>

<?php
else: ?>
<tr class="tabs">
    <th>Receipt ID</th>
    <th>Submitter Name</th>
    <th>PI/Group Name</th>
    <th>Number of Ligands</th>
    <th class="tab-link" data-tab="Median">Median RMSD</th>
    <th class="tab-link current" data-tab="Mean">Mean RMSD</th>
    <th>STD RMSD</th>
    <th>Method Name</th>
    <th>Software</th>
</tr>
</thead>

<?php
endif;

    echo "<tbody>\n";

    foreach ($csv_array as $line) {
        echo "<tr>\n";
        if (isset($line['filename']))
            echo "<td>" . $line['filename'] . "</td>\n";
        elseif (isset($line['submission id']))
            echo "<td>" . $line['submission id'] . "</td>\n";
        elseif (isset($line['submission ID']))
            echo "<td>" . $line['submission ID'] . "</td>\n";
        elseif (isset($line['Submission ID']))
            echo "<td>" . $line['Submission ID'] . "</td>\n";

        if ($line['isAnonymous']) {
            echo "<td>&nbsp;</td><td>&nbsp;</td>\n";
        } else 
            echo "<td>" . ucfirst($line['First Name']) . " " . ucfirst($line['Last Name']) . "</td><td>" . ucfirst($line['PI Name']) . "</td>\n";

        if (isset($line['Number of Ligands']))
            echo "<td>" . $line['Number of Ligands'] . "</td>\n";
        elseif (isset($line['number of ligands']))
            echo "<td>" . $line['number of ligands'] . "</td>\n";

        if (substr($include_method, 0, 4) == 'pose') {
            echo "<td>" . $line['Median RMSD'] . "</td>\n";
            echo "<td>" . $line['Mean RMSD'] . "</td>\n";
            echo "<td>" . $line["STD RMSD"] . "</td>\n";
        } elseif (   
            (($csvdir == 'CatS') && ($include_method == 'scoring'))  || 
            ($include_method == 'noties_scoring') || 
            (strpos($include_method, "xray_scoring"))
        ){
            echo "<td>" . $line['Kendalls Tau'] . "</td>\n";
            echo "<td>" . $line['Kendalls Tau Error'] . "</td>\n";
            echo "<td>" . $line["Spearman's Rho"] . "</td>\n";
            echo "<td>" . $line["Spearman's Rho Error"] . "</td>\n";
        } elseif (
            (($csvdir == 'CatS') && ($include_method == 'free_energy')) || 
            ((strpos($include_method, "xray_free"))) || 
            ($include_method == 'noties_freeenergy')
        ) {
            echo "<td>" . $line['Kendalls Tau'] . "</td>\n";
            echo "<td>" . $line['Kendalls Tau Error'] . "</td>\n";
            echo "<td>" . $line["Spearman's Rho"] . "</td>\n";
            echo "<td>" . $line["Spearman's Rho Error"] . "</td>\n";
            echo "<td>" . $line["Pearson's r"] . "</td>\n";
            echo "<td>" . $line["Pearson's r Error"] . "</td>\n";
            echo "<td>" . $line['RMSE'] . "</td>\n";
            echo "<td>" . $line['RMSE Error'] . "</td>\n";
        } elseif (($include_method == 'scoring') || ($include_method == 'free_energy')) {
            echo "<td>" . $line["Matthews Correlation Coefficient"] . "</td>\n";
        }

        echo "<td>" . "<a target='_blank' href='../spreadsheets/p-software.php?receipt=" . $line['Submission ID'] . "'>" . $line['Method Name'] . "</a></td>\n";
        echo "<td>" . str_replace("system preparation", "", $line['Software']) . "</td>\n";
        
        if (substr($include_method, 0, 4) !== 'pose')
            echo "<td>" . $line['Method Type'] . "</td>\n";


        echo "</tr>\n";
    }
    echo "</tbody>\n</table>\n";

?>
    <script type="text/javascript" src="/assets/plugins/jquery-ui.min.js"></script>
