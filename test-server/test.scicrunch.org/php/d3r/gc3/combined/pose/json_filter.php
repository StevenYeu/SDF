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
    if ($ligand == "Mean") {
        $avg = true;
    } else {
        $avg = false;
    }

    if ($ligand == "Median") {
        $median = true;
    } else {
        $median = false;
    }

    $chart = $_GET['chart'];
    $component = $_GET['component'];
    $results = $_GET['results'];
    $partial = $_GET['partial'];

    $abbr = 'catS';
    $max_compounds = 24; // exclude FXR_33

    if ($component == 968)
        $file = "data/D3R_GC3_CatS_1a_Website_BestChainRMSD.csv";
    elseif ($component == 972)
        $file = "data/D3R_GC3_CatS_1b_Website_BestChainRMSD.csv";
        
    $orderby = "asc";


    // Read the data from CSV file
    $row = 1;
    if (($handle = fopen($file, "r")) !== FALSE) {
        $py_array = array();
        while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
            $num = count($data);
            $row++;

            // can't guarantee that all data will come sorted, so let's push them all into a big array and deal with keys
            $py_array[$data[0]][$data[1]][] = array($data[2], $data[3]);
        }
        fclose($handle);
    }        

    // build an assoc array with CSV data
    foreach (array_keys($py_array) as $receipt_id) {
        foreach (array_keys($py_array[$receipt_id]) as $aligand) {
            $temp_array = array();
        
            foreach ($py_array[$receipt_id][$aligand] as $pair) {
                $temp_array[] = array('py/tuple'=>$pair);
            }

            $build_json_array[$receipt_id][$aligand] = $temp_array;
        }
    }

    // encode the array. will decode later. encode/decode is more obj friendly ?
    $json =  json_encode($build_json_array);

    $j_array = array();
    $me_array = array();
    $less_array = array();
    
    // basically, for each person loop thru ligand values
    foreach (json_decode($json) as $filename=>$value) {
        $person_array = array();
        $min_rmsd = 10000000;
        
        $rmsd_sum = 0;

        $chall = new Challenge_Submission();
        $chall->GetUserInfoFromReceipt($filename);

        if (($chall->uid == (int) $_SESSION['user']->id) || ($chall->uid == $debug_userid)) {
            $me_array[] = $filename;
        }

        $vars = get_object_vars ( $value );
        // check and see if file has all compounds
        if (sizeof($vars) < $max_compounds)
            $less_array[] = $filename;
                
        foreach($vars as $key=>$value) {
            $pose_count = 0; // reset pose count for each compound/ligand
            if (!($avg)) {
                if ($key == $ligand) {
                    foreach ($value as $idx) {
                    
                        $goo = $idx->{"py/tuple"};
                        
                        if ($chart == 'closest') {
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

                    if ($chart == 'closest') {
                        $j_array[] = array('label'=>$filename . " (" . $userid . ")", 'frequency'=>$min_rmsd);
                        //$mini[shortenReceiptID($filename)] = $min_rmsd;
                        $mini[$filename] = $min_rmsd;
                    } elseif ($chart == 'avg') {
                        $j_array[] = array('label'=>$filename . " (" . $userid . ")", 'frequency'=>round($rmsd_sum/$pose_count, 2));
                        //$mini[shortenReceiptID($filename)] = round($rmsd_sum/$pose_count, 3);
                        $mini[$filename] = round($rmsd_sum/$pose_count, 2);
                    }
                 }
            } 

        }

        if ($avg) {
            $tempo = get_average_data($vars, $chart, $max_compounds);
            $mini[$filename] = $tempo->avg;
        } elseif ($median) {
            $tempo = get_average_data($vars, $chart, $max_compounds);
            $mini[$filename] = $tempo->median;
        }        
            /*
            if (isset($tempo->lessthan))
                $less_array[] = $filename;
               */ 

        
        /* else {
            $tempo = get_less_than($vars, $chart, $max_compounds);
            if (isset($tempo->lessthan))
                $less_array[] = $filename;
        } 
        */ 
    }

    if ($orderby == "asc")
        asort($mini);
    else
        arsort($mini);

    $e = array();

    foreach ($mini as $key=>$value) {
        $e[] = array('label'=>$key, 'frequency'=>$value);
    }

    $data['flags'] = $me_array;

        $diff1 = array_diff($e, $less_array); // in $e but not in $less_array
    if ($partial) {
        $diff2 = array_diff($less_array, $e); // in $less_array but not in $e
//        $data['numerics'] = array_merge($diff1, $diff2);
        foreach ($e as $tester) {
            if (in_array($tester['label'], $less_array))
                $data['numerics'][] = $tester;
        }
        

    } else {
        $data['mine_less'] = array_intersect($me_array, $less_array);
        $data['anon_less'] = $less_array;
        
        foreach ($e as $tester) {
            if (!(in_array($tester['label'], $less_array)))
                $data['numerics'][] = $tester;
        }
    }

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
