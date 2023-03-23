<?php
error_reporting(E_ERROR);
$docroot = "../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
include_once $docroot . '/classes/term.class.php';
require $docroot . '/lib/elastic/vendor/autoload.php';

$USER = new User();
$USER->getByID(247);
$API_KEY = NULL;

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or exit("Error: cannot connect to the database - ". $mysqli->connect_error);

$client = Elasticsearch\ClientBuilder::create()->setHosts($config['elastichosts'])->build();
$param['index'] = 'scicrunch';
$param['type'] = 'term';
$dbObj = new DbObj();

$sql = 'select t.id as id, t.label as label, t.ilx as ilx from terms t, term_existing_ids tei where t.id = tei.tid';
$stmt = $mysqli->query($sql);
$count = 0;
$done = array();
while ($row = $stmt->fetch_assoc()){
  $count ++;
  if (array_key_exists($row['id'], $done)){ continue; }
/*
  if ($count > 10) {
    exit;
  }
*/
print $row['id'] . " " . $row['ilx'] . " " . $row['label'] . "\n";
    $done[$row['id']] = 1;
    $termObj = new Term($dbObj);
    $termObj->getById($row['id']);

    $termObj->getExistingIds();
    $termObj->getSynonyms();
    $termObj->getSuperclasses();
    $termObj->getRelationships();
    $termObj->getAnnotations();
    $termObj->getOntologies();

    $term = DbObj::termForElasticSearch($termObj);

    $param['id'] = $term['ilx'];
    $param['body'] = $term;

    $response = array();
    try {
        $response = $client->index($param);
    } catch (Exception $e) {
        print_r($e);
    }

}
$stmt->close();
$mysqli->close();
?>
