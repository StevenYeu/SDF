<?php

include '../../classes/classes.php';
\helper\scicrunch_session_start();

$cid = filter_var($_GET['cid'], FILTER_SANITIZE_NUMBER_INT);
$comp = filter_var($_GET['component'], FILTER_SANITIZE_NUMBER_INT);

if (!isset($_SESSION['user']) || ($_SESSION['user']->levels[$cid]<2 && $_SESSION['user']->role<1)) {
    header('location:/');
    exit();
}

$community = new Community();
$community->getByID($cid);

foreach ($_POST as $key => $value) {
    $splits = explode('-', $key);
    $vars[$splits[1]] = filter_var($value,FILTER_SANITIZE_STRING);
}
foreach ($_FILES as $key => $array) {
    $splits = explode('-', $key);
    if ($_FILES[$key] && $_FILES[$key]['error'] != 4) {
        $allowedExts = array("jpg", "jpeg", "gif", "png");
        $extension = end(explode(".", $_FILES[$key]["name"]));
        if (($_FILES[$key]["size"] < 5000000)&& in_array(strtolower($extension), $allowedExts)) {
            if ($_FILES[$key]["error"] > 0) {
                //header('location:http://scicrunch.com/finish?status=fileerror&type=community&title=' . $name . '&community=' . $portalName);
                exit();
            } else {
                $name = $community->portalName.'_data_'.rand(0,1000000).'.png';
                file_put_contents('../../upload/community-components/'.$name, file_get_contents($_FILES[$key]["tmp_name"]));
                @unlink($_FILES[$key]["tmp_name"]);
                $vars['image'] = $name;
            }
        } else {
            //header('location:http://scicrunch.com/finish?status=filetype&type=community&title=' . $name . '&community=' . $portalName);
            exit();
        }
    }
}

//print_r($vars);

if (isset($vars['start']) && $vars['start'] != '') {
    $vars['start'] = strtotime($vars['start']);
}
if (isset($vars['end']) && $vars['end'] != '') {
    $vars['end'] = strtotime($vars['end']);
}

$data = new Component_Data();
$vars['uid'] = $_SESSION['user']->id;
$vars['cid'] = $cid;
$vars['component'] = $comp;
if(isset($vars['position'])){
    $data->shiftAll($vars['position'],$cid,1,$comp);
    $vars['position']+=1;
} else {
    $vars['position'] = 0;
}
if(!$community->isTrusted() && $_SESSION["user"]->role < 1) $vars = \helper\sanitizeHTMLString($vars);
$data->create($vars);
$data->insertDB();

$notification = new Notification();
$notification->create(array(
    'sender' => 0,
    'receiver' => $_SESSION['user']->id,
    'view' => 0,
    'cid' => $community->id,
    'timed'=>0,
    'start'=>time(),
    'end'=>time(),
    'type' => 'add-body-data',
    'content' => 'Successfully added '.$data->getTitle().' for ' . $community->shortName
));
$notification->insertDB();
$_SESSION['user']->last_check = time();

$previousPage = $_SERVER['HTTP_REFERER'];
header('location:' . $previousPage);

?>
