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
        $map_file = "../evaluation-results/data/" . $component . "_map.txt";
        $map_array = explode("\n", file_get_contents($map_file));
    }

    if (!(in_array($_GET['method'], array("ligand", "structure")))) {
        echo "Must be ligand or structure based\n";
        exit;
    } else {
        if ($component == 417) {
            if ($_GET['method'] == 'ligand') {
                $file = 'data/GC2_stage1_Ligand-Based_Scoring.json';
            } else {
                $file = 'data/GC2_stage1_Structure-Based_Scoring.json';
            }
        } else {
            if ($_GET['method'] == 'ligand') {
                $file = 'data/GC2_stage2_Ligand-Based_Scoring.json';
            } else {
                $file = 'data/GC2_stage2_Structure-Based_Scoring.json';
            }
        }
    }

    $which = $_GET['q'];

    $jsonpickle = file_get_contents($file);
    $json = substr($jsonpickle, 2, strlen($jsonpickle) - 2);
    $json = substr($json, 0, strlen($json) - 6);

    $j_array = array();
    $me_array = array();

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