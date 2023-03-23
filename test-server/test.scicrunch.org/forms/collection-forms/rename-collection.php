<?php

include '../../classes/classes.php';
\helper\scicrunch_session_start();

$id = filter_var($_POST['collection'], FILTER_SANITIZE_NUMBER_INT);
if (!isset($_SESSION['user']) || !$_SESSION['user']->collections[$id]) {
    header('location:/');
    exit();
}

$name = filter_var($_POST['name'],FILTER_SANITIZE_STRING);

if($_SESSION["user"]->role < 1) $name = \helper\sanitizeHTMLString($name);

$_SESSION['user']->collections[$id]->rename($name);

$notification = new Notification();
$notification->create(array(
    'sender' => 0,
    'receiver' => $_SESSION['user']->id,
    'view' => 0,
    'cid' => 0,
    'timed'=>0,
    'start'=>time(),
    'end'=>time(),
    'type' => 'collection-create',
    'content' => 'Successfully renamed the collection to: ' . $collection->name
));
$notification->insertDB();
$_SESSION['user']->last_check = time();

$previousPage = $_SERVER['HTTP_REFERER'];
header('location:' . $previousPage);

?>
