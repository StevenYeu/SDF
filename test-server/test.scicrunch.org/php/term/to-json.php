<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/term.class.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api-classes/term/term_by_ilx.php";

session_start();
$ilx = $_GET["ilx"];
//echo $ilx;

$api_key = null;
$user = $_SESSION['user'];
//print_r($user);

$term = getTermByIlx($user, $api_key, $ilx, 1, 1);
$term = DbObj::termForExport($term);

echo header('Content-Type: application/json');
print json_encode($term);


?>
