<?php

include '../../classes/classes.php';
\helper\scicrunch_session_start();

$cid = filter_var($_GET['cid'], FILTER_SANITIZE_NUMBER_INT);
if (!isset($_SESSION['user']) || !isset($_SESSION["user"]->levels[$cid]) || $_SESSION["user"]->levels[$cid] < 2) {
    header('location:/');
    exit();
}

$community = new Community();
$community->getByID($cid);

$x = $_GET["x"] == "" ? NULL : filter_var($_GET['x'],FILTER_SANITIZE_NUMBER_INT);
$y = $_GET["y"] == "" ? NULL : filter_var($_GET['y'],FILTER_SANITIZE_NUMBER_INT);
$z = $_GET["z"] == "" ? NULL : filter_var($_GET['z'],FILTER_SANITIZE_NUMBER_INT);
$type = filter_var($_GET['type'],FILTER_SANITIZE_STRING);

$category = new Category();
$category->x = $x;
$category->y = $y;
$category->z = $z;
$category->cid = $cid;
$category->deleteType($type);

$notification = new Notification();
$notification->create(array(
    'sender' => 0,
    'receiver' => $_SESSION['user']->id,
    'view' => 0,
    'cid' => $community->id,
    'timed'=>0,
    'start'=>time(),
    'end'=>time(),
    'type' => 'community-' . $return . '-delete',
    'content' => 'Successfully deleted the ' . $return . ' in : ' . $community->name
));
$notification->insertDB();
$_SESSION['user']->last_check = time();

header('location:' . $_SERVER["HTTP_REFERER"]);

?>
