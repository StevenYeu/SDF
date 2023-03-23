<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
require_once $docroot . '/lib/elastic/vendor/autoload.php';

use Elasticsearch;
use Elasticsearch\ClientBuilder;

$client = Elasticsearch\ClientBuilder::create()->setHosts($config['elastichosts'])->build();
$param['index'] = 'scicrunch';
$param['type'] = 'resource';

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);


$types = array();
$sql = "select * from resource_type";
$stmt = $mysqli->query($sql); 
while ($row = $stmt->fetch_assoc()) {
  $types[$row['id']] = $row['name'];
}

$relationship_types = array();
$sql = "select * from resource_relationship_strings";
$stmt = $mysqli->query($sql); 
while ($row = $stmt->fetch_assoc()) {
  $relationship_types[$row['id']] = array('forward'=>$row['forward'], 'reverse'=>$row['reverse']);
}

function resourceElasticUpsert($rid){
  global $mysqli;
  global $types;
  global $relationship_types;
  global $client;
  global $param;
  $param['id'] = $rid;
  //print $rid . "\n";
  //print_r($types);
  //print_r($relationship_types);

  $sql = "select * from resources where rid = '" . $rid . "'";
  $stmt = $mysqli->query($sql); 
  $row = $stmt->fetch_assoc();
  //print_r($row);

  $body = array();
  $body['rid'] = $rid;
  $body['original_id'] = $row['original_id'];
  $body['type'] = $types[$row['typeID']];

  $sql2 = "select * from resource_versions where rid = " . $row['id']; 
  $last_version = 0;
  $stmt2 = $mysqli->query($sql2);
  while ($row2 = $stmt2->fetch_assoc()){
    if ($row2['version'] > $last_version && $row2['status'] == 'Curated'){
      $last_version = $row2['version'];
    }
  }

  if ($last_version == 0){
    continue;
  }

  $sql3 = "select * from resource_columns where rid = " . $row['id'] . " and version = " . $last_version;
  $stmt3 = $mysqli->query($sql3);
  while ($row3 = $stmt3->fetch_assoc()){
    if (strlen(trim($row3['value'])) > 0){
      $body[$row3['name']] = $row3['value'];
    }
  }


  $sql4 = "select * from resource_relationships where id1 = '" . $rid . "' or id2 = '" . $rid . "'";
  $stmt4 = $mysqli->query($sql4);
  while ($row4 = $stmt4->fetch_assoc()){
    $body['relationships'][] = array('id1'=>$row4['id1'], 'relationship'=>$relationship_types[$row4['reltype_id']]['forward'], 'id2'=>$row4['id2']);
  }

//print_r($body);
  print json_encode($body, JSON_PRETTY_PRINT);
}


?>
