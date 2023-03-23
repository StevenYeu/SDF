<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$rows = array();
$sql = 'select distinct annotation_tid from term_annotations';
//print $sql;
if ($result = $mysqli->query($sql)) {
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
       //print_r($row);
       $rows[] = $row;
   }
}
//print_r($rows);

foreach ($rows as $row){

  $insert = "insert ignore into term_annotation_types (annotation_tid, type) value (" . $row['annotation_tid'] . ", 'text')";
//echo $insert . "\n";

  $result = $mysqli->query($insert);
  if (!$result) {
     printf("%s\n", $mysqli->error);
//     exit();
  }
}

$mysqli->close();
?>
