<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/classes.php";
session_start();

require_once $_SERVER["DOCUMENT_ROOT"] . "/api-classes/datasets.php";

$templateid = $_GET["templateid"];
if(is_null($templateid)) {
    header("X-PHP-Response-Code: 400", true, 400);
    echo "templateid is required";
    exit;
}

$user = $_SESSION["user"];
$fields_data = getTemplateFields($user, NULL, $templateid);
if(!$fields_data->success) {
    header("X-PHP-Response-Code: " . $fields_data->status_code, true, $fields_data->status_code);
    echo $fields_data->status_msg;
    exit;
}
usort($fields_data->data, function($a, $b) {
    if($a->position < $b->position) return -1;
    if($a->position > $b->position) return 1;
    return 0;
});

$fields = Array();
$ilxs = Array();
foreach($fields_data->data as $field) {
    $fields[] = $field->name;
    $ilxs[] = $field->term()->ilxFormatted();
}

$template = DatasetFieldTemplate::loadBy(Array("id"), Array($templateid));

header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=" . $template->name . ".csv");
$output = fopen("php://output", "w");
fputcsv($output, $fields);
fputcsv($output, $ilxs);
fclose($output);

?>
