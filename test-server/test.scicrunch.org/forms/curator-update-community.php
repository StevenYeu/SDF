<?php

// inits
require_once $_SERVER['DOCUMENT_ROOT'] . '/classes/classes.php';
\helper\scicrunch_session_start();

// must be curator
if(!isset($_SESSION['user']) || $_SESSION['user']->role == 0){
    header("location: /");
    exit;
}

// get post request arguments
$cid = \helper\aR($_POST["cid"], "i");
$private = isset($_POST["public"]) ? 0 : 1;
$redirect = \helper\aR($_POST["redirect"], "s");
$altPortalName = \helper\aR($_POST["altportalname"], "s");
$archive = isset($_POST["archive"]) ? 1 : 0;
$comm_resource_id = isset($_POST["comm-resource"]) ? \helper\aR($_POST["comm-resource"], "i") : NULL;

// get community
$comm = new Community();
$comm->getByID($cid);

// set private
$comm->private = $private;

// set redirect url
$comm->redirect_url = $redirect;

// set archive
if($archive === 1) $comm->archive();
else $comm->deArchive();
if($altPortalName != "") {
    if(Community::uniquePortalName($altPortalName)) {
        $comm->altPortalName = $altPortalName;
    }
} else {
    $comm->altPortalName = "";
}

// set community rid
if($comm_resource_id) {
    $resource = new Resource();
    $resource->getByID($comm_resource_id);
    if(!$resource->id) $comm->rid = NULL;
    else $comm->rid = $resource->id;
} else {
    $comm->rid = NULL;
}

// update database
$comm->updateDB();

// go to previous page
$previous = $_SERVER["HTTP_REFERER"];
header("location:" . $previous);

?>
