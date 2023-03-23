<?php

include '../../classes/classes.php';
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/connection.class.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/term.class.php";

\helper\scicrunch_session_start();

if (!isset($_SESSION['user'])) {
    header('location:/');
    exit();
}

$previousPage = $_SERVER['HTTP_REFERER'];
$id = filter_var($_GET['notif_id'],FILTER_SANITIZE_NUMBER_INT);
$send_notification = filter_var($_GET['action'],FILTER_SANITIZE_NUMBER_INT);

if(!isset($id)){
    header('location:'.$previousPage);
    exit();
}

$cxn = new Connection();
$cxn->connect();
$cxn->update("term_notifications", "ii", Array("send_notification"), Array($send_notification, $id),"where id=?");
$cxn->close();

$previousPage = $_SERVER['HTTP_REFERER'];
header('location:'.$previousPage);

?>
