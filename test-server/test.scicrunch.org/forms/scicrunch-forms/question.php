<?php

include '../../classes/classes.php';
\helper\scicrunch_session_start();

if (!isset($_SESSION['user']) || $_SESSION["user"]->role < 1) {
    header('location:/');
    exit();
}

foreach ($_POST as $key => $value) {
    if ($key == 'content'){
        $vars[$key] = $value;
    } else
        $vars[$key] = filter_var($value, FILTER_SANITIZE_STRING);
}

$cid = filter_var($_GET['cid'],FILTER_SANITIZE_NUMBER_INT);

$data = new Component_Data();

$vars['uid'] = $_SESSION['user']->id;
$vars['cid'] = $cid;
$vars['component'] = 104;
$vars['color'] = '000000';

$data->create($vars);
$data->insertDB();

$splits = explode(',',$vars['tags']);
$data->insertTags($splits);

$notification = new Notification();
$notification->create(array(
    'sender' => 0,
    'receiver' => $_SESSION['user']->id,
    'view' => 0,
    'cid' => 0,
    'timed'=>0,
    'start'=>time(),
    'end'=>time(),
    'type' => 'add-scicrunch-question',
    'content' => 'Successfully asked the Question'
));
$notification->insertDB();

$_SESSION['user']->last_check = time();



header('location:/faq');

?>
