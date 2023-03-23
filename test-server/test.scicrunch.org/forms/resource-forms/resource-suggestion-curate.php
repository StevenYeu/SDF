<?php

// load classes, start session
include "../../classes/classes.php";
\helper\scicrunch_session_start();

// must be curator
if(!isset($_SESSION["user"]) || $_SESSION["user"]->role < 1) previousPage();

// get post query
$rsid = \helper\aR($_POST["rsid"], "i");
$comment = \helper\aR($_POST["comment"], "s");
$status = \helper\aR($_POST["submit"], "s");

// backout if missing arguments
if(!$rsid || !$status) previousPage();

// get resource suggestion
$resource_suggestion = ResourceSuggestion::loadBy(Array("id"), Array($rsid));
if(is_null($resource_suggestion)) previousPage();

// update the comment
$resource_suggestion->curator_comment = $comment;
$resource_suggestion->updateDB();

// if status is none, break
if($status === "none") previousPage();

// set new status
$resource_suggestion->status = $status;
$resource_suggestion->updateDB();
if($status !== ResourceSuggestion::STATUS_APPROVED) previousPage();

// if status == approved, get the new resource and redirect to its page
$resource = $resource_suggestion->createResource();
header("location:/browse/resourcesedit/" . $resource->rid);
exit;


/******************************************************************************************************************************************************************************************************/

function previousPage() {
    header("location:" . $_SERVER["HTTP_REFERER"]);
    exit;
}

?>
