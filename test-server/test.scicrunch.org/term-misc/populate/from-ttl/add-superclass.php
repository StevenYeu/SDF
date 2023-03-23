<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
include_once $docroot . "/api-classes/term/term_by_label.php";
include_once "./arc2/ARC2.php";

$FILE = './neurolex_basic.ttl';

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

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
$rdfxml = str_replace('ns0:definition', 'ns0_definition', $rdfxml);
$rdfxml = str_replace('rdf:about', 'rdf_about', $rdfxml);
$rdfxml = str_replace('ns1:label', 'ns1_label', $rdfxml);
$rdfxml = str_replace('ns5:synonym', 'ns5_synonym', $rdfxml);
$rdfxml = str_replace('ns3:neurolex_category', 'ns3_neurolex_category', $rdfxml);
$rdfxml = str_replace('ns1:subClassOf', 'ns1_subClassOf', $rdfxml);
$rdfxml = str_replace('rdf:resource', 'rdf_resource', $rdfxml);

$count = 0;
$rdf = new SimpleXMLElement($rdfxml);
foreach ( $rdf->xpath('//Description') as $item ) {
   $count++;
 //  print "\n" . $count . "\n";
   //print $item->asXML('php://output');
   //print_r($item);

   if ($count == 1){ continue; }

   if ($item->ns1_label == "") {
      continue;
   }

   $SUP = array();
   $label = $item->ns1_label->__toString();
   $terms = termLookup($USER, $API_KEY, $label);
   if (count($terms) > 1){
      print "THERE are TWO terms with the label '" . $label . "\n";
      continue;
   }

   $SUP['tid'] = $terms[0]['id'];
   $SUP['version'] = $terms[0]['version'];

   $sup = $item->ns1_subClassOf->attributes()[0];
   if (strlen($sup) > 1){
      $sup = $sup->__toString();
   }

   $sql = "select * from term_existing_ids where iri = '" . $sup . "'";
   if ($result = $mysqli->query($sql)) {
      while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
         $SUP['superclass_tid'] = $row['tid'];
      }
   }

   if (!isset($SUP['superclass_tid']) && strlen($sup) > 0){
      print "BAD: " . $sup . " not in db\n";
      continue;
   }

   if (strlen($sup) > 0){
      $sql = "select count(*) as count from term_superclasses where tid = " . $SUP['tid'] . " and superclass_tid = " . $SUP['superclass_tid'];
      //print "$sql\n";
      if ($result = $mysqli->query($sql)) {
         $row = $result->fetch_array(MYSQLI_ASSOC);
         if ($row['count'] > 0){
            print "tid: " . $SUP['tid'] . " and superclass_tid: " . $SUP['superclass_tid'] . " already exist in database\n";
            continue;
         }
      }

      $insert = "insert into term_superclasses (tid, superclass_tid, version, time) " .
             "value (" . $SUP['tid'] . ", " . $SUP['superclass_tid'] . ", " . $SUP['version'] . ", " . time() . ")";

      print $insert . "\n";
      $mysqli->query($insert);
   }

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

$mysqli->close();

?>
