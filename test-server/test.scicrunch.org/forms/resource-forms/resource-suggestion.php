<?php

// set up
include "../../classes/classes.php";
\helper\scicrunch_session_start();

// get email
if(isset($_SESSION["user"])) {  // user logged in
    $email = $_SESSION["user"]->email;
} else {    // user not logged in
    if(!isset($_POST["email"]) || !isset($_POST["g-recaptcha-response"])) header("location:" . $_SERVER["HTTP_REFERER"]);
    $email = \helper\aR($_POST["email"], "s");
    $captcha = $_POST["g-recaptcha-response"];
    $file = file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=" . CAPTCHA_SECRET_KEY . "&response=" . $captcha . "&remoteip=" . $_SERVER["REMOTE_ADDR"]);
    $json = json_decode($file);
    if($json->success = false) header("location:" . $_SERVER["HTTP_REFERER"]);
}

// get other fields
// resource name required
if(!isset($_POST["Resource_Suggestion_Name"])) header("location:" . $_SERVER["HTTP_REFERER"]);
$resource_name = \helper\aR($_POST["Resource_Suggestion_Name"], "s");
$resource_url = isset($_POST["Resource_URL"]) ? \helper\aR($_POST["Resource_URL"], "s") : NULL;
$description = isset($_POST["Description"]) ? \helper\aR($_POST["Description"], "s") : NULL;
$citation = isset($_POST["Defining_Citation"]) ? \helper\aR($_POST["Defining_Citation"], "s") : NULL;

// get the resource type
$typeid = isset($_POST["typeid"]) ? $_POST["typeid"] : 1;

// get the community
$cid = isset($_POST["cid"]) ? \helper\aR($_POST["cid"], "i") : 0;
$community = new Community();
$community->getByID($cid);
if(!$community->id) $submit_cid = 0;
elseif(!$community->isVisible(isset($_SESSION["user"]) ? $_SESSION["user"] : NULL)) $submit_cid = 0;
else $submit_cid = $community->id;

// protect against xss
if(!isset($_SESSION["user"]) || $_SESSION["user"]->role < 1) {
    $submit_cid = \helper\sanitizeHTMLString($submit_cid);
    $email = \helper\sanitizeHTMLString($email);
    $typeid = \helper\sanitizeHTMLString($typeid);
    $resource_name = \helper\sanitizeHTMLString($resource_name);
    $resource_url = \helper\sanitizeHTMLString($resource_url);
    $description = \helper\sanitizeHTMLString($description);
    $citation = \helper\sanitizeHTMLString($citation);
}

// create the suggestion
ResourceSuggestion::createNewObj($submit_cid, $email, $typeid, $resource_name, $resource_url, $description, $citation);

// redirect to finish page
if($submit_cid === 0) header("location:/create/resourcesuggestion-finish");
else header("location:/" . $community->portalName . "/about/resource?resource_suggestion=finish");

?>
