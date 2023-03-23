<?php

require_once "../../classes/classes.php";
\helper\scicrunch_session_start();

if(!isset($_SESSION["user"])) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

$parsed_url = parse_url($_SERVER["HTTP_REFERER"]);

$user = $_SESSION["user"];
$name = \helper\aR($_POST["name"], "s");
$description = \helper\aR($_POST["description"], "s");

$report = RRIDReport::createNewObj($name, $description, $user, NULL);

if(is_null($report)) {
    $existing = RRIDReport::loadBy(Array("name", "uid"), Array($name, $user->id));
    if(!is_null($existing)) {
        $referer = \helper\setGetQueryParam($_SERVER["HTTP_REFERER"], "error", "1", false);
    } else {
        $referer = \helper\setGetQueryParam($_SERVER["HTTP_REFERER"], "error", "2", false);
    }
    header("location: " . $referer);
    exit;
}

$referer = $_SERVER["HTTP_REFERER"];
$referer_path = explode("?", $referer)[0];
header("location: " . $referer_path . "/" . $report->id);

?>
