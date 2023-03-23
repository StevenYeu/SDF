<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
include_once $docroot . "/api-classes/term/term_ontologies.php";
require_once $docroot . "/api-classes/ilx_add.php";
require_once $docroot . "/api-classes/term/add_term.php";
require_once $docroot . "/api-classes/term/term_by_label.php";
require_once $docroot . "/api-classes/term/add_term_relationship.php";
require_once $docroot . "/api-classes/term/term_by_ilx.php";
include_once $docroot . "/api-classes/term/curie_catalog.php";

function getCurieCatalogId($cc, $namespace){
   $id = 0;
   foreach ($cc as $c) {
      if (trim($c['namespace']) == trim($namespace)) {
        $id = $c['id'];
      }
   }
   return $id;
}

$PROP_FILE = "./neurolex_properties.txt";
$ONT['file'] = "./neurolex_basic_pages.json";
$ONT['json_url'] = "https://raw.githubusercontent.com/SciCrunch/NeuroLex-MW-Tools/master/json/main.json";
$ONT['csv_url'] = "https://raw.githubusercontent.com/tgbugs/nlxeol/master/neurolex_full.csv";

$USER = new User();
$USER->getByID(32309);
if ( preg_match('/stage/',$config['mysql-hostname']) ){
   $USER->getByID(31878); //stage
}
$CID = '30';
$API_KEY = NULL;

$cc2 = getCurieCatalog($USER, $API_KEY);

$CC['namespace'] = 'http://neurolex.org/wiki/';
$CC['prefix'] = 'NLXWIKI';
$CC['id'] = getCurieCatalogId($cc2, $CC['namespace']);

if ($CC['id'] == 0){
   $cc2 = addToCurieCatalog($USER, $API_KEY, $CC['prefix'], $CC['namespace']);
   $CC['id'] = getCurieCatalogId($cc2, $CC['namespace']);
}

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$onto = getTermOntologies($USER, $API_KEY);
$ontologies = array();
foreach ($onto as $o) {
   $ontologies[$o['url']] = $o['id'];
}

if (!array_key_exists($ONT['json_url'], $ontologies)){
   $insert = "insert into term_ontologies (url) values ('" . $ONT['json_url'] . "')";
   print $insert . "\n";
   $mysqli->query($insert);
   $ONT['json_id'] = $mysqli->insert_id;
} else {
   $ONT['json_id'] = $ontologies[$ONT['url']];
}
if (!array_key_exists($ONT['csv_url'], $ontologies)){
   $insert = "insert into term_ontologies (url) values ('" . $ONT['csv_url'] . "')";
   print $insert . "\n";
   $mysqli->query($insert);
   $ONT['csv_id'] = $mysqli->insert_id;
} else {
   $ONT['csv_id'] = $ontologies[$ONT['csv_url']];
}
//print_r($ONT);


$properties = array();
if (($handle = fopen($PROP_FILE, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
       if (strlen(trim($data[2])) < 1){ continue; }

       $type = trim($data[2]);
       if ($type == "object property"){
          $obj = array();
          $obj['label'] = $data[1];
          $obj['type'] = 'relationship';
          $obj['ontologies'][] = array('id'=>$ONT['json_id'], 'url'=>$ONT['json_url']);

          $return = ilxAdd($USER,$API_KEY,$obj['label'],NULL,NULL);
          //print_r($return);
          $ilx = $return->data->fragment;
          $obj["ilx"] = $ilx;

          //add term
          $relationship_term = addTerm($USER, $API_KEY, $CID, $obj, '0');
          if ( $relationship_term->id < 1 ) {
             print "duplicate term entry '" . $obj['label'] . "'\n";
             $relationship_term = getTermByIlx($USER, $API_KEY, $ilx);
          }

           $properties[] = array('property'=>$data[0],'label'=>$data[1], 'relationship_term'=>$relationship_term);
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

            $labels = array();
            if (gettype($v[$prop['property']]) == 'string'){
               $labels[] = trim($v[$prop['property']]);
            } elseif (gettype($v[$prop['property']]) == 'array'){
               foreach ($v[$prop['property']] as $item){
                  $labels[] = trim($item);
               }
            }

            $term1_label = str_replace("_", " " , $k);
            $terms = termLookup($USER, $API_KEY, $term1_label);
            if (count($terms) > 1){
               print "There are more than one term with label " . $term1_label . " \n";
            }
            $term1 = $terms[0];

            foreach ($labels as $label){
               $terms = termLookup($USER, $API_KEY, $label);
               $term2 = $terms[0];
               if (count($terms) > 1){
                  print "There are more than one term with label " . $label . " \n";
                  $term2 = $terms[0];
               }
               if (count($terms) < 1){
                  print "The label " . $label . " not in our database. Uploading from term_raw table\n";
                  $select = "select coalesce(Label, Categories) as label2, Id_ as eid2 from term_raw where Label = '" .
                            trim($label) . "' or Label = ':Category:" . trim($label) . "'";
print "SELECTION form term_raw\n";
print $select . "\n";

                  $eid2 = "";
                  if ($result = $mysqli->query($select)) {
                     while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                        $eid2 = $row['eid2'];
                     }
                  }
                  if (strlen($eid2) == 0){
                     continue;
                  }


                  $obj = array();
                  $obj['label'] = trim(str_replace(":Category:", "", $label));
                  $obj['type'] = 'relationship';
                  $obj['ontologies'][] = array('id'=>$ONT['csv_id'], 'url'=>$ONT['csv_url']);
                  $obj['existing_ids'][] = array('iri'=>$CC['namespace'].$eid2, 'curie'=>$CC['prefix'].$eid2,'curie_catalog_id'=>$CC['id']);

                  $return = ilxAdd($USER,$API_KEY,$obj['label'],NULL,NULL);
                  //print_r($return);
                  $ilx = $return->data->fragment;
                  $obj["ilx"] = $ilx;

                  //add term
                  $term2 = addTerm($USER, $API_KEY, $CID, $obj, '0');
print "TERM2\n";
print_r($term2);
                  if ( $term2['id'] < 1 ) {
                     print "term2 not inserted:\n";
                     print_r($term2);
                     continue;
                  }
               }


               $sql = "select count(*) as count from term_relationships where term1_id = " . $term1['id'] . " and term2_id = " . $term2['id'] . 
                      " and relationship_tid = " . $prop['relationship_term']['id'];
print "CHECKING term_relationships table\n";
print $sql . "\n";
               if ($result = $mysqli->query($sql)) {
                  $row = $result->fetch_array(MYSQLI_ASSOC);
                  if ($row['count'] > 0){
                      print "term1_id: " . $term1['id'] . " and term2_id: " . $term2['id'] . " and relationship_tid: " . $prop['relationship_term']['id'] . " already exist in term_relationships\n";
                      continue;
                  }
               }

               $relation['relationship_tid'] = $prop['relationship_term']['id'];
               $relation['relationship_term_version'] = $prop['relationship_term']['version'];
               $relation['term1_id'] = $term1['id'];
               $relation['term2_id'] = $term2['id'];
               $relation['term1_version'] = $term1['version'];
               $relation['term2_version'] = $term2['version'];

               $return = array();
               if ($relation['term1_id'] < 1 || $relation['term2_id'] < 1 || $relation['relationship_tid'] < 1 ){
                  print "BAD: missing one of the relationship ids\n";
               } else {
                  $return = addTermRelationship($USER, $API_KEY, $relation, '0');
               }
$relation['term1_label'] = $term1['label'];
$relation['term2_label'] = $term2['label'];
$relation['relationship_term_label'] = $prop['relationship_term']['label'];
print_r($relation);
            }


         }
      }
   }
}

$mysqli->close();
?>
