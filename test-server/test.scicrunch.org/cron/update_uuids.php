<?php
//include_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";
$docroot = "..";
include_once "../classes/classes.php";
\helper\scicrunch_session_start();

//if(!isset($_SESSION['user']) || $_SESSION['user']->role == 0){
//    header("location: /");
//    exit;
//}

Resource::updateUUIDs();

?>
