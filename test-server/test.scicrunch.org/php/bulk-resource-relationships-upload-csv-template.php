<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/classes.php";?>

<?php
$headerNames = array("Action", "id1", "reltype", "id2");

header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=bulk-resource-relationships-upload-template.csv");

$list = array(
    $headerNames,
    array("add","required","required","required"),
    array("skip","","",""),
);
$output = fopen("php://output", "w");

foreach ($list as $fields) {
    fputcsv($output, $fields);
}

fclose($output);

?>
