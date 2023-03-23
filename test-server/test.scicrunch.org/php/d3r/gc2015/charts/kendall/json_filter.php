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

    $u = 0;

    if (!(in_array($_GET['component'], array(279, 280, 281, 294)))) {
        echo "invalid or missing component\n";
        exit;
    } else {
        $component = $_GET['component'];
        $map_file = "../evaluation-results/data/" . $component . "_map.txt";
        $map_array = explode("\n", file_get_contents($map_file));
    }

    $which = $_GET['q'];

    //$file = 'js/ranking.csv';
    $file = 'data/ranking_rounded.json';

    $jsonpickle = file_get_contents($file);
    $json = substr($jsonpickle, 2, strlen($jsonpickle) - 2);
    $json = substr($json, 0, strlen($json) - 6);

    $j_array = array();
    $me_array = array();

    // basically, for each person loop thru ligand values
    foreach (json_decode($json) as $filename=>$value) {
        if (!(in_array($filename, $map_array)))
            continue;

        $save_filename = $filename;

        $person_array = array();
        
        $chall = new Challenge_Submission();
        $chall->GetUserInfoFromReceipt($filename);

        $vars = get_object_vars ( $value );

        foreach($vars as $key=>$value) {
            if (substr($key, -3) == "csv") {
                $counter = str_replace("LigandScores", "", $key);
                $counter = str_replace(".csv", "", $counter);
                $filename = $save_filename . $counter;
            } else {
                $filename = $save_filename . "-" . str_replace("FreeEnergies", "FE", $key);
            }
            
            if (($chall->uid == (int) $_SESSION['user']->id) || ($chall->uid == $debug_userid)) {
                $me_array[] = $filename;
            }

            // for this $key, we want $value->{"py/tuple"}
            $data = $value->{"py/tuple"};
            if ($data[3] == 'N/A')
                continue;
                //$j_array[] = array('Receipt'=>$filename, 'kendall'=>null, 'moe'=>null, 'n'=>null);
            else
                $j_array[] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$data[3], 'moe'=>$data[4], 'n'=>$data[0]);
        }
    }

    usort($j_array, "custom_sort_desc");

    $echo_data['numerics'] = $j_array;
    $echo_data['flags'] = $me_array;

    echo json_encode($echo_data);
    
// Define the custom sort function
function custom_sort_desc($a,$b) {
    return $a['kendall']<$b['kendall'];
}

exit;





?>
