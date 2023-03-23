<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
include_once $docroot . "/api-classes/term/term_ontologies.php";
require_once $docroot . "/api-classes/ilx_add.php";
require_once $docroot . "/api-classes/term/add_term.php";
require_once $docroot . "/api-classes/term/term_by_label.php";
require_once $docroot . "/api-classes/term/add_term_annotation.php";
require_once $docroot . "/api-classes/term/term_by_ilx.php";

$PROP_FILE = "./neurolex_properties.txt";
$ONT['file'] = "./neurolex_basic_pages.json";
$ONT['url'] = "https://raw.githubusercontent.com/SciCrunch/NeuroLex-MW-Tools/master/json/main.json";

$USER = new User();
$USER->getByID(32309);
if ( preg_match('/stage/',$config['mysql-hostname'] )){
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


$properties = array();
if (($handle = fopen($PROP_FILE, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
       if (strlen(trim($data[2])) < 1){ continue; }

       $type = trim($data[2]);
       if ($type == "annotation property"){
          $obj = array();
          $obj['label'] = $data[1];
          $obj['type'] = 'annotation';
          $obj['ontologies'][] = array('id'=>$ONT['id'], 'url'=>$ONT['url']);

          $return = ilxAdd($USER,$API_KEY,$obj['label'],NULL,NULL);
          //print_r($return);
          $ilx = $return->data->fragment;
          $obj["ilx"] = $ilx;

          //add term
          $annotation_term = addTerm($USER, $API_KEY, $CID, $obj, '0');
          if ( $annotation_term->id < 1 ) {
             print "duplicate term entry '" . $obj['label'] . "'\n";
             $annotation_term = getTermByIlx($USER, $API_KEY, $ilx);
          }

           $properties[] = array('property'=>$data[0],'label'=>$data[1], 'term'=>$annotation_term);
       }
    }
    fclose($handle);
}

//print_r($properties);

$iri2tid = array();
$sql = "select tid, iri from term_existing_ids";
if ($result = $mysqli->query($sql)) {
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
       $iri2tid[$row['iri']] = $row['tid'];
   }
}

$json = file_get_contents($ONT['file']);
$data = json_decode($json, true);

$namespace = "http://neurolex.org/wiki/";

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

      foreach ($properties as $prop){
         //print_r($prop);
         if (array_key_exists($prop['property'],$v)){
//print_r($p);

            $values = array();
            if (gettype($v[$prop['property']]) == 'string'){
               $values[] = $v[$prop['property']];
            } elseif (gettype($v[$prop['property']]) == 'array'){
               foreach ($v[$prop['property']] as $item){
                  $values[] = $item;
               }
            }

            $label = str_replace("_", " " , $k);
            $terms = termLookup($USER, $API_KEY, $label);
            if (count($terms) > 1){
               print "There are more than one term with label " . $label . " \n";
            }
            $term = $terms[0];

            foreach ($values as $value){
               $sql = "select count(*) as count from term_annotations where tid = " . $term['id'] . " and annotation_tid = " . $prop['term']['id'] .
                      " and value ='" . $mysqli->escape_string($value) . "'";
               //print $sql . "\n";
               if ($result = $mysqli->query($sql)) {
                  $row = $result->fetch_array(MYSQLI_ASSOC);
                  if ($row['count'] > 0){
                      print "tid: " . $term['id'] . " and annotation_tid: " . $prop['term']['id'] . " already exist in term_annotations\n";
                      continue;
                  }
               }

               $annotation['annotation_tid'] = $prop['term']['id'];
               $annotation['annotation_term_version'] = $prop['term']['version'];
               $annotation['value'] = $v[$prop['property']];
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
