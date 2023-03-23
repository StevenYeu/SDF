<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
include_once $docroot . "/api-classes/ilx_add.php";
include_once $docroot . "/api-classes/term/add_term.php";
include_once '../from-csv/vendor/autoload.php';

use League\Csv\Reader;

//jeff's
$USER = new User();
$USER->getByID(247);
$CID = '1';
$API_KEY = NULL;

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

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
//   print $index . "\n";
//   print_r($row);
//   print "\n";
   if ($index == 0){ continue; }

   $label = str_replace('_', ' ', $row[3]);
   $definition = str_replace('_', ' ', trim($row[5]));
   $comment = trim($row[6]);

   $obj = array();
   $obj['label'] = trim($label);
   $obj['definition'] = $definition;
   $obj['comment'] = $comment;
   $obj['type'] = 'cde';
   $obj['version'] = 1;
   $obj['status'] = 0;

   //print_r($obj);
   $return = ilxAdd($USER,$API_KEY,$obj['label'],NULL,NULL);
   //print_r($return);
   $ilx = $return->data->fragment;
   $obj["ilx"] = $ilx;

   //add term
   $term = addTerm($USER, $API_KEY, $CID, $obj, '0');
   if ( !isset($term['id']) ) {
      print "\n'" . $label . "' NOT inserted:\n";
      print_r($term);
   }else{
      //print "'" . $label . "' inserted successfully:\n";
   }
}

$mysqli->close();
?>
