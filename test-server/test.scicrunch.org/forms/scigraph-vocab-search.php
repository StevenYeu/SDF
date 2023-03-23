<?php

$url_base = "http://scigraph.scicrunch.io:9000/scigraph/";

$url_curies = $url_base . "cypher/curies";
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url_curies);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$asdf = json_decode($response, true);
unset($asdf["SCR"]);
$prefixes = join("&prefix=", array_keys($asdf));

$q = rawurlencode($_GET["q"]);
$limit = isset($_GET["limit"]) ? $_GET["limit"] : NULL;
$type = $_GET["type"] === "term" ? "term" : "search";
$url_vocab = $url_base . "vocabulary";
$url = $url_vocab . "/" . $type . "/" . $q . "?" . $prefixes;
if(!is_null($limit)) $url .= "&limit=" . $limit;

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);
$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
header('X-PHP-Response-Code: ' . $status, true, $status);
echo $response;

?>
