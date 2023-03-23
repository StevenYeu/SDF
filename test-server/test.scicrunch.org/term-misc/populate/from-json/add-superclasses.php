<?php
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
//include_once $docroot . "/api-classes/term/term_by_label.php";
//include_once $docroot . "/classes/term.php";

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

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

$NAMESPACE = "http://neurolex.org/wiki/";
$BAD = array();

//////////////////////////////////////////
// get a list of terms
//////////////////////////////////////////
$iri2tid = array();
$sql = "select tid, iri from term_existing_ids";
if ($result = $mysqli->query($sql)) {
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
       $iri2tid[$row['iri']] = $row['tid'];
   }
}
//print_r($iri2tid);


//////////////////////////////////////////
// add superclasses for term list from json files
//////////////////////////////////////////
$dir = "./data/files/";
$superclasses = array();
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
      if (!array_key_exists("Id",$fields)){
         print "'" . $label . "' doesn't have ID\n";
         continue;
      }

      $eid = $NAMESPACE . trim($fields['Id']);
      if (in_array($eid, $BAD)) {
         print 'BAD ' . $eid . ": " . $tid . "\n";
         continue;
      }

      if (array_key_exists("SuperCategory",$fields)){
         //print $eid . " SuperCategory: " . $fields['SuperCategory'] . "\n"; 
         if (gettype($fields['SuperCategory']) == 'array'){
            //print count($fields['SuperCategory']) . "\n\n";
            foreach ($fields['SuperCategory'] as $a){
               $superclasses[$tid][] = trim($a);
            }
            //print_r( $fields['Abbrev'] );
         }
         if (gettype($fields['SuperCategory']) == 'string'){
            $superclasses[$tid][] = trim($fields['SuperCategory']);
            //print $fields['SuperCategory']. "\n";
         }
      }

   }
}

$not_loaded = array();
foreach ($superclasses as $tid=>$array) {
   foreach ($array as $label){
      $label = str_replace("_", " ", $label);

      $terms = array();
      $sql = "select * from terms where label = '" . $label . "'";
      if ($result = $mysqli->query($sql)) {
         $row = $result->fetch_array(MYSQLI_ASSOC);
         $terms[] = $row;
      }
      //$terms = termLookup($USER, $API_KEY, $label);
      if (count($terms) > 1){
         print "THERE are TWO terms with the label '" . $label . "'\n";
         continue;
      }
      if (count($terms) < 1){
         //print "There is no terms with the SuperCategory label '" . $label . "'\n";
         $not_loaded["$label"] = 1;
         continue;
      }

      $SUP = array();
      $SUP['tid'] = $tid;
      $SUP['superclass_tid'] = $terms[0]['id'];
      $SUP['version'] = 1;
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

      //print $insert . "\n";
      $mysqli->query($insert);
   
   }
}

$nl_file = "./superclass-not-loaded.txt";
$handle = fopen($nl_file, 'w') or die('Cannot open file:  '.$nl_file."\n");
foreach (array_keys($not_loaded) as $nl){
    $data = "There is no terms with the SuperCategory label '" . $nl . "'\n";
    fwrite($handle, $data);
   //print $nl . "\n";
}
fclose($handle);


$mysqli->close();
?>
