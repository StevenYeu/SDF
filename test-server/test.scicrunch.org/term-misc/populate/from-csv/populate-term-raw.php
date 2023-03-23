<?php 
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
include 'vendor/autoload.php';
use League\Csv\Reader;

$user = new User();
//$user->getByID(32309);
$user->getByID(31878); //stage
$cid = '30';
$api_key = NULL;

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

/*
//read mapping curie file and set up a hash for replacement
$curie_map = array();
$file = "./neurolex_mapping.csv";
$reader = Reader::createFromPath($file);
foreach ($reader as $index => $row) {
    //print_r($row);
    if (sizeof($row) < 2){
      //print "Bad one: $index\n";
      continue;
    }

    if (empty($row[1])) { continue; }

    //echo $row[1] . "\n";
    $curie_map[$row[0]] = $row[1];
}
print_r($curie_map);
exit;
*/

//get $titles
$titles = array();
$query = "SHOW COLUMNS FROM term_raw";
$result =  $mysqli->query($query);
while ($row = $result->fetch_assoc()) {
   $titles[] = $row['Field'];
}
//print_r($titles);

$file = "./neurolex_term.csv";
$reader = Reader::createFromPath($file);
$reader->setOffset(1);
foreach ($reader as $index => $row) {
   if ($index == 0){
      /*
      //check to see have the columns matching in mysql and csv files
      for ($i=0;$i < sizeof($row);$i++){
         $object = new stdClass();
         $object->title = $row[$i];
         $object->index = $i;
         $object->title_mod = $titles[$i];
         print_r($object);
      } 
      */
      //skiped with setOffset(1)
      //continue;
   }
   
   //skip entries with different column number
   if (sizeof($row) != sizeof($titles)) {
       echo sizeof($row) . " != " . sizeof($titles) . " ";
       echo "Bad: line " . $index . "\n";
       continue;
   }

    $values = "";
    for ($i=0;$i < sizeof($row);$i++){
       $val = trim($row[$i]);
       $values .= !empty($val) ? "'" . $mysqli->escape_string($val) . "', " : "NULL, ";
    }
    $values = preg_replace("/, $/", "", $values);

    $insert = 'INSERT INTO term_raw (' . implode(', ', $titles) . ') VALUES (' . $values . ')';
    //echo $insert . "\n";

    $mysqli->query($insert) or die("Error: database error - " . $mysqli->error);
}
$mysqli->close();

?>
