<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
include_once $docroot . "/api-classes/term/term_ontologies.php";
require_once $docroot . "/api-classes/ilx_add.php";
require_once $docroot . "/api-classes/term/term_by_ilx.php";
require_once $docroot . "/api-classes/term/term_by_label.php";
require_once $docroot . "/api-classes/term/add_term.php";
require_once $docroot . "/api-classes/term/add_term_relationship.php";
include_once "../from-ttl/arc2/ARC2.php";

/*
1. add "Neurolex category" of type "relationship" to db
2. go through ttl file and get values for "Neurlox category" relationship property for each term having eid
3. if doesn't exist in term_relationships, insert
*/

$ONT['file'] = './neurolex_basic.ttl';
$ONT['url'] = 'https://raw.githubusercontent.com/tgbugs/nlxeol/master/neurolex_basic.ttl';

$USER = new User();
$USER->getByID(32309);
$CID = '0';
if ( preg_match('/stage/',$config['mysql-hostname']) ){
   $USER->getByID(31878); //stage
}
if ( preg_match('/nif-mysql/',$config['mysql-hostname']) ){
   $USER->getByID(32290); 
   $CID = '0';
}
$API_KEY = NULL;

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$onto = getTermOntologies($USER, $API_KEY);
$ontologies = array();
foreach ($onto as $o) {
   $ontologies[$o['url']] = $o['id'];
}

if (!array_key_exists($ONT['url'], $ontologies)){
   $insert = "insert into term_ontologies (url) values ('" . $ONT['url'] . "')";
   print $insert . "\n";
   $mysqli->query($insert);
   $ONT['id'] = $mysqli->insert_id;
} else {
   $ONT['id'] = $ontologies[$ONT['url']];
}
//print_r($ONT);

$obj = array();
$obj['label'] = 'Neurolex category';
$obj['type'] = 'object';
$obj['ontologies'][] = array('id'=>$ONT['id'], 'url'=>$ONT['url']);

$return = ilxAdd($USER,$API_KEY,$obj['label'],NULL,NULL);
//print_r($return);
$ilx = $return->data->fragment;
$obj["ilx"] = $ilx;

//add term
$prop_term = addTerm($USER, $API_KEY, $CID, $obj, '0');
if ( $prop_term->id < 1 ) {
   print "duplicate term entry 'Neurolex category'\n";
   $prop_term = getTermByIlx($USER, $API_KEY, $ilx);
}
//print_r($prop_term);

$parser = ARC2::getRDFParser();

$parser->parse($ONT['file']);
$triples = $parser->getTriples();
$rdfxml = $parser->toRDFXML($triples);

$rdfxml = str_replace('rdf:Description', 'Description', $rdfxml);
$rdfxml = str_replace('rdf:about', 'rdf_about', $rdfxml);
$rdfxml = str_replace('ns1:label', 'ns1_label', $rdfxml);
$rdfxml = str_replace('ns3:neurolex_category', 'ns3_neurolex_category', $rdfxml);

$count = 0;
$rdf = new SimpleXMLElement($rdfxml);
foreach ( $rdf->xpath('//Description') as $item ) {
   $count++;
   //print "\n" . $count . "\n";
   //print $item->asXML('php://output');
   //print_r($item);

   if ($count == 1){
      continue;
   }
   $label = $item->ns1_label->__toString();
   if ($label == "") {
      continue;
   }

   $terms = termLookup($USER, $API_KEY, $label);
   if (count($terms) > 1){
      print "There are more than one term with label " . $label . " \n";
      continue;
   }
   if (count($terms) < 1){
      print "There is no entry with label " . $label . " \n";
      continue;
   }
   $term1 = $terms[0];

   $value = $item->ns3_neurolex_category->__toString();
   $value = preg_replace("/_/"," ", trim($value));
   //$value = $mysqli->escape_string($value);
   if (strlen($value) < 1){
      continue;
   }

   $terms2 = termLookup($USER, $API_KEY, $value);
   if (count($terms2) > 1){
      print "There are more than one term with label " . $value . " \n";
   }
   if (count($terms2) < 1){
      print "There is no entry with label " . $value . " \n";
      continue;
   }
   $term2 = $terms2[0];

   $sql = "select count(*) as count from term_relationships where term1_id = " . $term1['id'] . " and relationship_tid = " . $prop_term['id'] .
          " and term2_id  = " . $term2['id'];
   print $sql . "\n";
continue;
   if ($result = $mysqli->query($sql)) {
      $row = $result->fetch_array(MYSQLI_ASSOC);
      if ($row['count'] > 0){
          print "term1_id: " . $term1['id'] . " and term2_id: " . $term2['id'] . " and relationship_tid: " . $prop_term['id'] . " already exist in term_relationships\n";
          continue;
      }
   }

   $relationship['relationship_tid'] = $prop_term['id'];
   $relationship['relationship_term_version'] = $prop_term['version'];
   $relationship['term1_id'] = $term1['id'];
   $relationship['term1_version'] = $term1['version'];
   $relationship['term2_id'] = $term2['id'];
   $relationship['term2_version'] = $term2['version'];

   $return = addTermRelationship($USER, $API_KEY, $relationship, '0');

}
$mysqli->close();

?>
