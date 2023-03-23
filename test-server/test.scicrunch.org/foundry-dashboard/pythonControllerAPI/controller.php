<?php

function execute($apiKey, $processType, $resourceID, $params = []) {
  $url = "https://python.scicrunch.io/controller/". $processType . "?api_key=" . $apiKey.  "&resourceID=" . $resourceID;
  $ch = curl_init($url);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HEADER, false);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, []);
  $response = curl_exec($ch);
  curl_close($ch);
  return array(json_decode($response));
}

?>
