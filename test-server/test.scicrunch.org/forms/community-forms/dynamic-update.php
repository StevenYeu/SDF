<?php

include '../../classes/classes.php';
\helper\scicrunch_session_start();

if (!isset($_SESSION['user'])) {
    header('location:/');
    exit();
}

$id = filter_var($_GET['id'],FILTER_SANITIZE_NUMBER_INT);

foreach ($_POST as $key => $value) {
    if ($key == 'content'){
        $vars[$key] = $value;
    } else
        $vars[$key] = filter_var($value, FILTER_SANITIZE_STRING);
}

$data = new Component_Data();
$data->getByID($id);

if ($_SESSION['user']->levels[$data->cid] < 2) {
    header('location:/');
    exit();
}

$community = new Community();
$community->getByID($data->cid);

if(!$community->isTrusted() && $_SESSION["user"]->role < 1) $vars = \helper\sanitizeHTMLString($vars);

$data->title = $vars['title'];
$data->color = 0000;
$data->description = $vars['description'];
$data->content = $vars['content'];

$data->updateDB();

$splits = explode(',',$vars['tags']);
$data->wipeTags();
$data->insertTags($splits);

$notification = new Notification();
$notification->create(array(
    'sender' => 0,
    'receiver' => $_SESSION['user']->id,
    'view' => 0,
    'cid' => $community->id,
    'timed'=>0,
    'start'=>time(),
    'end'=>time(),
    'type' => 'update-scicrunch-content',
    'content' => 'Successfully updated ' . $data->title
));
$notification->insertDB();
$_SESSION['user']->last_check = time();

header('location:/'.$community->portalName.'/account/communities/'.$community->portalName);

?>
