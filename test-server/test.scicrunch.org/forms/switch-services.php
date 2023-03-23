<?php

require_once __DIR__ . "/../classes/classes.php";
\helper\scicrunch_session_start();

$switch_to = $_GET["to"];
if($switch_to === "production") {
    unset($_SESSION["betaenvironment"]);
} elseif ($switch_to === "stage") {
    $_SESSION["betaenvironment"] = true;
}

header("location: " . $_SERVER["HTTP_REFERER"]);

?>
