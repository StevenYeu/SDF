<?php
require './vendor/autoload.php';

$client = Elasticsearch\ClientBuilder::create()->build();

$params = ['index' => 'scicrunch'];
$response = $client->indices()->delete($params);


?>
