<?php 
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$sql = "select distinct concept_id from _term_mapping_dump where existing_id is null";
$stmt = $mysqli->query($sql);
$numRows = $stmt->num_rows;
if ($numRows > 0 ){ 
  while ($row = $stmt->fetch_assoc()){
    //print_r($row);
    $arr = explode("_",$row['concept_id']);
    if ( count($arr) != 2 ){
      $arr = explode(":",$row['concept_id']);
    }
    if ( count($arr) == 2 ) {
      //print_r($arr);
      $sql2 = "select existing_id from term_existing_ids where curie = 'NLXWIKI:" . $arr[0] . ":" . $arr[1] . 
              "' or curie = 'NLXWIKI:" . $arr[0] . "_" . $arr[1] . "'";
      $stmt2 = $mysqli->query($sql2);
      $numRows2 = $stmt2->num_rows;
      if ($numRows2 > 0 ){ 
        //print $row['concept_id'] . "\n";
        while ($row2 = $stmt2->fetch_assoc()){
          //print_r($row2);
          $update = "update _term_mapping_dump set existing_id='" . $row2['existing_id'] . "' where concept_id ='" . $row['concept_id'] . "' ";
          //print $update . "\n";
          $mysqli->query($update);
        }
      }
      $stmt2->close();
    }

  }
}
$stmt->close();
$mysqli->close();
exit;

