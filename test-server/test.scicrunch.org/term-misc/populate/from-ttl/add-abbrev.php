<?php
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$iri2tid = array();
$sql = "select tid, iri from term_existing_ids";
if ($result = $mysqli->query($sql)) {
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
       $iri2tid[$row['iri']] = $row['tid'];
   }
}
//print_r($iri2tid);

$file = "./neurolex_basic_pages.json";
$json = file_get_contents($file);
$data = json_decode($json, true);

$namespace = "http://neurolex.org/wiki/";
$BAD = array();

$terms = array();
foreach ($data['pages'] as $p){
   //print "\n\n";
   //print_r($p);
   foreach ($p as $k=>$v){
      if (!array_key_exists("Id",$v)){
         print "Doesn't have ID\n"; 
         continue;
      } 
      $eid = $namespace . trim($v['Id']);
      $tid = isset($iri2tid[$eid]) ? $iri2tid[$eid] : 0;
      if ($tid == 0) { continue; }
      if (in_array($eid, $BAD)) {
         print 'BAD ' . $eid . ": " . $tid . "\n";
         continue;
      }
      if (!array_key_exists("Abbrev",$v)){
         continue;
      }

      $terms[$tid] = array();
      if (array_key_exists("Abbrev",$v)){
         //print "Abbrev: " . $v['Abbrev'] . "\n"; 
         if (gettype($v['Abbrev']) == 'array'){
            foreach ($v['Abbrev'] as $a){
               $terms[$tid][] = array('literal'=>trim($a), 'type'=>'abbrev');
            }
            //print_r( $v['Abbrev'] );
         }
         if (gettype($v['Abbrev']) == 'string'){
            $terms[$tid][] = array('literal'=>trim($v['Abbrev']), 'type'=>'abbrev');
            //print $v['Abbrev']. "\n";
         }
      } 
   }
}

//print_r($terms);
foreach ($terms as $k=>$v){
   if (count($v) > 0){
      foreach ($v as $s){
         if (strlen($s['literal']) < 2) { continue; }

         $sql = "select count(*) as count from term_synonyms where tid = " . $k . " and literal = '" . $s['literal'] . "'";
         //print "$sql\n";
         if ($result = $mysqli->query($sql)) {
            $row = $result->fetch_array(MYSQLI_ASSOC);
            if ($row['count'] > 0){
               print "tid: " . $k . " and literal: " . $s['literal'] . " already exist in database\n";
               continue;
            }
         }
         $insert = "insert into term_synonyms (tid, literal, type, version, time) values (" . $k . ", '" . $s['literal'] . "', 'abbrev', 1, " . time() . ")";
         $mysqli->query($insert);
         print $insert. "\n";
      }
   }
}

$mysqli->close();


?>
