<?php
$docroot = "../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
include_once $docroot . '/api-classes/term/term_by_ilx.php';

$user = new User();
$user->getByID(32309);
$api_key = null;
$ilx = 'ilx_0100161';
$term = getTermByIlx($user, $api_key, $ilx);
$data_json = json_encode($term);


$url = 'localhost:9200/scicrunch/term/ilx_0100161';


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
curl_setopt($ch, CURLOPT_POST, 1);
curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response  = curl_exec($ch);
curl_close($ch);

var_dump($response);


?>
