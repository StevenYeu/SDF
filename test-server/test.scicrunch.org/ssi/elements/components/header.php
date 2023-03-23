<?php

$community = $data["community"];
$component = $data["component"];
$vars = $data["vars"];
$tab = $data["tab"];
$hl_sub = $data["hl_sub"];
$ol_sub = $data["ol_sub"];
$docroot = __DIR__ . "/../../..";


if(!isset($vars['stripped']) || $vars['stripped'] != 'true') {
    if($community->id == 0) {
        $components = $community->components;
        include $docroot . '/ssi/header.php';
    } elseif($component) {
        if ($component->component == 0) {
            include $docroot . '/components/header/header-normal.php';
        } elseif ($component->component == 1) {
            include $docroot . '/components/header/header-boxed.php';
        } elseif ($component->component == 2) {
            include $docroot . '/components/header/header-float.php';
        } elseif ($component->component == 3) {
            include $docroot . '/components/header/header-flat.php';
        } elseif ($component->component == 4) {
            include $docroot . '/components/header/header-float-no-logo.php';
        }
    } else {
        include $docroot . '/components/header/header-normal.php';
    }
}
echo \helper\htmlElement("login-form", Array("errorID" => $errorID, "community" => $community));

?>
