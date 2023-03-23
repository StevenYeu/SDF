<?php
require '../vendor/autoload.php';

use Elasticsearch;
use Elasticsearch\ClientBuilder;

$elastichosts = array('http://biocaddie.scicrunch.io:80/');

//$client = Elasticsearch\ClientBuilder::create()->build();
$client = Elasticsearch\ClientBuilder::create()->setHosts($elastichosts)->build();

$mysqli = new mysqli('xx', 'xx', 'xx', 'xx') or die("Error: cannot connect to the database - ". $mysqli->connect_error);
//mysqli($db_host, $db_user, $db_password, $db_name)

//$sql = "select id, ilx, label, ontology_urls, definition, comment from terms";
$sql = "select * from terms";
$count = 0;
if ($result = $mysqli->query($sql)) {
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
      $count++;
 
//stripslashes(
      $param = array();
      $param['id'] = $row['ilx'];
      //if ( $param['id'] == 'ilx_0100757' or $param['id'] == 'ilx_0100758' ){
         //continue;
      //}
      foreach ($row as $k => $v){
         $row[$k] = utf8_encode(stripslashes($v));
      }

      $data = array();
      $data = $row;

      //$sql2 = "select literal, type from term_synonyms where tid = " . $row['id'];
      $sql2 = "select * from term_synonyms where tid = " . $row['id'];
      if ($result2 = $mysqli->query($sql2)) {
         while ($row2 = $result2->fetch_array(MYSQLI_ASSOC)) {
            //print_r($row);
            foreach ($row2 as $k => $v){
               $row2[$k] = utf8_encode(stripslashes($v));
            }
            $data['synonyms'][] = $row2;
         }
      }

      //$sql3 = "select curie, iri, curie_catalog_id from term_existing_ids where tid = " . $row['id'];
      $sql3 = "select * from term_existing_ids where tid = " . $row['id'];
      if ($result3 = $mysqli->query($sql3)) {
         while ($row3 = $result3->fetch_array(MYSQLI_ASSOC)) {
            //print_r($row3);
            foreach ($row3 as $k => $v){
               $row3[$k] = utf8_encode(stripslashes($v));
            }
            $data['existing_ids'][] = $row3;
         }
      }

      $sids = array();
      $s = "select distinct superclass_tid from term_superclasses where tid = " . $row['id'];
      if ($r = $mysqli->query($s)) {
         while ($rr = $r->fetch_array(MYSQLI_ASSOC)) {
            $sids[] = $rr['superclass_tid'];
         }
      }
      $str = implode(",", $sids);
      //$sql4 = "select id, ilx, label, ontology_urls, definition from terms where id in (" . $str . ")";
      $sql4 = "select * from terms where id in (" . $str . ")";
      if ($result4 = $mysqli->query($sql4)) {
         while ($row4 = $result4->fetch_array(MYSQLI_ASSOC)) {
            //print_r($row4);
            foreach ($row4 as $k => $v){
               $row4[$k] = utf8_encode(stripslashes($v));
            }
            $data['superclasses'][] = $row4;
         }
      }

      $param['body'] = $data;
      $param['index'] = 'scicrunch';
      $param['type'] = 'term';
//      $return = $client->index($param);
  //print_r($param);
try {
  $response = $client->index($param);
  //print "RESPONSE: " . $response . "\n";
  //print_r($response);
} catch (Exception $e) {
  print "ERROR: " . $param['id'] . "\n";
  //print_r($e);
}
//      print_r($return);
//if ( $count > 500 ) {break;}
   }
}

/*
$param['index'] = 'scicrunch';
$param['type'] = 'term';
$param['body']['ilx'] = 'ilx_0100331';
$return = $client->get($param);
print_r($return);
*/


?>
