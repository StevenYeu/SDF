<?php

include '../../classes/classes.php';
\helper\scicrunch_session_start();

if (!isset($_SESSION['user']) && $_SESSION["user"]->levels[$cid] < 2) {
    header('location:/');
    exit();
}

$cid = filter_var($_GET['cid'],FILTER_SANITIZE_NUMBER_INT);
$type = filter_var($_GET['type'],FILTER_SANITIZE_STRING);
$pastCat = filter_var($_POST['past-category'],FILTER_SANITIZE_STRING);
$pastSub = filter_var($_POST['past-subcategory'],FILTER_SANITIZE_STRING);
$cat = filter_var($_POST['category'],FILTER_SANITIZE_STRING);
$sub = filter_var($_POST['subcategory'],FILTER_SANITIZE_STRING);

$community = new Community();
$community->getByID($cid);
if(!$community->isTrusted() && $_SESSION["user"]->role < 1) $cat = \helper\sanitizeHTMLString($cat);
if(!$community->isTrusted() && $_SESSION["user"]->role < 1) $sub = \helper\sanitizeHTMLString($sub);

$category = new Category();
$category->updateName($cid,$type,$pastCat,$pastSub,$cat,$sub);

$notification = new Notification();
$notification->create(array(
    'sender' => 0,
    'receiver' => $_SESSION['user']->id,
    'view' => 0,
    'cid' => $community->id,
    'timed'=>0,
    'start'=>time(),
    'end'=>time(),
    'type' => 'community-name-update',
    'content' => 'Successfully updated the name from : ' . $community->name
));
$notification->insertDB();
$_SESSION['user']->last_check = time();

header('location:/' . $community->portalName . '/account/communities/' . $community->portalName.'/sources');

?>
