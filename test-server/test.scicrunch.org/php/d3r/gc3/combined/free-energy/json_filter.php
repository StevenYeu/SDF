<?php

    /*
        This file takes the JSON data converted from a Python Pickle file and processes
        into a format that D3.js can use for charts. It handles the various result types:
        rmsd, rsr, rscc ...
    */

    // change this to see the user's bars
    if (isset($_GET['debug']))
        $debug_userid = $_GET['debug'];
    else
        $debug_userid = -1;
    
    include('../../../../../classes/classes.php');
    \helper\scicrunch_session_start();
    //error_reporting(0);

    if (!(isset($_GET['q']))) {
        echo "q value must be 'rmsd' or 'kendall'<br />\n";
        exit;
    }

    $sub_data = array("965"=>"p38a", "966"=>"VEGFR2", "967"=>"TIE2", "968"=>"CatS1","1009"=>"CatS2", "969"=>"JAK2SC2", "970"=>"JAK2SC3", "971"=>"ABL1");

    if (!(in_array($_GET['component'], array_keys($sub_data)))) {
        echo "invalid or missing component\n";
        exit;
    } else {
        $component = $_GET['component'];
        $map_file = "../pose/data/" . $component . "_map.txt";
        $map_array = explode("\n", file_get_contents($map_file));        
    }

    $which = $_GET['q'];
    $group = $_GET['group'];
    $set = $_GET['set'];

    if ($group == 'active') {
        $file_fragment = $sub_data[$_GET['component']] . "_" . ucfirst($_GET['method']) . "FreeEnergyProtocol_4_methods.csv";

        // can be confusing, so will clarify ..
        // the label "Set 1" = free_energy4 radio button
        // the label "Set 2" = free_energy5 radio button
        if ($set == 1)
            $set = 4;
        else
            $set = 5;

        if ($_GET['component'] == 967)
            $file_fragment = str_replace("Protocol_4_", "Protocol_" . $set . "_", $file_fragment);

        if (($_GET['component'] == 968) || ($_GET['component'] == 1009))
            $csvdir = "CatS";
        else
            $csvdir = "kinases";

    } elseif ($group == 'xray') {
        $file_fragment = $sub_data[$_GET['component']] . "_FreeEnergyProtocol_4_methods.csv";
        $csvdir = "CatS_XrayStructOnly";

    } elseif ($group = 'noties') {
        $file_fragment = $sub_data[$_GET['component']] . "_FreeEnergyProtocol_4_methods.csv";

        if ($component == 967)
            $file_fragment = $sub_data[$_GET['component']] . "_FreeEnergyProtocol_5_methods.csv";

//        $file = "../spreadsheets/Kinases_noTIES/" . $file_fragment;
        if (($_GET['component'] == 968) || ($_GET['component'] == 1009))
            $csvdir = "CatS";
        else
            $csvdir = "Kinases_noTIES";

    }

/*
    if (strpos($include_method, "xray"))
        $csvdir = "CatS_XrayStructOnly";
    elseif (substr($include_method, 0, 6) == "noties")
        $csvdir = "Kinases_noTIES";
    else {
        if (($_GET['component'] == 968) || ($_GET['component'] == 1009))
            $csvdir = "CatS";
        else
            $csvdir = "kinases";
    }
*/    
    $file = '../spreadsheets/newcsvs/' . $csvdir . "/" . $file_fragment;

    if ($_GET['partial'])
        $file = str_replace(".csv", "_partial.csv", $file);
    else
        $file = str_replace(".csv", "_complete.csv", $file);

    /* new code to use csv instead of json */
    $csv_array = array_map('str_getcsv', file($file));
    $header = array_shift($csv_array);  // grabs first line
    $header = array_map('trim', $header); // trims the header fields, just in case!

    for ($i=0; $i<sizeof($header); $i++) {
        if ($header[$i] == 'Number of ligands')
            $header[$i] == 'Number of Ligands';
        elseif ($header[$i] == 'RMSE')
            $header[$i] = 'RMSEc (kcal/mol)';
        elseif ($header[$i] == "RMSE Error") 
            $header[$i] = 'RMSEc Uncertainty';
    }
    
    array_walk($csv_array, '_combine_array', $header);  // walk thru array and create new associated array with header field as key

    function _combine_array(&$row, $key, $header) {
      $row = array_combine($header, $row);
    }
    /* end csv parser */

    $j_array = array();
    $me_array = array();
    $less_array = array();
    $max_compounds = 15;

    foreach ($csv_array as $line) {
        $filename = $line['Submission ID']; // actually is receipt_id
  /*      
        if (!(in_array($filename, $map_array)))
            continue;
*/
        if ($line['Number of Ligands'] == 0)
            continue;

        $person_array = array();

        $chall = new Challenge_Submission();
        $chall->GetUserInfoFromReceipt($filename);

        if (($chall->uid == (int) $_SESSION['user']->id) || ($chall->uid == $debug_userid)) {
            $me_array[] = $filename;
        }

        if ($which == 'kendall') {
            if ($line['Kendalls Tau'] == 'null') 
                continue;
            $j_array['Set' . $set][] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$line['Kendalls Tau'], 'moe'=>$line['Kendalls Tau Error'], 'n'=>$line['Number of Ligands']);
        } elseif ($which == 'rmsd') {
            if ($line['RMSEc (kcal/mol)'] > 2) 
                continue;
            $j_array['Set' . $set][] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$line['RMSEc (kcal/mol)'], 'moe'=>$line['RMSEc Uncertainty'], 'n'=>$line['Number of Ligands']);
        } elseif ($which == 'pearson') {
            if ($line["Pearson's r Error"] == "nan")
                $line["Pearson's r Error"] = 0;  

            $j_array['Set' . $set][] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$line["Pearson's r"], 'moe'=>$line["Pearson's r Error"], 'n'=>$line['Number of Ligands']);
        } elseif ($which == 'spearman') {

            if ($line["Spearman's Rho Error"] == "nan")
                $line["Spearman's Rho Error"] = 0;  

            $j_array['Set' . $set][] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$line["Spearman's Rho"], 'moe'=>$line["Spearman's Rho Error"], 'n'=>$line['Number of Ligands']);
        } elseif ($which == 'matthews') {
            $j_array['Set' . $set][] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$line["Matthews Correlation Coefficient"], 'moe'=>0, 'n'=>$line['Number of Ligands']);
        } elseif ($line['Number of ligands'] < $max_compounds)
            $less_array[] = $filename;
    }

// Define the custom sort function
     function custom_sort_asc($a,$b) {
          return $a['kendall']>$b['kendall'];
     }

     function custom_sort_desc($a,$b) {
          return $a['kendall']<$b['kendall'];
     }

    // Sort the multidimensional array
    if ($_GET['q'] == 'rmsd')
        usort($j_array['Set' . $set], "custom_sort_asc");
    else
        usort($j_array['Set' . $set], "custom_sort_desc");

    $merge_set = $j_array['Set' . $set];

    $echo_data['numerics'] = $merge_set;
    $echo_data['mine_less'] = array_values(array_intersect($me_array, $less_array));
    $echo_data['flags'] = array_values(array_diff($me_array, $echo_data['mine_less']));
    $echo_data['anon_less'] = array_values(array_diff($less_array, $echo_data['mine_less']));

    echo json_encode($echo_data);
    exit;
?>
