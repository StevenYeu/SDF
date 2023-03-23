<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
include_once $docroot . "/api-classes/term/curie_catalog.php";
include_once $docroot . "/api-classes/term/term_ontologies.php";
include_once $docroot . "/api-classes/term/term_list.php";
require_once $docroot . "/api-classes/ilx_add.php";
require_once $docroot . "/api-classes/term/add_term.php";
include_once "./arc2/ARC2.php";

$ONT['file'] = './neurolex_basic.ttl';
$ONT['url'] = 'https://raw.githubusercontent.com/tgbugs/nlxeol/master/neurolex_basic.ttl';
$ONT['id'] = 0;

$CC['namespace'] = 'http://neurolex.org/wiki/';
$CC['prefix'] = 'NLXWIKI';
$CC['id'] = 0;

$ANN['label'] = 'Neurolex Category';
$ANN['id'] = 0;
$ANN['ilx'] = '';
$ANN['version'] = 1;

$USER = new User();
$USER->getByID(32309);
if ( preg_match('/stage/',$config['mysql-hostname']) ){
   $USER->getByID(31878); //stage
}
$CID = '30';
$API_KEY = NULL;

$cc2 = getCurieCatalog($USER, $API_KEY);
foreach ($cc2 as $c) {
    if ($CC['namespace'] == trim($c['namespace'])) {
        $CC['id'] = $c['id'];
    }
}
//print_r($CC);

$onto = getTermOntologies($USER, $API_KEY);
foreach ($onto as $o) {
    if ($ONT['url'] == $o['url']) {
        $ONT['id'] = $o['id'];
    }
}
//print_r($ONT);

$anno = getTermList($USER, $API_KEY, "annotation");
foreach ($anno as $a) {
    if ($ANN['label'] == $a['label']) {
        $ANN['id'] = $a['id'];
        $ANN['ilx'] = $a['ilx'];
        $ANN['version'] = $a['version'];
    }
}
//print_r($ANN);

if ( $CC['id'] == 0 || $ONT['id'] == 0 || $ANN['id'] == 0 || $ANN['ilx'] == "" ){
    print "Please make sure you insert curie_catalog, term_ontologies, and terms (of type 'annotation') entries before running this script\n";
    exit;
}

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$parser = ARC2::getRDFParser();
//$parser->nsp('NLXWIKI','http://neurolex.org/wiki/');
//$parser->nsp('ILX','http://uri.interlex.org/base/ilx_');
//$parser->nsp('ilx','http://uri.interlex.org/base/'); ns3
//$parser->nsp('owl','http://www.w3.org/2002/07/owl#'); ns2
//$parser->nsp('skos','http://www.w3.org/2004/02/skos/core#'); ns0
//$parser->nsp('OBOANN','http://ontology.neuinfo.org/NIF/Backend/OBO_annotation_properties.owl#'); ns5
//$parser->nsp('oboInOwl','http://www.geneontology.org/formats/oboInOwl#'); ns4

$parser->parse($ONT['file']);
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

$superclass_terms = array();
$count = 0;
$rdf = new SimpleXMLElement($rdfxml);
foreach ( $rdf->xpath('//Description') as $item ) {
   $count++;
   print "\n" . $count . "\n";
   //print $item->asXML('php://output');
   //print_r($item);

   if ($count == 1){
      if ( $item->attributes()->rdf_about != $ONT['url'] ) {
          print $ONT['url'] . " doesn't match first entry in this file\n";
          exit();
      }
      continue;
   }
   if ($item->ns1_label == "") {
      continue;
   }

   $obj = array();
   $obj['label'] = $item->ns1_label->__toString();
   $obj['definition'] = $item->ns0_definition->__toString();

   $obj['ontologies'] = array('id'=>$ONT['id'], 'url'=>$ONT['url']);

   $syns = array();
   foreach ($item->ns5_synonym->__toString() as $syn){
       $syns[] = $syn;
   }
   $obj['synonyms'] = $syns;

   $eid = $item->attributes()->rdf_about->__toString();
   $parts = explode("/", $eid);
   $frag = $parts[count($parts)-1];
   $existing_id['curie'] = $CC['prefix'] . ":" . $frag;
   $existing_id['curie_catalog_id'] = $CC['id'];
   $existing_id['iri'] = $eid;
   $obj['existing_ids'] = array($existing_id);

   $return = ilxAdd($user,$api_key,$obj['label'],NULL,NULL);
   //print_r($return);
   $ilx = $return->data->fragment;
   $obj["ilx"] = $ilx;

   //add term
   $term = addTerm($USER, $API_KEY, $CID, $obj, '0');

   $annotation['annotation_tid'] = $ANN['id'];
   $annotation['annotation_term_version'] = $ANN['version'];
   $annotation['value'] = $item->ns3_neurolex_category->__toString();
   $annotation['tid'] = $term->id;
   $annotation['term_version'] = $term->version;

   if (strlen($annotation['value']) > 0){
   addTermAnnotation($user, $api_key, $args);
   }

   $sup = $item->ns1_subClassOf->attributes()[0];
   if (strlen($sup) > 1){
      $sup = $sup->__toString();
   }

   //insert term
   $superclass_terms[$sup] = $term;
print_r($obj);
continue;


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

//addTermAnnotation($user, $api_key, $args)
}

?>
