<?php

require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";
$resource_id = \helper\getIDFromRID($id);
$resource = new Resource();
$resource->getByID($resource_id);

$url_end_array = Array();
if(!is_null($notif)) $url_end_array[] = "notif=" . $notif;
if($notif_email === true) $url_end_array[] = "notif_email";
if(!is_null($_GET["redirectid"])) $url_end_array[] = "redirectid=" . \helper\aR($_GET["redirectid"], "s");
$url_end = "";
if(!empty($url_end_array)) $url_end = "?" . implode("&", $url_end_array);

if($resource->uuid){
    header("location: /scicrunch/Resources/record/nlx_144509-1/" . $resource->rid . "/resolver" . $url_end);
}else{
    \helper\errorPage("noresource");
}
exit;

?>
