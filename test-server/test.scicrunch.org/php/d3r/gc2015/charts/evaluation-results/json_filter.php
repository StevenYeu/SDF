<?php

    /*
        This file takes the JSON data converted from a Python Pickle file and processes
        into a format that D3.js can use for charts. It handles the various result types:
        rmsd, rsr, rscc ...
    */

    include('../../../../../classes/classes.php');
    include("average_function.php");

    // change this to see the user's bars
    if (isset($_GET['debug']))
        $debug_userid = $_GET['debug'];
    else
        $debug_userid = -1;
    
    \helper\scicrunch_session_start();
    error_reporting(0);

    $ligand = $_GET['ligand'];
    if ($ligand == "AVG") {
        $avg = true;
    } else {
        // ligand # is after "_", like in HSP90_179 or MAP_01 . can be 2 or 3 digits
        $li_split = explode("_", $ligand);
        $ligand = $li_split[1];
        $avg = false;
    }
    $chart = $_GET['chart'];
    $component = $_GET['component'];
    $results = $_GET['results'];

    if ($component == 279)
        $abbr = 'hsp90';
    elseif ($component == 280)
        $abbr = 'map4k4';

    // get the right json data file
    switch ($results) {
        case "rmsd": // this is what was originally called "docking"
            $file = "data/" . $abbr . "_rmsd_rounded.json";
            $orderby = "asc";
            break;
/*
remove rsr and rscc for now
        case "rsr":
            $file = "data/" . $abbr . "_rsr.json";
            $orderby = "asc";
            break;

        case "rscc":
            $file = "data/" . $abbr . "_rscc.json";
            $orderby = "desc";
            break;
*/            
    }

    $jsonpickle = file_get_contents($file);
    $json = substr($jsonpickle, 2, strlen($jsonpickle) - 2);
    $json = substr($json, 0, strlen($json) - 6);

    $j_array = array();
    $me_array = array();
    
    // basically, for each person loop thru ligand values
    foreach (json_decode($json) as $filename=>$value) {
        $person_array = array();
        $min_rmsd = 10000000;
        
        $rmsd_sum = 0;

        // get filename without the "_2nd" if it's there
        list($filename) = explode("_", $filename);

        $chall = new Challenge_Submission();
        $chall->GetUserInfoFromReceipt($filename);

        if (($chall->uid == (int) $_SESSION['user']->id) || ($chall->uid == $debug_userid)) {
            $me_array[] = $filename;
        }

        $vars = get_object_vars ( $value );

        foreach($vars as $key=>$value) {
            // skip if HSP90_44
            if ($key == 44)
                continue;

            $pose_count = 0; // reset pose count for each compound/ligand
            if (!($avg)) {
                if ($key == $ligand) {
                    foreach ($value as $idx) {
                        $goo = $idx->{"py/tuple"};
                        if ($chart == 'best') {
                            $min_rmsd = min($min_rmsd, $goo[0]);
                        } elseif ($chart == 'avg') {
                            $pose_count++;
                            $rmsd_sum += $goo[0];
                        } else {
                            // for "pose", only save for pose = 1
                            // fix the code - false assumption that pose 1 is always first!
                            if ($goo[1] == "1") {
                                $j_array[] = array('label'=>$filename . " (" . $userid . ")", 'frequency'=>$goo[0]);
                                //$mini[shortenReceiptID($filename)] = $goo[0];
                                $mini[$filename] = $goo[0];
                            }
                            
                        }
                    }

                    if ($chart == 'best') {
                        $j_array[] = array('label'=>$filename . " (" . $userid . ")", 'frequency'=>$min_rmsd);
                        //$mini[shortenReceiptID($filename)] = $min_rmsd;
                        $mini[$filename] = $min_rmsd;
                    } elseif ($chart == 'avg') {
                        $j_array[] = array('label'=>$filename . " (" . $userid . ")", 'frequency'=>round($rmsd_sum/$pose_count, 3));
                        //$mini[shortenReceiptID($filename)] = round($rmsd_sum/$pose_count, 3);
                        $mini[$filename] = round($rmsd_sum/$pose_count, 3);
                    }
                 }
            } 

        }

        if ($avg) {
            $mini[$filename] = get_average_data($vars, $chart);
        }

    }

    if ($orderby == "asc")
        asort($mini);
    else
        arsort($mini);

    $e = array();

    foreach ($mini as $key=>$value) {
        $e[] = array('label'=>$key, 'frequency'=>$value);
    }

    $data['numerics'] = $e;
    $data['flags'] = $me_array;

    echo json_encode($data);
    exit;

    // some receiptID's might have "_2nd" appended, so can't just use last 4 char.
    function shortenReceiptID($filename) {
        if (substr($filename, -4) == "_2nd")
            $filename = substr($filename, -9);
        else
            $filename = substr($filename, -5);

        return "... " . $filename;
    }

?>
