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

    $sub_data = array("1147"=>"Cathepsin S", "1146"=>"BACE1a", "1470"=>"BACE1b", "1479"=>"BACE2");

    if (!(in_array($_GET['component'], array_keys($sub_data)))) {
        echo "invalid or missing component\n";
        exit;
    } else {
        $component = $_GET['component'];
        $map_file = "../pose/data/" . $component . "_map.txt";
        $map_array = explode("\t", file_get_contents($map_file));
    }
/*
    if ($component == 417) {
        $file = '../spreadsheets/csv2dec/Scoring_stage_1_table';
    } else {
        $file = '../spreadsheets/csv2dec/Scoring_stage_2_table';
    }
*/

    $which = $_GET['q'];

        if ($_GET['component'] == "1146")
            $csvdir = "CATS";
        elseif (($_GET['component'] == "1146") || ($_GET['component'] == "1479")) 
            $csvdir = "BACE1_stage1";
        else
            $csvdir = "BACE1_stage2";

    $file = '../spreadsheets/newcsvs/' . $csvdir . "/" . $csvdir . "_" . "free_energy_overall_rounded.csv";
include "../spreadsheets/includes.php";

/*
    if ($_GET['partial'])
        $file = str_replace(".csv", "_partial.csv", $file);
    else
        $file = str_replace(".csv", "_complete.csv", $file);
*/
    /* new code to use csv instead of json */
    $csv_array = array_map('str_getcsv', file($file));
    $header = array_shift($csv_array);  // grabs first line
    $header = array_map('trim', $header); // trims the header fields, just in case!

    // if there are two 'Submission ID' header fields, change the 2nd one
    $i = 0;
    foreach ($header as $head) {
        if (($head == 'Submission ID') && ($i > 0)) {
            $header[$i] = 'SubSwap';
        }
        
        $i++;    
    }

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
            $j_array['Set' . $set][] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$line['Kendalls Tau'], 'moe'=>$line['Kendalls Tau Error'], 'n'=>$line['Number of Ligands'], 'method'=>str_replace("_based_scoring", "", trim($line["challenge_type"])), 'manual'=>strtolower(trim($line["Manual Intervention"])), 'machine'=>strtolower(trim($line["Machine Learning"])));
        } elseif ($which == 'rmsd') {
            if ($line['RMSEc (kcal/mol)'] > 2) 
                continue;
            $j_array['Set' . $set][] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$line['RMSEc (kcal/mol)'], 'moe'=>$line['RMSEc Uncertainty'], 'n'=>$line['Number of Ligands'], 'method'=>str_replace("_based_scoring", "", trim($line["challenge_type"])), 'manual'=>strtolower(trim($line["Manual Intervention"])), 'machine'=>strtolower(trim($line["Machine Learning"])));
        } elseif ($which == 'pearson') {
            if ($line["Pearson's r Error"] == "nan")
                $line["Pearson's r Error"] = 0;  

            $j_array['Set' . $set][] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$line["Pearson's r"], 'moe'=>$line["Pearson's r Error"], 'n'=>$line['Number of Ligands'], 'method'=>str_replace("_based_scoring", "", trim($line["challenge_type"])), 'manual'=>strtolower(trim($line["Manual Intervention"])), 'machine'=>strtolower(trim($line["Machine Learning"])));
        } elseif ($which == 'spearman') {

            if ($line["Spearman's Rho Error"] == "nan")
                $line["Spearman's Rho Error"] = 0;  

            $j_array['Set' . $set][] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$line["Spearman's Rho"], 'moe'=>$line["Spearman's Rho Error"], 'n'=>$line['Number of Ligands'], 'method'=>str_replace("_based_scoring", "", trim($line["challenge_type"])), 'manual'=>strtolower(trim($line["Manual Intervention"])), 'machine'=>strtolower(trim($line["Machine Learning"])));
        } elseif ($which == 'matthews') {
            $j_array['Set' . $set][] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$line["Matthews Correlation Coefficient"], 'moe'=>0, 'n'=>$line['Number of Ligands'], 'method'=>str_replace("_based_scoring", "", trim($line["challenge_type"])), 'manual'=>strtolower(trim($line["Manual Intervention"])), 'machine'=>strtolower(trim($line["Machine Learning"])));
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
