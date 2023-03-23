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

    $stage_data = array();

    $chall = new Challenge();
    $j_array = array();
    $max_compounds = 102;

    $u = 0;

    if (!(in_array($_GET['component'], array(417,443)))) {
        echo "invalid or missing component\n";
        exit;
    } else {
        $component = $_GET['component'];
        $map_file = "../pose/data/" . $component . "_map.txt";
        $map_array = explode("\n", file_get_contents($map_file));
    }

    if ($component == 417) {
        $file = '../spreadsheets/csv2dec/Scoring_stage_1_table';
    } else {
        $file = '../spreadsheets/csv2dec/Scoring_stage_2_table';
    }

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

        if ($line['Kendalls Tau'] == 'null') 
            continue;

        $j_array[] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$line['Kendalls Tau'], 'moe'=>$line["Kendall's Tau Uncertainty"], 'n'=>$line['Number of ligands']);

        if ($line['Number of ligands'] < $max_compounds)
            $less_array[] = $filename;

    }
    
/*    
    // basically, for each person loop thru ligand values
    foreach (json_decode($json) as $filename=>$value) {
        if (!(in_array($filename, $map_array)))
            continue;

        $person_array = array();
        $less_array = array();
        $data = $value;

        $chall = new Challenge_Submission();
        $chall->GetUserInfoFromReceipt($filename);

        if (($chall->uid == (int) $_SESSION['user']->id) || ($chall->uid == $debug_userid)) {
            $me_array[] = $filename;
        }

        if ($data[3] == 'N/A')
            continue;
            //$j_array[] = array('Receipt'=>$filename, 'kendall'=>null, 'moe'=>null, 'n'=>null);
        else {
            $j_array[] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$data[1], 'moe'=>$data[2], 'n'=>$data[0]);

            if ($data[0] < $max_compounds)
                $less_array[] = $filename;
        }
    }
*/
    usort($j_array, "custom_sort_desc");

    $echo_data['numerics'] = $j_array;
    $echo_data['mine_less'] = array_values(array_intersect($me_array, $less_array));
    $echo_data['flags'] = array_values(array_diff($me_array, $echo_data['mine_less']));
    $echo_data['anon_less'] = array_values(array_diff($less_array, $echo_data['mine_less']));

    echo json_encode($echo_data);
    
// Define the custom sort function
function custom_sort_desc($a,$b) {
    return $a['kendall']<$b['kendall'];
}

exit;

?>