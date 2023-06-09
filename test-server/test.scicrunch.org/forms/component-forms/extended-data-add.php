<?php

include '../../classes/classes.php';
\helper\scicrunch_session_start();

$id = filter_var($_GET['data'], FILTER_SANITIZE_NUMBER_INT);

$data = new Component_Data();
$data->getByID($id);

$community = new Community();
$community->getByID($data->cid);

if (!isset($_SESSION['user']) || ($_SESSION['user']->levels[$data->cid]<2 && $_SESSION['user']->role<1)) {
    header('location:/');
    exit();
}

foreach ($_POST as $key => $value) {
    $vars[$key] = filter_var($value,FILTER_SANITIZE_STRING);
}

foreach ($_FILES as $key => $array) {
    $splits = explode('-', $key);
    if ($_FILES[$key] && $_FILES[$key]['error'] != 4) {
        $extension = end(explode(".", $_FILES[$key]["name"]));
        if ($_FILES[$key]["size"] < 100000000) { //100mb
            if ($_FILES[$key]["error"] > 0) {
                exit();
            } else {
                $vars['extension'] = strtoupper($extension);
                $name = $id.'_data_'.rand(0,1000000).'.'.$extension;
                file_put_contents('../../upload/extended-data/'.$name, file_get_contents($_FILES[$key]["tmp_name"]));
                @unlink($_FILES[$key]["tmp_name"]);
                $vars['file'] = $name;
            }
        } else {
            exit();
        }
    }
}

$vars['data'] = $id;
$vars['component'] = $data->component;
$vars['cid'] = $data->cid;
$vars['uid'] = $_SESSION['user']->id;

$extend = new Extended_Data();
if(!$community->isTrusted() && $_SESSION["user"]->role < 1) $vars = \helper\sanitizeHTMLString($vars);
$extend->create($vars);
$extend->insertDB();


$notification = new Notification();
$notification->create(array(
    'sender' => 0,
    'receiver' => $_SESSION['user']->id,
    'view' => 0,
    'cid' => $community->id,
    'timed'=>0,
    'start'=>time(),
    'end'=>time(),
    'type' => 'add-extended-data',
    'content' => 'Successfully added '.$extend->name.' for ' . $data->title
));
$notification->insertDB();
$_SESSION['user']->last_check = time();

$previousPage = $_SERVER['HTTP_REFERER'];
header('location:' . $previousPage);

?>
