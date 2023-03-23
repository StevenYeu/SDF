<?php

require_once __DIR__ . "/../classes/classes.php";
\helper\scicrunch_session_start();

if(!isset($_SESSION["user"]) || $_SESSION["user"]->role < 1) header("location:/");

$viewid = \helper\aR($_POST["viewid"], "s");
$prefix = \helper\aR($_POST["prefix"], "s");

RRIDPrefixRedirect::createNewObj($_SESSION["user"], $viewid, $prefix);

header("location:" . $_SERVER["HTTP_REFERER"]);

?>
