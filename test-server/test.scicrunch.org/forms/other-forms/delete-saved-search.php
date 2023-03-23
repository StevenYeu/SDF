<?php

include '../../classes/classes.php';
require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/update_subscription.php";

\helper\scicrunch_session_start();

if (!isset($_SESSION['user'])) {
    header('location:/');
    exit();
}

$previousPage = $_SERVER['HTTP_REFERER'];
$id = filter_var($_GET['id'],FILTER_SANITIZE_NUMBER_INT);

if(!isset($id)){
    header('location:'.$previousPage);
    exit();
}

$saved = new Saved();
$saved->getByID($id);

if(!$saved->id || $saved->uid != $_SESSION['user']->id){
    header('location:'.$previousPage);
    exit();
}

updateSubscription($_SESSION["user"], NULL, "unsubscribe", $saved->nifServicesType(), $saved->id);
$saved->deleteDB();

$notification = new Notification();
$notification->create(array(
    'sender' => 0,
    'receiver' => $_SESSION['user']->id,
    'view' => 0,
    'cid' => $saved->cid,
    'timed'=>0,
    'start'=>time(),
    'end'=>time(),
    'type' => 'delete-search',
    'content' => 'Successfully deleted that saved search'
));
$notification->insertDB();
$_SESSION['user']->last_check = time();

$previousPage = $_SERVER['HTTP_REFERER'];
header('location:'.$previousPage);

?>
