<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/classes.php';
\helper\scicrunch_session_start();

if(!isset($_SESSION['user']) || $_SESSION['user']->role == 0){
    header("location: /");
    exit;
}

$cxn = new Connection();
$cxn->connect();
$cxn->clearSearchCache();
$cxn->close();

$previous = $_SERVER['HTTP_REFERER'];
header("location: " . $previous);
exit;

?>
