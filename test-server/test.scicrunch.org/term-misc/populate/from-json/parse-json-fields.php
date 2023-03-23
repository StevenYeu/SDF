<?php

$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$NAMESPACE = "http://neurolex.org/wiki/";
$BAD = array();

//////////////////////////////////////////
// get a list of terms
//////////////////////////////////////////
$iri2tid = array();
$sql = "select tid, iri from term_existing_ids";
if ($result = $mysqli->query($sql)) {
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
       $iri2tid[$row['iri']] = $row['tid'];
   }
}
//print_r($iri2tid);


//////////////////////////////////////////
// add synonyms for term list from json files
//////////////////////////////////////////
$dir = "./data/files/";
$terms = array();
foreach($iri2tid as $iri=>$tid){
   $parts = explode("/", $iri);
   $filename = $parts[count($parts)-1];

   $file_path = $dir . $filename . ".json";
   if (!file_exists($file_path)) {
      print 'File "' . $label . '"  id:"' . $filename . '" NOT FETCHED!' . "\n";
      continue;
   }

   $json = file_get_contents($file_path);
   $data = json_decode($json, true);
   //print_r($data);
   foreach ($data as $k=>$v){
      foreach($v as $field=>$value){
        $unique[trim($field)] = 1;
      }
   }
}
$keys = array_keys($unique);
sort($keys);

foreach( $keys as $k){
   if (preg_match("/http/",$k)){continue;}
   //echo $k . "\n";

   $insert = "insert ignore into _term_properties (property) values ('" . $k . "')";
   //print $insert . "\n";
   $mysqli->query($insert);
}

?>
