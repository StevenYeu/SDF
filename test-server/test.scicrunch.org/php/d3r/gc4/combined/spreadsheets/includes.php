<?php

    $sub_data = array("1147"=>"Cathepsin S", "1146"=>"BACE1a", "1470"=>"BACE1b", "1479"=>"BACE2");
    if ((isset($_GET['component'])) && (in_array($_GET['component'],  array_keys($sub_data)))) {
        $component = $_GET['component'];
        if ($component == 1146)
            $dataname = "BACE (Stage 1A)";
        elseif ($component == 1470)
            $dataname = "BACE (Stage 1B)";
        elseif ($component == 1479)
            $dataname = "BACE (Stage 2)";
        else {
//            $dataname = $sub_data[$component];
            $dataname = 'CATS';
        }
    } else {
        echo "Component ID is invalid or missing.";
        exit;
    }

    if (isset($_GET['group']) && ($_GET['group'] == 'xray'))
        $group = 'xray';
    else
        $group = 'default';

    if ($dataname == 'CATS')
        $abbr = 'CATS';
    elseif ($group == 'xray')
        $abbr = 'BACE1XrayOnly';
    else
        $abbr = 'BACE1';

    $file = '../spreadsheets/newcsvs/' . $abbr . "_" . $component . "_";
    if (($_GET['method'] == 'ligand') || ($_GET['method'] == 'structure') || ($_GET['method'] == 'combined'))
        $method = $_GET['method'] . "_based";
    elseif (strpos($_SERVER['SCRIPT_NAME'], "free"))
        $method = 'free_energy';
    else
        $method = 'pose_prediction';
        
    if ($method == 'pose_prediction') {
        $file .= $method . "_methods.csv";
        $include_method == 'pose';
    } else {
        $file .= $method . "_scoring_methods.csv";
        $include_method == 'scoring';
    }

    if (isset($_GET['partial']) && ($_GET['partial']))
        $file = str_replace(".csv" , "_partial.csv", $file);
    else
        $file = str_replace(".csv" , "_complete.csv", $file);

?>    