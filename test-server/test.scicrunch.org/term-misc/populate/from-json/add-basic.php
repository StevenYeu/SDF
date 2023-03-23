<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
include_once $docroot . "/api-classes/term/curie_catalog.php";
include_once $docroot . "/api-classes/term/add_curie_catalog.php";
include_once $docroot . "/api-classes/term/term_ontologies.php";
include_once $docroot . "/api-classes/term/add_term_ontology.php";
include_once $docroot . "/api-classes/ilx_add.php";
include_once $docroot . "/api-classes/term/add_term.php";
include_once "../from-ttl/arc2/ARC2.php";

$USER = new User();
$USER->getByID(32309);
$CID = '0';
if ( preg_match('/stage/',$config['mysql-hostname']) ){
   $USER->getByID(31878); //stage
}
if ( preg_match('/nif-mysql/',$config['mysql-hostname']) ){
   $USER->getByID(32290); //stage
   $CID = '0';
}
$API_KEY = NULL;

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

//////////////////////////////////////////
// add curie_catalog entries, if not in db
//////////////////////////////////////////
$catalog = getCurieCatalog($USER, $API_KEY);
$CC['wiki-namespace'] = 'http://neurolex.org/wiki/';
$CC['wiki-prefix'] = 'NLXWIKI';
$CC['ilx-namespace'] = 'http://uri.interlex.org/base/ilx_';
$CC['ilx-prefix'] = 'ILX';
foreach ($catalog as $c) {
   if (trim($c['namespace']) == $CC['wiki-namespace']) {
      $CC['wiki-id'] = $c['id'];
   }
   if (trim($c['namespace']) == $CC['ilx-namespace']) {
      $CC['ilx-id'] = $c['id'];
   }
}

if (!isset($CC['wiki-id'])){
   $entry = addToCurieCatalog($USER, $API_KEY, $CC['wiki-prefix'], $CC['wiki-namespace']);
   $CC['wiki-id'] = $entry['id'];
   print "Added '" . $CC['wiki-prefix'] . "': '" . $CC['wiki-namespace'] . "' to curie_catalog. ID:" . $CC['wiki-id'] . "\n";
}
if (!isset($CC['ilx-id'])){
   $entry = addToCurieCatalog($USER, $API_KEY, $CC['ilx-prefix'], $CC['ilx-namespace']);
   $CC['ilx-id'] = $entry['id'];
   print "Added '" . $CC['ilx-prefix'] . "': '" . $CC['ilx-namespace'] . "' to curie_catalog. ID:" . $CC['ilx-id'] . "\n";
}
//print_r($CC);

//////////////////////////////////////////
// add term_ontologies entries, if not in db
//////////////////////////////////////////
$ontologies = getTermOntologies($USER, $API_KEY);
$ONT['ttl-file'] = "./neurolex_basic.ttl";
$ONT['ttl-url'] = "https://raw.githubusercontent.com/tgbugs/nlxeol/master/neurolex_basic.ttl";
$ONT['csv-url'] = "https://raw.githubusercontent.com/tgbugs/nlxeol/master/neurolex_full.csv";
foreach ($ontologies as $ontology) {
   if (trim($ontology['url']) == $ONT['ttl-url']) {
      $ONT['ttl-id'] = $ontology['id'];
   }
   if (trim($ontology['url']) == $ONT['csv-url']) {
      $ONT['csv-id'] = $ontology['id'];
   }
}

if (!isset($ONT['ttl-id'])){
   $entry = addToTermOntology($USER, $API_KEY, $ONT['ttl-url']);
   $ONT['ttl-id'] = $entry['id'];
   print "Added '" . $ONT['ttl-url'] . "' to term_ontologies. ID:" . $ONT['ttl-id'] . "\n";
}
if (!isset($ONT['csv-id'])){
   $entry = addToTermOntology($USER, $API_KEY, $ONT['csv-url']);
   $ONT['csv-id'] = $entry['id'];
   print "Added '" . $ONT['csv-url'] . "' to term_ontologies. ID:" . $ONT['csv-id'] . "\n";
}
//print_r($ONT);

//////////////////////////////////////////
// find entries in neurolex_basic turtle file
//////////////////////////////////////////
$parser = ARC2::getRDFParser();
$parser->parse($ONT['ttl-file']);
$triples = $parser->getTriples();
$rdfxml = $parser->toRDFXML($triples);

$turtle_entries = array();
$rdfxml = str_replace('rdf:Description', 'Description', $rdfxml);
$rdfxml = str_replace('rdf:about', 'rdf_about', $rdfxml);
$rdfxml = str_replace('ns1:label', 'ns1_label', $rdfxml);

$rdf = new SimpleXMLElement($rdfxml);
foreach ( $rdf->xpath('//Description') as $item ) {
   if ($item->ns1_label == "") {
      continue;
   }

   $eid = $item->attributes()->rdf_about->__toString();
   $turtle_entries[] = $eid;
}
//print_r($turtle_entries);


//////////////////////////////////////////
// add terms marked as "not exclude" in term_list
//////////////////////////////////////////
$included_terms = array();
$sql = "select id, label from _term_upload_list where exclude = '0' and label is not null";
if ($result = $mysqli->query($sql)) {
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
      $id = trim($row['id']);
      //$filename = str_replace(":","__", $id);
      $included_terms[$id] = $row['label'];
   }
}
//print_r($included_terms);

//////////////////////////////////////////
// add terms that their json entries are fetched
//////////////////////////////////////////
$dir = "./data/files/";
foreach($included_terms as $filename=>$label){
   $file_path = $dir . $filename . ".json";
   if (!file_exists($file_path)) {
      //print 'File "' . $label . '"  id:"' . $filename . '" NOT FETCHED!' . "\n";
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
      $skip = false;

      $eid = $CC['wiki-namespace'] . trim($fields['Id']);

      $obj = array();
      $obj['label'] = trim($label);
      $obj['type'] = 'term';
      $obj['version'] = 1;
      $obj['status'] = 0;

      $obj['ontologies'][] = array('id'=>$ONT['csv-id'], 'url'=>$ONT['csv-url']);
      if(in_array($eid, $turtle_entries)){
         $obj['ontologies'][] = array('id'=>$ONT['ttl-id'], 'url'=>$ONT['ttl-url']);
      }

      $parts = explode("/", $eid);
      $frag = $parts[count($parts)-1];
      $existing_id['curie'] = $CC['wiki-prefix'] . ":" . $frag;
      $existing_id['curie_catalog_id'] = $CC['wiki-id'];
      $existing_id['iri'] = $eid;
      $existing_id['preferred'] = '0';
      $obj['existing_ids'] = array($existing_id);

      if(array_key_exists("Definition",$fields)){
         $def = trim($fields['Definition']);
         //if(strlen($def) != mb_strlen($def, 'utf-8')) { 
            //echo "'" . $label . "' contains non-English words\n" . $def . "\n\n";
         //}
         if(preg_match('/TBD/', $def)) { 
            //if(preg_match('/(retired)/', $def)) { 
               echo "'" . $label . "' (Id:" . $fields['Id'] . ") skipped. definition:\n" . $def . "\n\n";
               $skip = true;
            //}
         }

         $obj['definition'] = $def;
      }
     // print_r($obj);
      if ($skip == true){
         continue;
      }

      $return = ilxAdd($USER,$API_KEY,$obj['label'],NULL,NULL);
      //print_r($return);
      $ilx = $return->data->fragment;
      $obj["ilx"] = $ilx;

      //add term
      $term = addTerm($USER, $API_KEY, $CID, $obj, '0');
      if ( $term['id'] < 1 ) {
         print "'" . $label . "' NOT inserted:\n";
         print_r($term);
      }else{
         //print "'" . $label . "' inserted successfully:\n";
      }
   }
}

$mysqli->close();

?>
