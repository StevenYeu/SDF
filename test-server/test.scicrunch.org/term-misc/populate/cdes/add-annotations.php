<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
include_once $docroot . "/api-classes/ilx_add.php";
include_once $docroot . "/api-classes/term/add_term.php";
require_once $docroot . "/api-classes/term/term_by_ilx.php";
//require_once $docroot . "/api-classes/term/term_by_label.php";
include_once $docroot . "/api-classes/term/add_term_annotation.php";
include_once '../from-csv/vendor/autoload.php';

use League\Csv\Reader;

//jeff's
$UID = 247;
$USER = new User();
$USER->getByID($UID);
$CID = '1';
$API_KEY = NULL;

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$annotation_terms = array('Source','Variable type','Arrive');
//$annotation_terms = array('Arrive');
$properties = array();
foreach ($annotation_terms as $label){
   $obj = array();
   $obj['label'] = $label;
   $obj['type'] = 'annotation';
   $obj['status'] = 0;
   $obj['version'] = '1';

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

   $properties["$label"] = $prop_term;
}
//print_r($properties);


$file = "./ODC-SCI_MASTERtemplate_JLN.csv";
$reader = Reader::createFromPath($file);
$reader->setOffset(1);
/*
    [0] => MASTERSort
    [1] => Source
    [2] => SubSort
    [3] => VariableName
    [4] => VariableType
    [5] => Definition
    [6] => ARRIVE
    [7] => Comments
    [8] => Example
*/
foreach ($reader as $index => $row) {
   //print $index . "\n";
   //print_r($row);
   //print "\n";
//continue;
   if ($index == 0){ continue; }

   $label = str_replace('_', ' ', trim($row[3]));
   //$terms = termLookup($USER, $API_KEY, $label);
   $terms = array();
   $sql = "select * from terms where label = '" . $label . "'";
   if ($result = $mysqli->query($sql)) {
       $row0 = $result->fetch_array(MYSQLI_ASSOC);
       $terms[] = $row0;
   }
   if (count($terms) < 1){
      print "There is no term with label " . $label . " \n";
      continue;
   }
   if (count($terms) > 1){
      print "There are more than one term with label " . $label . " \n";
      foreach ($terms as $term){
         if ($term['orig_uid'] == $UID){
            $cde_term = $term;
         }
      }
   } else {
      $cde_term = $terms[0];
   }
   if (!isset($cde_term['id'])) { continue; }

   //add Source
   $source = str_replace('_', ' ', trim($row[1]));
   if (strlen($source) > 0){ 
      echo "DOING Source\n";
      addAnnotation($cde_term, $properties['Source'], $source, $mysqli, $USER, $API_KEY);
   }


   //add Variable type
   $vartype = str_replace('_', ' ', trim($row[4]));
   if (strlen($vartype) > 0){ 
      echo "DOING Variable type\n";
      addAnnotation($cde_term, $properties['Variable type'], $vartype, $mysqli, $USER, $API_KEY);
   }


   //add Arrive
   $arrive = str_replace('_', ' ', trim($row[4]));
   if (strlen($arrive) > 0){ 
      echo "DOING ARRIVE\n";
      addAnnotation($cde_term, $properties['Arrive'], 1, $mysqli, $USER, $API_KEY);
   }


}

function addAnnotation($cde_term, $annotation_term, $value, $mysqli, $USER, $API_KEY){
   $value = $mysqli->escape_string($value);
   $sql = "select count(*) as count from term_annotations where tid = " . $cde_term['id'] . " and annotation_tid = " . $annotation_term['id'] .
          " and value ='" . $value . "'";
   print $sql . "\n";
   if ($result = $mysqli->query($sql)) {
      $row = $result->fetch_array(MYSQLI_ASSOC);
      if ($row['count'] > 0){
         print "tid: " . $cde_term['id'] . " and annotation_tid: " . $annotation_term['id'] . " and value '" . $value . "' already exist in term_annotations\n";
      } else {
         $annotation = array();
         $annotation['annotation_tid'] = $annotation_term['id'];
         $annotation['annotation_term_version'] = $annotation_term['version'];
         $annotation['value'] = $value;
         $annotation['tid'] = $cde_term['id'];
         $annotation['term_version'] = $cde_term['version'];

         $return = array();
         if (strlen($annotation['value']) > 0){
            $return = addTermAnnotation($USER, $API_KEY, $annotation, '0');
         }
         $return['term_label'] = $cde_term['label'];
         $return['annotation_term_label'] = $annotation_term['label'];
         print_r($return);
      }
   }
}

$mysqli->close();
?>
