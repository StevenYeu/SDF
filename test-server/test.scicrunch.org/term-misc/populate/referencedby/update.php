<?php 
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$sql = "select distinct existing_id from term_mappings";
//print $sql . "\n";
$stmt = $mysqli->query($sql);
$numRows = $stmt->num_rows;
$count = 0;
if ($numRows > 0 ){ 
  while ($row = $stmt->fetch_assoc()){
    $count ++;
    print $count . "\n";
    //print_r($row);
    $sql2 = "select tid from term_existing_ids where curie = '" . $row['existing_id'] . "'";
    $stmt2 = $mysqli->query($sql2);
    $row2 = $stmt2->fetch_assoc();

    $update = "update term_mappings set tid=" . $row2['tid'] . " where existing_id ='" . $row['existing_id'] . "'";
    //print $update . "\n";
    $mysqli->query($update);
    $stmt2->close();
  }
}
$stmt->close();
$mysqli->close();
exit;

