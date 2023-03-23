<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
include_once $docroot . "/api-classes/term/term_ontologies.php";
require_once $docroot . "/api-classes/ilx_add.php";
require_once $docroot . "/api-classes/term/add_term.php";
//require_once $docroot . "/api-classes/term/term_by_label.php";
require_once $docroot . "/api-classes/term/add_term_annotation.php";
require_once $docroot . "/api-classes/term/term_by_ilx.php";

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

$ontologies = getTermOntologies($USER, $API_KEY);
$ONT['csv-url'] = "https://raw.githubusercontent.com/tgbugs/nlxeol/master/neurolex_full.csv";
foreach ($ontologies as $ontology) {
   if (trim($ontology['url']) == $ONT['csv-url']) {
      $ONT['csv-id'] = $ontology['id'];
   }
}

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$prop_list = array();
$sql = "select * from _term_properties where type = 'annotation' and label is not NULL and include = '1'";
if ($result = $mysqli->query($sql)) {
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
       $prop_list[$row['property']] = trim($row['label']);
   }
}
//print_r($prop_list);

$properties = array();
foreach ($prop_list as $prop=>$label){
   $obj = array();
   $obj['label'] = $label;
   $obj['type'] = 'annotation';
   $obj['ontologies'][] = array('id'=>$ONT['csv-id'], 'url'=>$ONT['csv-url']);

   $return = ilxAdd($USER,$API_KEY,$obj['label'],NULL,NULL);
   //print_r($return);
   $ilx = $return->data->fragment;
   $obj["ilx"] = $ilx;

   //add term
   $prop_term = addTerm($USER, $API_KEY, $CID, $obj, '0');
   if ( $prop_term->id < 1 ) {
      print "duplicate term entry '" . $obj['label'] . "'\n";
      $prop_term = getTermByIlx($USER, $API_KEY, $ilx);
   }

   $properties[] = array('property'=>$prop,'label'=>$label, 'term'=>$prop_term);
}

$iri2tid = array();
$sql = "select tid, iri from term_existing_ids";
if ($result = $mysqli->query($sql)) {
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
       $iri2tid[$row['iri']] = $row['tid'];
   }
}
//print_r($iri2tid);

$dir = "./data/files/";
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
      $label = str_replace("_", " ", $label);
      if (!array_key_exists("Id",$fields)){
         print "'" . $label . "' doesn't have ID\n";
         continue;
      }

      $terms = array();
      $sql = "select * from terms where label = '" . $label . "'";
      if ($result = $mysqli->query($sql)) {
         $row = $result->fetch_array(MYSQLI_ASSOC);
         $terms[] = $row;
      }
      //$terms = termLookup($USER, $API_KEY, $label);
      if (count($terms) > 1){
         print "There are more than one term with label " . $label . " \n";
         continue;
      }
      if (count($terms) < 1){
         print "There is no term with label " . $label . " \n";
         continue;
      }
      $term = $terms[0];
      if ($tid != $term['id']){
         print "Mismatch of term id for label '" . $label . "' tid=" . $tid . " id=" . $term['id'] . "\n";
         continue;
      }

      foreach ($properties as $prop){
      //   print_r($prop);
         if (array_key_exists($prop['property'],$fields)){
            $values = array();
            if (gettype($fields[$prop['property']]) == 'string'){
               $values[] = trim($fields[$prop['property']]);
            } elseif (gettype($fields[$prop['property']]) == 'array'){
               foreach ($fields[$prop['property']] as $item){
                  $values[] = trim($item);
               }
            }
            //print_r($values);

            foreach ($values as $value){
               $value = $mysqli->escape_string($value);
               $sql = "select count(*) as count from term_annotations where tid = " . $term['id'] . " and annotation_tid = " . $prop['term']['id'] .
                      " and value ='" . $value . "'";
               //print $sql . "\n";
               if ($result = $mysqli->query($sql)) {
                  $row = $result->fetch_array(MYSQLI_ASSOC);
                  if ($row['count'] > 0){
                      print "tid: " . $term['id'] . " and annotation_tid: " . $prop['term']['id'] . " already exist in term_annotations\n";
                      continue;
                  }
               }

               $annotation = array();
               $annotation['annotation_tid'] = $prop['term']['id'];
               $annotation['annotation_term_version'] = $prop['term']['version'];
               $annotation['value'] = $value;
               $annotation['tid'] = $term['id'];
               $annotation['term_version'] = $term['version'];

               $return = array();
               if (strlen($annotation['value']) > 0){
                  $return = addTermAnnotation($USER, $API_KEY, $annotation, '0');
               }
$return['term_label'] = $term['label'];
$return['annotation_term_label'] = $prop['term']['label'];
print_r($return);

            }
         }
      }
   }
}


$mysqli->close();
?>
