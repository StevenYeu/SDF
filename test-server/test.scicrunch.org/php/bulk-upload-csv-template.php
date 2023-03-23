<?php require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/classes.php";?>

<?php
$rf = new Resource_Fields();
$arrlength = sizeof($rf->getByType(1,0));
$headerNames = array("Action", "Resource ID");
for($i = 0; $i < $arrlength; $i++) {
    $temp = $rf->getByType(1,0);
    $headerNames[$i+2] = $temp[$i]->name;
}
header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=bulk-upload-template.csv");

$list = array(
    $headerNames,
    array("add","","required","required","required","","","","","","","","","","","","","","","","","","","","","","","","", "", ""),
    array("update","required","","","","","","","","","","","","","","","","","","","","","","","","","","","", "", ""),
    array("skip","","","","","","","","","","","","","","","","","","","","","","","","","","","","", "", "")
);
$output = fopen("php://output", "w");

foreach ($list as $fields) {
    fputcsv($output, $fields);
}

fclose($output);

?>
