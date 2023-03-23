<?php

require_once __DIR__ . "/../../classes/classes.php";
\helper\scicrunch_session_start();
if(!isset($_SESSION["user"]) || !isset($_GET["uid"]) || !isset($_GET["ban"])) {
    header("location:/");
}

$uid = filter_var($_GET["uid"], FILTER_SANITIZE_NUMBER_INT);
$ban = filter_var($_GET["ban"], FILTER_SANITIZE_NUMBER_INT);
if($ban !== 1 && $ban !== 0) header("location:/");

$user = new User();
$user->getByID($uid);
if(!$user->id) header("location:/");
if($user->role >= $_SESSION["user"]->role) header("location:/");
$user->updateField("banned", $ban);

header("location:" . $_SERVER["HTTP_REFERER"]);

?>
