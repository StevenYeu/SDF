<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$rows = array();
$sql = 'select id, ilx from terms where ilx like "tmp%"';
//print $sql;
if ($result = $mysqli->query($sql)) {
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
       //print_r($row);
       $rows[] = $row;
   }
}
//print_r($rows);

foreach ($rows as $row){
  $ilx = str_replace('tmp', "ilx", $row['ilx']);

  $update = "update terms set ilx='" . $ilx . "' where id =". $row['id'];

  //$result = $mysqli->query($update);
  if (!$result) {
     printf("%s\n", $mysqli->error);
//     exit();
  }

  $update2 = "update ilx_identifiers set fragment='" . $ilx . "' where fragment ='". $row['ilx'] . "'";

  //$result = $mysqli->query($update2);
  if (!$result) {
     printf("%s\n", $mysqli->error);
//     exit();
  }
}

$mysqli->close();
?>
