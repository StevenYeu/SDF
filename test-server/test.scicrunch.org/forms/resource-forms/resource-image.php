<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/classes.php";
\helper\scicrunch_session_start();

if(!isset($_SESSION['user'])) throw new Exception("must be logged in to change a resource image");
$user = $_SESSION['user'];

$scr_id = filter_var($_GET["rid"], FILTER_SANITIZE_STRING);
$rid = \helper\getIDFromRID($scr_id);
$resource = new Resource();
$resource->getByID($rid);
if(!$resource->id) throw new Exception("bad resource id");
if(!$resource->isAuthorizedOwner($user->id)){
    header("location: /browse/resourcesedit/" . $scr_id);
    exit;
}

if(isset($_FILES["resource-image"])){
    $resource->setImage($_FILES['resource-image'], $_SERVER, $user);
}

header("location: /browse/resourcesedit/" . $scr_id);

?>
