<?php

include '../../classes/classes.php';
require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/update_subscription.php";
\helper\scicrunch_session_start();

if(!isset($_SESSION["user"])){
    header("location:/");
    exit;
}
$user = $_SESSION["user"];

$type = \helper\aR($_GET["type"], "s");
$id = \helper\aR($_GET["id"], "s");
$action = \helper\aR($_GET["action"], "s");

if(is_null($type) || is_null($id) || is_null($action) || !in_array($type, Subscription::getAllowedTypes())){
    previousPage();
}

updateSubscription($user, NULL, $action, $type, $id);

previousPage();

function previousPage(){
    header("location:".$_SERVER["HTTP_REFERER"]);
    exit;
}

?>
