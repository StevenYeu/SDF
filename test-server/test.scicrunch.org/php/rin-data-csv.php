<?php

require_once __DIR__ . "/../classes/classes.php";

$query = $_GET["q"];
$per_page = 1000;
$page = 1;
$search_options = Array("filters" => Array(), "facets" => Array());
foreach($_GET["filter"] as $filter) {
    $search_options["filters"][] = explode(":", $filter, 2);
}
foreach($_GET["facet"] as $facet) {
    $search_options["facets"][] = explode(":", $facet, 2);
}
if($_GET["column"] && $_GET["sort"]) {
    $search_options["sort"] = Array("column" => $_GET["column"], "direction" => $_GET["sort"]);
}

$viewid = $_GET["viewid"];
if($viewid == "interlex") $view_manager = ElasticInterLexManager::managerByViewID("interlex");
else $view_manager = ElasticRRIDManager::managerByViewID($viewid);
if(is_null($view_manager)) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

$results = $view_manager->search($query, $per_page, $page, $search_options);
$fields = $view_manager->fields();

header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=" . $view_manager->getName(true) . ".csv");
$output = fopen("php://output", "w");

$header = Array();
foreach($fields as $field) {
    if(!$field->visible("table")) {
        continue;
    }
    $header[] = $field->name;
}
fputcsv($output, $header);

foreach($results as $result) {
    $row = Array();
    foreach($header as $h) {
        $fmt_value = $result->getField($h);
        if(count($fmt_value) > 1) $fmt_value = join(", ", $fmt_value);
        else if(count($fmt_value) == 0) $fmt_value = "";
        $row[] = trim($fmt_value);
        // $row[] = trim($result->getField($h));
    }
    fputcsv($output, $row);
}
fclose($output);

?>
