<?php

include '../classes/classes.php';

$name = filter_var($_GET['name'],FILTER_SANITIZE_STRING);

if(!Community::uniquePortalName($name) || !Community::validPortalName($name)){
    echo '0';
} else {
    echo '1';
}

?>
