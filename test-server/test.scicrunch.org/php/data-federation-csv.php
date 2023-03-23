<?php

require_once __DIR__ . "/../classes/classes.php";
$query_string = preg_replace("/&nifid=[^&]+/", "", $_SERVER["QUERY_STRING"]);

if(!isset($_GET["nifid"])) {
    goBack();
}
$nifid = $_GET["nifid"];
$source = new Sources();
$source->getByView($nifid);
if(!$source->id) {
    goBack();
}

$url = ENVIRONMENT . "/v1/federation/data/" . $source->nif . ".json?";
$url .= $query_string;
$results = Connection::multi(Array($url));
if(!$results[0]) {
    goBack();
}
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


function goBack() {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

?>
