<?php 

//error_reporting(E_ALL);
//ini_set("display_errors", 1);
include('../../../classes/classes.php');

$challenge = new Challenge;

// get all challenges within specified time range
// should work for any number of weeks since it just loops thru results
// i don't even have to keep track of what week the file came from
$rmsds = array('LMCSS', 'SMCSS', 'hiResApo', 'hiResHolo', 'hiTanimoto');

        $binmax = 10;
        $binmax100 = $binmax * 100;
        $binmax101 = $binmax100 + 1;
        $binincrement = 1;

$masdf = 0;
    // sum of targets for the weeks
    // options are 1, 3, all.
    // 1 = use max yearweek
    // 3 = use max yearweek - 3
    // all = use min yearweek
    $yearweek = $challenge->getCELPPYearWeek($_GET['weeks']);

if ($results = $challenge->getCELPPjsonByWeeksAgo($yearweek)) {
    // $results will almost always have multiple submissions
    // let's take each submission at a time as $row
    // first loop is just for initialization
    foreach ($results as $row) {
        $row['submission_folder'] = str_replace(".extsubmission.evaluation", "", $row['submission_folder']);
        $row['submission_folder'] = str_replace("stage.7.", "", $row['submission_folder']);

        // set bigloop variables to 0 for each submission_folder
        $bigloop[$row['submission_folder']]['targets'] = 0;
        $bigloop[$row['submission_folder']]['count'] = 0;
    }
    // now for the nitty gritty
    foreach ($results as $row) {
        $row['submission_folder'] = str_replace(".extsubmission.evaluation", "", $row['submission_folder']);
        $row['submission_folder'] = str_replace("stage.7.", "", $row['submission_folder']);

        if (substr($row['uid_folder'], 0, 5) == '33567') {
            $folder_split = explode("_", $row['uid_folder']);
            $row['submission_folder'] = $folder_split[1];
        }
        // 
        $singleloop['targets'] = 0;
        $singleloop['sum'] = 0;
    
        // let's focus on just the RMSD data in the json field
        // format has many PDBs, and each a bunch of pairs of RMSD types/values
        $celpp_array = json_decode($row['json']);
    
        // rmsd.json starts at the PDB level ... this loop is for just one person, so not using 'submission_folder'
//        echo "hit me<br />\n";
        foreach ($celpp_array as $pdb=>$pdb_values) {
            // for each PDB, there should be a LMCSS_ori that we have to test
            if (($pdb_values->LMCSS_ori < 5) || ($pdb_values->LMCSS_ori ='')) {

            // since LMCSS_ori is valid, go ahead and add number to array ...
                // for each PDB, it has all the rmsd types=>value

                foreach ($pdb_values as $rmsd=>$value) {
                    if ($rmsd == $_GET['rmsd']) {
                        if (is_numeric($value)) {
                            $buildfile .= $row['submission_folder'] . "," . $value . "\n";
                            $buildarray[$row['submission_folder']][] = $value;
#                                $buildfile[] = rando() . "," . $value . "\n";
                        }
                    }
                }
            } else
                continue;
        }

    }
}

// let's do this for "ifcz" ...
if ($results = $challenge->getCELPPjsonByWeeksAgo($yearweek)) {
    // $results will almost always have multiple submissions
    // let's take each submission at a time as $row

    // now for the nitty gritty
    foreach ($results as $row) {
        // 
        $singleloop['targets'] = 0;
        $singleloop['sum'] = 0;
    
        // let's focus on just the RMSD data in the json field
        // format has many PDBs, and each a bunch of pairs of RMSD types/values
        $celpp_array = json_decode($row['json']);
    
        // rmsd.json starts at the PDB level ... this loop is for just one person, so not using 'submission_folder'
//        echo "hit me<br />\n";
        foreach ($celpp_array as $pdb=>$pdb_values) {
            if ($pdb !== "ifcz")
                continue;

            // for each PDB, there should be a LMCSS_ori that we have to test
            if (($pdb_values->LMCSS_ori < 5) || ($pdb_values->LMCSS_ori ='')) {

            // since LMCSS_ori is valid, go ahead and add number to array ...
                // for each PDB, it has all the rmsd types=>value

                foreach ($pdb_values as $rmsd=>$value) {
                    if ($rmsd == $_GET['rmsd']) {
                        if (is_numeric($value)) {
                            $buildfile .= $row['submission_folder'] . "," . $value . "\n";
                            $buildarray[$row['submission_folder']][] = $value;
#                                $buildfile[] = rando() . "," . $value . "\n";
                        }
                    }
                }
            } else
                continue;
        }

    }
}
echo "date,value\n";

//print_r($buildarray);

foreach (array_keys($buildarray) as $folder) {
    $map[$folder] = $folder . " (" . sizeof($buildarray[$folder]) . ")";
    $ratio[$folder] = sizeof($buildarray[$folder]);
}

//print_r($ratio);
arsort($ratio);
//print_r($ratio);

foreach (array_keys($ratio) as $folder) {
//    $buildfile = str_replace($folder . ",", $map[$folder] . ",", $buildfile);

    foreach ($buildarray[$folder] as $line) {
        echo $map[$folder] . "," . $line . "\n";
    }
}

//print_r($ratio);exit;
//echo $buildfile;

function rando() {
    return random_int(1, 15);
}
exit;
            

?>