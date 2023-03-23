<?php
error_reporting(E_ERROR);
$docroot = "../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
require_once $docroot . '/lib/elastic/vendor/autoload.php';
include_once $docroot . '/classes/term.class.php';

use Elasticsearch;
use Elasticsearch\ClientBuilder;

$client = Elasticsearch\ClientBuilder::create()->setHosts($config['elastichosts'])->build();

//print $config['elasticsearch']['index'] . " " . $config['elasticsearch']['type'] . "\n";

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$rows = array();
$sql = "select * from terms where status = '0' and id > 1938";
if ($result = $mysqli->query($sql)) {
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
       $rows[] = $row;
   }
}
$mysqli->close();
print count($rows)."\n";

$log = fopen("elastic-logs.txt", "wr") or die("Unable to open file!");
foreach ($rows as $row){
   $term = termElasticUpsert($row['id'], $client);
   if (isset($term['error'])) {
       fwrite($log, "=======================\n");
       fwrite($log, $term);
       fwrite($log, $term['error']);
   } else {
       print_r($term);
   }
}
fclose($log);


function termElasticUpsert($tid, $client) {
print $tid . "\n";
    global $config;
    $dbObj = new DbObj();

    $termObj = new Term($dbObj);
    $termObj->getById($tid);

    $termObj->getExistingIds();
    $termObj->getSynonyms();
    $termObj->getSuperclasses();
    $termObj->getAncestors();
    $termObj->getRelationships();
    $termObj->getAnnotations();
    $termObj->getOntologies();

    $term = DbObj::termForElasticSearch($termObj);
//     print $term['ilx'] . "\n";
//     print_r($term);

    $param['index'] = $config['elasticsearch']['index'];
    $param['type'] = $config['elasticsearch']['type'];
    $param['id'] = $term['ilx'];
    $param['body'] = $term;
//print_r($param);

    $response = array();
    try {
        $response = $client->index($param);
    } catch (Exception $e) {
        $response['error'] = $e;
        //return $e;
    }

    return $response;

}

?>
