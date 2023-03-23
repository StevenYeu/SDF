<?php
$docroot = "../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';

$host = $config['elasticsearch']['host'] . "/" . $config['elasticsearch']['index'] . "/" . $config['elasticsearch']['type'];
$ilx = 'ilx_0115054';
$url = $host . "/" . $ilx;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
//curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response  = curl_exec($ch);
curl_close($ch);

print_r($response);

?>
