<?php

require_once __DIR__ . "/../classes/classes.php";
\helper\scicrunch_session_start();

//IS THIS HOW TO PARSE FROM URL?
$id = filter_var($_GET['collection'],FILTER_SANITIZE_NUMBER_INT);
$view = filter_var($_GET['view']);

if(!isset($_SESSION['user']) || !$_SESSION['user']->collections[$id]){
    goBack();
}
//NEED QUERY STRING?
//$query_string = preg_replace("/&nifid=[^&]+/", "", $_SERVER["QUERY_STRING"]);

/*
if(!isset($_GET["nifid"])) {
    exit();
}
$nifid = $_GET["nifid"];
$source = new Sources();
$source->getByView($nifid);
if(!$source->id) {
    goBack();
}*/

//call to data services, returns json file, can filter by uuid
//need to also get collection id and make sure user actually own that collection
//  thru GET param
//if($_SESSION['user']->collections->uid != $id){
//    exit();
//}

//get every collected item with that uuid
//get uuid for each collected itemn and pass to data services as GET param

$source = new Sources();
$source->getByView($view);
if(!$source->id) {
    goBack();
}

$holder = new Item();
$collection = $_SESSION["user"]->collections[$id];
$collected = $holder->getByCollection($collection->id, $_SESSION["user"]->id);
$get_uuids= array();
foreach($collected as $collect){
    if($collect->view == $view && $collect->collection == $id){
        $get_uuids[] = $collect->uuid;
    }
}

$url = Connection::environment() . "/v1/federation/data/" . $source->nif . ".json?exportType=data&q=";
//$url .= $query_string;
$url .= implode("+OR+", $get_uuids);

$results = Connection::multi(Array($url));

if(!$results[0]) {
    goBack();
}

$json = json_decode($results[0], true);


$header = array_keys($json["result"]["result"][0]);
foreach($json["result"]["result"] as $row) {
    $row_data = Array();
    foreach($header as $h) {
        $row_data[] = strip_tags($row[$h]);
    }
    $rows[] = $row_data;
}
header("Pragma: no-cache");

header("Cache-Control: ");
header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=" . $source->source . "-" . $source->view . ".csv");
$output = fopen("php://output", "w");
fputcsv($output, $header);
foreach($rows as $row) {
    fputcsv($output, $row);
}
fclose($output);
/*
$source = new Sources();
//convert into table
//takes first row, makes header out of it
$json = json_decode($results[0], true);
$header = array_keys($json["result"]["result"][0]);
$rows = Array();
foreach($json["result"]["result"] as $row) {
    $row_data = Array();
    foreach($header as $h) {
        $row_data[] = strip_tags($row[$h]);
    }
    $rows[] = $row_data;
}

header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=" . $source->source . "-" . $source->view . ".csv");
$output = fopen("php://output", "w");
fputcsv($output, $header);
foreach($rows as $row) {
    fputcsv($output, $row);
}
fclose($output);
*/
//goBack();

function goBack() {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

?>
