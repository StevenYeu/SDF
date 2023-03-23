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
   foreach ($data as $label=>$fields){
      if (!array_key_exists("Id",$fields)){
         print "'" . $label . "' doesn't have ID\n";
         continue;
      }

      $eid = $NAMESPACE . trim($fields['Id']);
      if (in_array($eid, $BAD)) {
         print 'BAD ' . $eid . ": " . $tid . "\n";
         continue;
      }

      $terms[$tid] = array();
      //abbreviations
      if (array_key_exists("Abbrev",$fields)){
         //print $eid . " Abbrev: " . $fields['Abbrev'] . "\n"; 
         if (gettype($fields['Abbrev']) == 'array'){
            foreach ($fields['Abbrev'] as $a){
               $terms[$tid][] = array('literal'=>trim($a), 'type'=>'abbrev');
            }
            //print_r( $fields['Abbrev'] );
         }
         if (gettype($fields['Abbrev']) == 'string'){
            $terms[$tid][] = array('literal'=>trim($fields['Abbrev']), 'type'=>'abbrev');
            //print $fields['Abbrev']. "\n";
         }
      }

      //abbreviations
      if (array_key_exists("Synonym",$fields)){
         //print $eid . " Synonym: " . $fields['Synonym'] . "\n"; 
         if (gettype($fields['Synonym']) == 'array'){
            foreach ($fields['Synonym'] as $a){
               $a = preg_replace("/^\s*,\s*/","", trim($a));
               if (preg_match("/;/",$a)){
                  $ss = explode(";", $a);
                  foreach ($ss as $s){
                     $terms[$tid][] = array('literal'=>trim($s));
                  }
               } else {
                  $terms[$tid][] = array('literal'=>trim($a));
               }
            }
            //print_r( $fields['Synonym'] );
         }
         if (gettype($fields['Synonym']) == 'string'){
            if (preg_match("/;/",$fields['Synonym'])){
               $ss = explode(";", $fields['Synonym']);
               foreach ($ss as $s){
                  $terms[$tid][] = array('literal'=>trim($s));
               }
            } else {
               $terms[$tid][] = array('literal'=>trim($fields['Synonym']));
            }
            //print $fields['Synonym']. "\n";
         }
      }

   }
}
//print_r($terms);

foreach ($terms as $tid=>$synonyms) {
   if (count($synonyms) < 1){
      continue;
   }
   //print $tid . ":\n";
   //print_r($synonyms);
   foreach ($synonyms as $s) {
      //print_r($s);
      $sql = "select count(*) as count from term_synonyms where tid = " . $tid . " and literal = '" . $mysqli->escape_string($s['literal']) . "'";
      if ($result = $mysqli->query($sql)) {
         $row = $result->fetch_array(MYSQLI_ASSOC);
         if ($row['count'] > 0){
            print "tid: " . $tid . " and literal: " . $s['literal'] . " already exist in database\n";
            continue;
         }
      }
      $insert = "insert into term_synonyms (tid, literal, type, version, time) values (" . $tid . ", '" .
                $mysqli->escape_string($s['literal']) . "', '" . $s['type'] . "', 1, " . time() . ")";
//      print $insert . "\n";
      $mysqli->query($insert);
   }
}

$mysqli->close();
?>
