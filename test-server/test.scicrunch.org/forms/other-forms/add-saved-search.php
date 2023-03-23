<?php

include '../../classes/classes.php';
require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/update_subscription.php";
\helper\scicrunch_session_start();

if (!isset($_SESSION['user'])) {
    header('location:/');
    exit();
}

$vars['uid'] = $_SESSION['user']->id;

foreach($_POST as $key => $value){
    $vars[$key] = filter_var($value,FILTER_SANITIZE_STRING);
}

$community = new Community();
$community->getByID($vars['cid']);

$saved = new Saved();
if($_SESSION["user"]->role < 1) $vars = \helper\sanitizeHTMLString($vars);
$saved->create($vars);
$saved->insertDB();


$notification = new Notification();
$notification->create(array(
    'sender' => 0,
    'receiver' => $_SESSION['user']->id,
    'view' => 0,
    'cid' => $community->id,
    'timed'=>0,
    'start'=>time(),
    'end'=>time(),
    'type' => 'save-search',
    'content' => 'Successfully saved the search'
));
$notification->insertDB();
$_SESSION['user']->last_check = time();
updateSubscription($_SESSION['user'], NULL, "subscribe", $saved->nifServicesType(), $saved->id);

$previousPage = $_SERVER['HTTP_REFERER'];
header('location:'.$previousPage);

?>
