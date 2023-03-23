<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$rows = array();
$sql = 'select * from terms where label like "%\'%" or definition like "%\'%"';
//print $sql;
if ($result = $mysqli->query($sql)) {
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
       //print_r($row);
       $rows[] = $row;
   }
}
//print_r($rows);

foreach ($rows as $row){
  $label = stripslashes($row['label']);  
  $definition = str_replace('\n', " ", $row['definition']);
  $definition = str_replace('\t', " ", $definition);
  $definition = stripslashes($definition);  
  $definition = stripslashes($definition);  
  $definition = stripslashes($definition);  
  $definition = stripslashes($definition);  

  $update = "update terms set label='" . $mysqli->escape_string($label) . "', definition='" . $mysqli->escape_string($definition) . "' where id =". $row['id'];

  $result = $mysqli->query($update);
  if (!$result) {
     printf("%s\n", $mysqli->error);
//     exit();
  }
}

$mysqli->close();
?>
