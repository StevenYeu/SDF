<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
include_once $docroot . '/classes/resource-elasticsearch.class.php';

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$elastic_search = new ResourceElasticsearch();

$sql = "select * from resources";
$stmt = $mysqli->query($sql);
$count = 0;
while ($row = $stmt->fetch_assoc()){
  $count++;
  //print_r($row);
  //if ($count > 10){ exit; }

  //resourceElasticUpsert($row['rid']);
  $response = $elastic_search->upsert($row['rid']);
}
$stmt->close();

$mysqli->close();

?>
