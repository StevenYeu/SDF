<?php

include '../classes/classes.php';
\helper\scicrunch_session_start();
$previousPage = $_SERVER['HTTP_REFERER'];

if(!isset($_SESSION['user'])){
    header('location:/');
    exit();
}

$_SESSION["user"]->updateORCIDData();

header("location: " . $previousPage);

?>
