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

    if (!(($_GET['component'] !== 279) || ($_GET['component'] !== 281))) {
        echo "invalid or missing component\n";
        exit;
    } else {
        $component = $_GET['component'];
        $map_file = "../evaluation-results/data/" . $component . "_map.txt";
        $map_array = explode("\n", file_get_contents($map_file));        
    }

    $which = $_GET['q'];

    //$file = 'js/ranking.csv';
    $file = 'data/free_energy_rounded.json';

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
            // only want data where $key looks like "rel_FreeEnergiesSetX"
            // for this $key, we want $value->{"py/tuple"}
            if (substr($key, 0, 3) == 'rel') {
                $filename = $save_filename . "-" . str_replace("FreeEnergies", "FE", $key);

                if (($chall->uid == (int) $_SESSION['user']->id) || ($chall->uid == $debug_userid)) {
                    $me_array[] = $filename;
                }

                $set = substr($key, -4);
                $data = $value->{"py/tuple"};

                if ($which == 'kendall') {
                    $j_array[$set][] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$data[3], 'moe'=>$data[4], 'n'=>$data[0]);
                } elseif ($which == 'rmsd') {
                    $j_array[$set][] = array('label'=>$filename, 'Receipt'=>$filename, 'kendall'=>$data[5], 'moe'=>$data[6], 'n'=>$data[0]);
                }

            }
        }
    }

// Define the custom sort function
     function custom_sort_asc($a,$b) {
          return $a['kendall']>$b['kendall'];
     }

     function custom_sort_desc($a,$b) {
          return $a['kendall']<$b['kendall'];
     }

     if ($_GET['set']=="all") {
        // sort the values within each Set
        for ($s=1; $s<=3; $s++) {
            if ($_GET['q'] == 'rmsd')
                usort($j_array['Set' . $s], "custom_sort_asc");
            elseif ($_GET['q'] == 'kendall')
                usort($j_array['Set' . $s], "custom_sort_desc");

        }

        $merge_set = array();

        for ($s=1; $s<=3; $s++) {
            $merge_set = array_merge($merge_set, $j_array['Set' . $s]);   
        }

    } else {
        // Sort the multidimensional array
        if ($_GET['q'] == 'rmsd')
            usort($j_array['Set' . $_GET['set']], "custom_sort_asc");
        elseif ($_GET['q'] == 'kendall')
            usort($j_array['Set' . $_GET['set']], "custom_sort_desc");

        $merge_set = $j_arrray['Set' . $_GET['set']];

    }

    //         echo json_encode($j_array['Set' . $_GET['set']]);
    $echo_data['numerics'] = $merge_set;
    $echo_data['flags'] = $me_array;

    echo json_encode($echo_data);
    exit;
?>
