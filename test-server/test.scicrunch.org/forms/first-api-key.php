<?php

require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/classes.php";
\helper\scicrunch_session_start();
if(!isset($_SESSION["user"])) goHome();

$user = $_SESSION["user"];
$user_key_count = APIKey::userKeyCount($user);
if($user_key_count >= 10) goHome();

$api_key = APIKey::createNewObj($user->id, 0, 0, 1);
$api_key->addPermission("user");
$_SESSION["loginconfirm"] = true;
$previous_page = $_SERVER["HTTP_REFERER"];
header("location:".$previous_page);

function goHome(){
    header("location:/");
    exit;
}

?>
