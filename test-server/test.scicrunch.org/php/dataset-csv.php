<?php
ini_set("max_execution_time", 300);

require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/classes.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/api-classes/datasets.php";
session_start();

if (isset($saveonly)) {
    $filesizee = $saveonly["filesizee"];
    $datasetid = $saveonly["datasetid"];
    $include_ilx = false;
    $no_data = false;    
} else {
    $datasetid = $_GET["datasetid"];
    $include_ilx = $_GET["ilx"] ? true : false;
    $no_data = $_GET["template-only"] ? true : false;
}

if(is_null($datasetid)) {
    header("X-PHP-Response-Code: 400", true, 400);
    echo "datasetid is required";
    exit;
}

$user = $_SESSION["user"];

$dataset = Dataset::loadBy(Array("id"), Array($datasetid));
if(is_null($dataset)) {
    header("X-PHP-Response-Code: 400", true, 400);
    echo "dataset not found";
    exit;
}

// add to logs
ScicrunchLogs::createNewObj($dataset->lab()->cid, $_SESSION['user']->id, $datasetid, 'dataset', 'dataset download', $_SERVER['REQUEST_URI']);    

// for $saveonly case (curator tool), bypass permissions by using dataset owner's ID ...
if (isset($saveonly)) {
    $user = new User();
    $user->getByID($dataset->uid);
}

$fields_data = getDatasetFields($user, NULL, $dataset->id);
if(!$fields_data->success) {
    header("X-PHP-Response-Code: " . $fields_data->status_code, true, $fields_data->status_code);
    echo $fields_data->status_msg;
    exit;
}

/* use api endpoint function so that permissions can be checked */
$results_data = datasetSearch($user, NULL, $dataset->id, "", 0, 1);
if(!$results_data->success) {
    header("X-PHP-Response-Code: " . $results_data->status_code, true, $results_data->status_code);
    echo $results_data->status_msg;
    exit;
}

$count = $results_data->data["count"];

$batch_size = $count;
$fields = $dataset->fields();
usort($fields, function($a, $b) {
    if($a->position < $b->position) return -1;
    if($a->position > $b->position) return 1;
    return 0;
});
$field_row = Array();
$ilx_row = Array();
foreach($fields as $field) {
    $field_row[] = $field->name;
    if($include_ilx) {
        $ilx_row[] = $field->term()->ilxFormatted();
    }
}

/* let's try this ... */

//$records = $dataset->searchRecords("", $index, $batch_size, true, $field_row);

//die("Stay here!");



if (isset($saveonly)) {
    $outfile =  "dataset_" . $dataset->id . "_" . time() . ".csv";
    $output = fopen($saveonly["path"] . $outfile, "w");
    $saveonly["outfile"] = $outfile;
} else {
    header("Content-Type: application/csv");
    header("Content-Disposition: attachment; filename=\"" . $dataset->name . ".csv" . "\"");
    $output = fopen("php://output", "w");
}    
fputcsv($output, $field_row);
if($include_ilx) {
    fputcsv($output, $ilx_row);
}

if(!$no_data) {
    $records = $dataset->searchRecords("", $index, $batch_size, true, $field_row);
    fwrite($output, $records);
}
fflush($output);

fclose($output);

if (isset($saveonly))
    $filesizee = filesize($saveonly["path"] . $outfile);
    $saveonly["filesizee"] = $filesizee;
?>
