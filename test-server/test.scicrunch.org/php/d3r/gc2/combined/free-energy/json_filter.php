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

    if ((isset($_GET['set'])) && (($_GET['set'] == 1) || ($_GET['set'] == 2))) {
        $set = $_GET['set'];
    } else {
        echo "Must be Set 1 or 2";
        exit;
    }

    if (!(($_GET['component'] !== 417) || ($_GET['component'] !== 443))) {
        echo "invalid or missing component\n";
        exit;
    } else {
        $component = $_GET['component'];
        $map_file = "../pose/data/" . $component . "_map.txt";
        $map_array = explode("\n", file_get_contents($map_file));        
    }

    $which = $_GET['q'];

    if ($component == 417)
        $file = '../spreadsheets/csv2dec/FE_set_' . $set . '_stage_1_scoring_FE_methods';
    else
        $file = '../spreadsheets/csv2dec/FE_set_' . $set . '_stage_2_scoring_FE_methods';

    if ($_GET['partial'] == 0)
        $file .= "_complete.csv";
    else        
        $file .= "_partial.csv";

    /* new code to use csv instead of json */
    $csv_array = array_map('str_getcsv', file($file));
    $header = array_shift($csv_array);  // grabs first line
    $header = array_map('trim', $header); // trims the header fields, just in case!

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
        $filename = $line['Submission ID'];
        
        if (!(in_array($filename, $map_array)))
            continue;

        if ($line['Number of ligands'] == 0)
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
            $j_array['Set' . $set][] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$line['Kendalls Tau'], 'moe'=>$line['Kendalls Tau Error'], 'n'=>$line['Number of ligands']);
        } elseif ($which == 'rmsd') {
            if ($line['RMSEc (kcal/mol)'] > 2) 
                continue;
            $j_array['Set' . $set][] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$line['RMSEc (kcal/mol)'], 'moe'=>$line['RMSEc Uncertainty'], 'n'=>$line['Number of ligands']);
        } else

        if ($line['Number of ligands'] < $max_compounds)
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
    elseif ($_GET['q'] == 'kendall')
        usort($j_array['Set' . $set], "custom_sort_desc");

    $merge_set = $j_array['Set' . $set];

    $echo_data['numerics'] = $merge_set;
    $echo_data['mine_less'] = array_values(array_intersect($me_array, $less_array));
    $echo_data['flags'] = array_values(array_diff($me_array, $echo_data['mine_less']));
    $echo_data['anon_less'] = array_values(array_diff($less_array, $echo_data['mine_less']));

    echo json_encode($echo_data);
    exit;
?>
