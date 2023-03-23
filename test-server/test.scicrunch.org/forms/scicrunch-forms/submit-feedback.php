<?php

include '../../classes/classes.php';
\helper\scicrunch_session_start();

$feedback = filter_var($_POST["feedback"], FILTER_SANITIZE_STRING);

$referer = $_SERVER['HTTP_REFERER'];

if (empty($referer)) {
    http_response_code(403);
    exit();
}

if (!$feedback) {
    // do not submit empty feedback
    header("location: " . $referer . "?feedback=false");
    exit();
}

if(isset($_SESSION['user'])) {
    $user_name = $_SESSION["user"]->firstname . " " . $_SESSION["user"]->lastname . " <" . $_SESSION["user"]->email . ">";
} else {
    $user_name = filter_var($_POST["name"], FILTER_SANITIZE_STRING);
}
$message = 
    "User: $user_name\n" .
    "Time: " . \helper\dateFormat("normal", time()) . "\n" .
    "Referer: $referer" . "\n" .
    "\n" .
    "Message:" ."\n" .
    $feedback . "\n";

$title = "User Feedback from $user_name";
\helper\sendEmail("info@scicrunch.org", NULL, $message, $title);

header("location: " . $referer . "?feedback=true");
?>
