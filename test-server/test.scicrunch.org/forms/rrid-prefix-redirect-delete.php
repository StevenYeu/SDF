<?php

require_once __DIR__ . "/../classes/classes.php";
\helper\scicrunch_session_start();

if(!isset($_SESSION["user"]) || $_SESSION["user"]->role < 1) header("location:/");

$viewid = \helper\aR($_GET["viewid"], "s");
$prefix = \helper\aR($_GET["prefix"], "s");

$rrid_prefix = RRIDPrefixRedirect::loadBy(Array("viewid", "prefix"), Array($viewid, $prefix));
if(!is_null($rrid_prefix)) {
    RRIDPrefixRedirect::deleteObj($rrid_prefix);
}
header("location:" . $_SERVER["HTTP_REFERER"]);

?>
