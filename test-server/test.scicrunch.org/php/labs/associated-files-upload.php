<?php
//error_reporting(E_ALL);
//ini_set("display_errors", 1);

require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/classes.php";

session_start();
$user = $_SESSION["user"];

/* Getting file name */
$filename = $_FILES['file']['name'];

// don't know if it'll be .doc or .docx, so look at last 4. pdf and csv will always be just 3
if (substr($filename, -4) == 'docx')
    $extension = "docx";
else
    $extension = substr($filename, -3);

$filename = $_GET['type'] . "_" . $_GET['dataset_id'] . "_" . time() . "." . $extension;

/* Location */
$location = $_SERVER["DOCUMENT_ROOT"] . '/../doi-datasets/dataset_' . $_GET['dataset_id'];
if (!file_exists($location)) {
    mkdir($location, 0755);
}

/* Upload file */
move_uploaded_file($_FILES['file']['tmp_name'],$location . "/" . $filename);

$file = DatasetAssociatedFiles::createNewObj($_SESSION['user']->id, $_GET['dataset_id'], $_GET['type'], $filename);

$arr = array("status"=>"success", "name"=>$filename, "orig"=>$_FILES['file']['name']);
echo json_encode($arr);

