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
require_once $docroot . "/api-classes/term/add_term_annotation.php";
include_once "./arc2/ARC2.php";

/*
1. add "Neurolex category" of type "annotation" to db
2. go through ttl file and get values for "Neurlox category" annotation property for each term having eid
3. if doesn't exist in term_annotations, insert
*/

$ONT['file'] = './neurolex_basic.ttl';
$ONT['url'] = 'https://raw.githubusercontent.com/tgbugs/nlxeol/master/neurolex_basic.ttl';

$USER = new User();
$USER->getByID(32309);
if ( preg_match('/stage/',$config['mysql-hostname']) ){
   $USER->getByID(31878); //stage
}
$CID = '30';
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
$obj['type'] = 'annotation';
$obj['ontologies'][] = array('id'=>$ONT['id'], 'url'=>$ONT['url']);

$return = ilxAdd($USER,$API_KEY,$obj['label'],NULL,NULL);
//print_r($return);
$ilx = $return->data->fragment;
$obj["ilx"] = $ilx;

//add term
$annotation_term = addTerm($USER, $API_KEY, $CID, $obj, '0');
if ( $annotation_term->id < 1 ) {
   print "duplicate term entry 'Neurolex category'\n";
   $annotation_term = getTermByIlx($USER, $API_KEY, $ilx);
}
//print_r($annotation_term);

$parser = ARC2::getRDFParser();

$parser->parse($ONT['file']);
$triples = $parser->getTriples();
$rdfxml = $parser->toRDFXML($triples);

$rdfxml = str_replace('rdf:Description', 'Description', $rdfxml);
$rdfxml = str_replace('rdf:about', 'rdf_about', $rdfxml);
$rdfxml = str_replace('ns1:label', 'ns1_label', $rdfxml);
$rdfxml = str_replace('ns3:neurolex_category', 'ns3_neurolex_category', $rdfxml);

$superclass_terms = array();
$count = 0;
$rdf = new SimpleXMLElement($rdfxml);
foreach ( $rdf->xpath('//Description') as $item ) {
   $count++;
   print "\n" . $count . "\n";
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
   }
   $term = $terms[0];

   $value = $item->ns3_neurolex_category->__toString();
   $sql = "select count(*) as count from term_annotations where tid = " . $term['id'] . " and annotation_tid = " . $annotation_term['id'] .
          " and value ='" . $mysqli->escape_string($value) . "'";
   //print $sql . "\n";
   if ($result = $mysqli->query($sql)) {
      $row = $result->fetch_array(MYSQLI_ASSOC);
      if ($row['count'] > 0){
          print "tid: " . $term['id'] . " and annotation_tid: " . $annotation_term['id'] . " already exist in term_annotations\n";
          continue;
      }
   }

   $annotation['annotation_tid'] = $annotation_term['id'];
   $annotation['annotation_term_version'] = $annotation_term['version'];
   $annotation['value'] = $value;
   $annotation['tid'] = $term['id'];
   $annotation['term_version'] = $term['version'];

   if (strlen($annotation['value']) > 0){
      addTermAnnotation($USER, $API_KEY, $annotation, '0');
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
