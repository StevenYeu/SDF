<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
include_once "./arc2/ARC2.php";

$BAD = array('http://neurolex.org/wiki/D009116');

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$iri2tid = array();
$sql = "select tid, iri from term_existing_ids";
if ($result = $mysqli->query($sql)) {
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
       $iri2tid[$row['iri']] = $row['tid'];
   }   
}
//print_r($iri2tid);

$FILE = './neurolex_basic.ttl';

$parser = ARC2::getRDFParser();
//$parser->nsp('NLXWIKI','http://neurolex.org/wiki/');
//$parser->nsp('ILX','http://uri.interlex.org/base/ilx_');
//$parser->nsp('ilx','http://uri.interlex.org/base/'); ns3
//$parser->nsp('owl','http://www.w3.org/2002/07/owl#'); ns2
//$parser->nsp('skos','http://www.w3.org/2004/02/skos/core#'); ns0
//$parser->nsp('OBOANN','http://ontology.neuinfo.org/NIF/Backend/OBO_annotation_properties.owl#'); ns5
//$parser->nsp('oboInOwl','http://www.geneontology.org/formats/oboInOwl#'); ns4


$parser->parse($FILE);
$triples = $parser->getTriples();
$rdfxml = $parser->toRDFXML($triples);

$rdfxml = str_replace('rdf:Description', 'Description', $rdfxml);
$rdfxml = str_replace('rdf:about', 'rdf_about', $rdfxml);
$rdfxml = str_replace('ns1:label', 'ns1_label', $rdfxml);
$rdfxml = str_replace('ns5:synonym', 'ns5_synonym', $rdfxml);

$synonyms = array();
$count = 0;
$rdf = new SimpleXMLElement($rdfxml);
foreach ( $rdf->xpath('//Description') as $item ) {
   $count++;
//   print "\n" . $count . "\n";
   //print $item->asXML('php://output');
   //print_r($item);

   if ($count == 1){
      continue;
   }
   if ($item->ns1_label->__toString() == "") {
      continue;
   }
   if (count($item->ns5_synonym) < 1) {
      continue;
   }

   $eid = $item->attributes()->rdf_about->__toString();
   $tid = $iri2tid[$eid]; 
   if (in_array($eid, $BAD)) { 
      print 'BAD ' . $eid . ": " . $tid . "\n";
      continue; 
   }

   foreach ($item->ns5_synonym as $syn){
       $s = $syn->__toString();
       if (preg_match("/\"/", $s)){ continue; }

       if (preg_match("/,/", $s)){
          $syns = explode(",", $s);
          foreach ($syns as $syn){
             $synonyms[$tid][] = array('literal'=>trim($syn));
          }
       }
       elseif (preg_match("/;/", $s)) {
          $syns = explode(";", $s);
          foreach ($syns as $syn){
             $synonyms[$tid][] = array('literal'=>trim($syn));
          }
       }
       else {
          $synonyms[$tid][] = array('literal'=>trim($syn));
       }

   }

   //print $tid . ": " . $eid . "\n";
   //print_r($synonyms);

   //print "Label: " . $label . "\n";
   //print "Definition: " . $definition . "\n";
   //print "Neurolex Category: " . $anno_value . "\n";
   //print "Existing ID: " . $eid . "\n";
   //print "Superclass: " . $sup . "\n";
   //foreach ($syns as $syn){
       //print "Synonym: " . $syn . "\n";
   //}
   //print "Existing ID: " . str_replace('http://neurolex.org/wiki/', '', $item->attributes()->rdf_about) . "\n";
   //print "Superclass: " . str_replace('http://neurolex.org/wiki/', '', $item->ns1_subClassOf->attributes()[0]) . "\n";
   //print_r($item->ns1_subClassOf->attributes());

}

foreach ($synonyms as $key=>$value) {
   //print $key . ":\n";
   //print_r($value);
   foreach ($value as $k=>$v) {
      $sql = "select count(*) as count from term_synonyms where tid = " . $key . " and literal = '" . $mysqli->escape_string($v['literal']) . "'";
      if ($result = $mysqli->query($sql)) {
         $row = $result->fetch_array(MYSQLI_ASSOC);
         if ($row['count'] > 0){
            print "tid: " . $key . " and literal: " . $v['literal'] . " already exist in database\n";
            continue;
         }
      }
      $insert = "insert into term_synonyms (tid, literal, type, version, time) values (" . $key . ", '" . 
                $mysqli->escape_string($v['literal']) . "', NULL, 1, " . time() . ")";
      print $insert . "\n";
      $mysqli->query($insert);
   }

}

$mysqli->close();

?>
