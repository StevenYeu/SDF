<?php
$docroot = "../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
include_once $docroot . '/api-classes/term/term_elasticsearch.php';

$ilx = 'ilx_0115052';
$user = new User();
$user->getByID(32309);
$api_key = null;

$response = termElasticDelete($user, $api_key, $ilx);

print_r($response);

?>
