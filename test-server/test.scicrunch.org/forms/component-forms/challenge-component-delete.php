<?php

include '../../classes/classes.php';
\helper\scicrunch_session_start();

$cid = filter_var($_GET['cid'], FILTER_SANITIZE_NUMBER_INT);
$id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

if(!isset($_SESSION['user']) || ($_SESSION['user']->levels[$cid]<2 && $_SESSION['user']->role<1)){
    header('location:/');
    exit();
}

$community = new Community();
if($cid==0)
    $community->shortName = 'SciCrunch';
else
    $community->getByID($cid);

$component = new Component();
$found = false;
$components = $component->getByCommunity($cid);
foreach ($components['page'] as $compo) {
    if ($compo->id == $id) {
        $component = $compo;
        $found = true;
    }
}
if ($found) {
    $component->shiftAllPages($component->position, $cid, -1);
    $component->removeDB();

    $notification = new Notification();
    $notification->create(array(
        'sender' => 0,
        'receiver' => $_SESSION['user']->id,
        'view' => 0,
        'cid' => $community->id,
        'timed'=>0,
        'start'=>time(),
        'end'=>time(),
        'type' => 'delete-container-component',
        'content' => 'Successfully removed ' . $component->text1 . ' from ' . $community->shortName
    ));
    $notification->insertDB();
    $_SESSION['user']->last_check = time();
}
$component->connect();
$component->delete('component_data','i',array($component->component),'where component=?');
$component->close();

$previousPage = $_SERVER['HTTP_REFERER'];
$splits = explode('/about/',$previousPage);
if(count($splits)>1){
    header('location:/'.$community->portalName);
} else {

header('location:' . $previousPage);
}

?>
