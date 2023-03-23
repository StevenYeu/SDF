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
        $map_file = "../evaluation-results/data/" . $component . "_map.txt";
        $map_array = explode("\n", file_get_contents($map_file));        
    }

    $which = $_GET['q'];

    if ($component == 417)
        $file = 'data/GC2_stage1_Free_Energy_Set_' . $set . '.json';
    else
        $file = 'data/GC2_stage2_Free_Energy_Set_' . $set . '.json';

    $jsonpickle = file_get_contents($file);
    $json = substr($jsonpickle, 2, strlen($jsonpickle) - 2);
    $json = substr($json, 0, strlen($json) - 6);

    $j_array = array();
    $me_array = array();
    $less_array = array();
    $max_compounds = 15;

    // basically, for each person loop thru ligand values
    foreach (json_decode($json) as $filename=>$value) {
        if (!(in_array($filename, $map_array)))
            continue;

        $person_array = array();
        
        $chall = new Challenge_Submission();
        $chall->GetUserInfoFromReceipt($filename);

        if (($chall->uid == (int) $_SESSION['user']->id) || ($chall->uid == $debug_userid)) {
            $me_array[] = $filename;
        }

        $data = $value;

        if ($which == 'kendall') {
            if ($data[1] == 'null') 
                continue;
            $j_array['Set' . $set][] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$data[1], 'moe'=>$data[2], 'n'=>$data[0]);
        } elseif ($which == 'rmsd') {
            if ($data[7] == 'null') 
                continue;
            $j_array['Set' . $set][] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$data[7], 'moe'=>$data[8], 'n'=>$data[0]);
        }

        if ($data[0] < $max_compounds)
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
