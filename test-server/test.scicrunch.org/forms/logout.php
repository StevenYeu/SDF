<?php
include ('../classes/classes.php');
\helper\scicrunch_session_start();

$previousPage = $_SERVER['HTTP_REFERER'];

$_SESSION['user']->log('logout');

session_unset();
session_destroy();

// in the case that the user just verified their account, logout should send them to home
if (stripos($previousPage, "verification")) 
    $previousPage = "/";

// if ODC site, send back to front page rather than previous page
$parse_url = parse_url($previousPage);


if (preg_match("/https:\/\/(.*)?\/(.*)?\//", $previousPage, $matches)) {
    header('location: https://' . $matches[1]);
    exit;
} elseif (substr($parse_url['path'], 0, 4) == '/odc')
    header('location: https://' . $parse_url['host'] . $parse_url['path'] . 'Software-Discovery-Portal');

header('location:'.$previousPage);
?>
