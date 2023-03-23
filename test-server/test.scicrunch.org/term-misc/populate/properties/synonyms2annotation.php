<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
require_once $docroot . "/api-classes/ilx_add.php";
require_once $docroot . "/api-classes/term/add_term.php";
require_once $docroot . "/api-classes/term/add_term_annotation.php";
require_once $docroot . "/api-classes/term/term_by_ilx.php";
require_once $docroot . "/api-classes/term/term_by_id.php";

$UID = 247;
$USER = new User();
$USER->getByID($UID);
$CID = '1';
$API_KEY = NULL;

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$syn_term = array();
$abv_term = array();
$labels = array('Synonym','Abbreviation');
foreach ($labels as $label){
   $obj = array();
   $obj['label'] = $label;
   $obj['type'] = 'annotation';
   $obj['version'] = '1';
   $obj['status'] = '0';
   $obj['display_superclass'] = '1';

   $return = ilxAdd($USER,$API_KEY,$obj['label'],NULL,NULL);
//   print_r($return);
   $ilx = $return->data->fragment;
   $obj["ilx"] = $ilx;

   //add term
   $prop_term = addTerm($USER, $API_KEY, $CID, $obj, '0');
   if ( $prop_term->id < 1 ) {
//      print "duplicate term entry '" . $obj['label'] . "'\n";
      $prop_term = getTermByIlx($USER, $API_KEY, $ilx, '0', '0');
   }

   if ($label == 'Synonym'){
      $syn_term = $prop_term;
   }
   if ($label == 'Abbreviation'){
      $abv_term = $prop_term;
   }
}
//print_r($syn_term);
//print_r($abv_term);

$rows = array();
$sql = "select * from term_synonyms";
if ($result = $mysqli->query($sql)) {
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
       //print_r($row);
       $rows[] = $row;
   }
}
//print_r($rows);

foreach ($rows as $row){
    $term = getTermById($USER, $API_KEY, $row['tid'], '0', '0');
    $value = stripslashes($row['literal']);

    $sql2 = "select count(*) as count from term_annotations where tid = " . $term['id'] . " and annotation_tid = " . $syn_term['id'] .
            " and value ='" . $value . "'";
    if ($row['type'] == 'abbrev'){
        $sql2 = "select count(*) as count from term_annotations where tid = " . $term['id'] . " and annotation_tid = " . $abv_term['id'] .
            " and value ='" . $value . "'";
    }
    //print $sql2 . "\n"; 
    $exists = '0';
    if ($result2 = $mysqli->query($sql2)) { 
        $row2 = $result2->fetch_array(MYSQLI_ASSOC);
        if ($row2['count'] > 0){
            print "tid: " . $term['id'] . " and annotation_tid and value " . $value . " already exist in term_annotations\n";
            //$exists = '1';
            continue;
        }
    }

    if ($exists == '0'){
        $annotation = array();
        $annotation['annotation_tid'] = $syn_term['id'];
        $annotation['annotation_term_version'] = $syn_term['version'];
        $ann_label = $syn_term['label'];
        if ($row['type'] == 'abbrev'){
            $annotation['annotation_tid'] = $abv_term['id'];
            $annotation['annotation_term_version'] = $abv_term['version'];
            $ann_label = $abv_term['label'];
        }
        //$annotation['value'] = $mysqli->escape_string($value);
        $annotation['value'] = stripslashes($value);
        $annotation['tid'] = $term['id'];
        $annotation['term_version'] = $term['version'];

        $return = addTermAnnotation($USER, $API_KEY, $annotation, '0');

        $return['term_label'] = $term['label'];
        $return['annotation_term_label'] = $ann_label;
        print_r($return);
    }
}

$mysqli->close();

?>
