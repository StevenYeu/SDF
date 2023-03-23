<?php

require_once "../../classes/classes.php";
\helper\scicrunch_session_start();
require_once $GLOBALS["DOCUMENT_ROOT"] . "/api-classes/rrid-report.php";

if(!isset($_SESSION["user"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

$vars = Array("types-data" => Array(), "subtypes-data" => Array());
foreach($_POST as $key => $val) {
    if($key == "item-id") {
        $vars["item-id"] = \helper\aR($val, "i");
        continue;
    }
    $split = explode("|", $key);
    if(count($split) != 3) {
        continue;
    }
    if($split[0] == "type") {
        $vars["types-data"][] = Array("name" => $split[2], "val" => \helper\aR($val, "s"));
    } elseif($split[0] == "subtype") {
        $vars["subtypes-data"][] = Array("subtype-id" => $split[1], "name" => $split[2], "val" => \helper\aR($val, "s"));
    }
}

if(!isset($vars["item-id"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

updateReportItemUserData($_SESSION["user"], NULL, $vars["item-id"], $vars["types-data"]);
updateReportItemSubtypeUserData($_SESSION["user"], NULL, $vars["item-id"], $vars["subtypes-data"]);

header("location: " . $_SERVER["HTTP_REFERER"]);
exit;

?>
