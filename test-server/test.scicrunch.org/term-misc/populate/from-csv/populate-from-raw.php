<?php
error_reporting(E_ERROR);
$docroot = "../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
require_once $docroot . "/api-classes/ilx_add.php";
require_once $docroot . "/api-classes/term/add_term.php";
include $docroot . '/term-misc/populate/csv/vendor/autoload.php';
use League\Csv\Reader;

$user = new User();
//$user->getByID(32309);
$user->getByID(31878); //stage
$cid = '30';
$api_key = NULL;

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

//create easyaccess map of curie_catlog entries
$cc = array();
$sql = "select * from curie_catalog";
if ($result = $mysqli->query($sql)) {
  while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
    $cc[trim($row['prefix'])] = trim($row['namespace']) . "@@" . $row['id'];
  }
}

//read mapping curie file and set up a hash for replacement
$curie_map = array();
$file = $docroot . "/term-misc/populate/csv/neurolex_mapping.csv";
$reader = Reader::createFromPath($file);
foreach ($reader as $index => $row) {
    //if (sizeof($row) < 2){ continue; }
    if (empty($row[1])) { continue; }

    $obj = array();
    $parts = explode(":",$row[1]);
    $parts2 = explode("@@",$cc[$parts[0]]);

    $obj['Id_'] = $row[0];
    $obj['row'] = $row;

    $obj['id'] = $parts[1];
    $obj['curie_catalog_id'] = $parts2[1];
    $obj['prefix'] = explode(":",$row[1])[0];
    $obj['namespace'] = $parts2[0];
    $obj['curie'] = $row[1];
    $obj['iri'] = $obj['namespace'] . $obj['id'];
    $curie_map[$row[0]] = $obj;
}
//print_r($curie_map);

/*
mappings
Your term  -> neurolex_full.csv header value
Label -> Label
//added by hand to all records, OntologyURLs -> OntologyURLs
Definition -> Definition
Synonyms -> Synonym  # requires transformation, currently most are comma separated
Curie::Iri -> Id   # requires transformation
Superclasses -> SuperCategory  # requires transformation :Category:
Abbrev -> Synonym:abrev

//example json:
//         {"key":"xxxxx","cid":52,
//         "definition":"Another neuron",
//         "ontology_urls":"https://......",
//         "label":"UBERON:0000955",
//         "ilx":"SCR_000001",
//         "synonyms":[{"literal":"synonym1"},{"literal":"syntonym2"}],
//         "existing_ids":[{"curie":"GO:123","iri":"http://iri1"},{"curie":"UBERON:567","iri":"http://iri2"},{"curie_catalog_id":7}],
//         "superclasses":[{"superclass_tid":5}]}
*/

$sql = "select coalesce(Label, Categories) as Label, Abbrev, Synonym, Definition, Id_, SuperCategory from term_raw";

$superclasses = array();
if ($result = $mysqli->query($sql)) {
  $count = 0;
  while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
    //print_r($row);
    $count++;
//if ($count > 20 ) {break;}
    $obj = array();
    $obj["label"] = preg_match("/^:Category:/",$row['Label']) ? preg_replace("/^:Category:/","",$row['Label']) : $row['Label'];
    $obj["definition"] = $row['Definition'];
    //$obj["test"] = $curie_map[$row['Id_']];

    $obj["existing_ids"] = array();
    if (isset($row['Id_']) && !empty($row['Id_'])){
        $eids = explode(",", $row['Id_']);
        for ($i=0; $i<sizeof($eids); $i++){
           $map = $curie_map[$eids[$i]];
           //echo $eids[$i] . "\n";
           if (isset($map) && !empty($map)){
              //print_r($map);
              $obj["existing_ids"][] = array("curie"=>$map['curie'], "iri"=>$map['iri'], "curie_catalog_id"=>$map['curie_catalog_id']);
           }
        }
    }

    //need to deal with iupac nomenclature, [1-6],[1-6]['-]
    $obj["synonyms"] = array();
    if (isset($row['Synonym']) && !empty($row['Synonym'])) {
        $syns = explode(',', $row['Synonym']);
        if (preg_match("/[1-6]{1},[1-6]{1}/",$row['Synonym'])) {
           $tmp = preg_replace("/([1-6]),([1-6])/", "$1CHANGEME$2", trim($row['Synonym']));
           $syns = explode(',', $tmp);
        }

        for ($i=0; $i<sizeof($syns); $i++){
           $syn = trim($syns[$i]);
           if (preg_match("/CHANGEME/",$syn)) {
              $syn = preg_replace("/CHANGEME/",',',$syn);
           }
           $obj["synonyms"][] = array("literal"=>$syn);
        }
    }
    if (isset($row['Abbrev']) && !empty($row['Abbrev'])) {
        $syns = explode(",", trim($row['Abbrev']));
        for ($i=0; $i<sizeof($syns); $i++){
            $obj["synonyms"][] = array("literal"=>trim($syns[$i]),"type"=>'abbrev');
        }
    }
    //$obj["Synonym"] = $row['Synonym'];
    //print_r($obj);

    $return = ilxAdd($user,$api_key,$obj['label'],NULL,NULL);
    //print_r($return);
    $ilx = $return->data->fragment;
    $obj["ilx"] = $ilx;

    //add term
    $term = addTerm($user, $api_key, $cid, $obj);

    //collect supercategory and its term id for later inserting into term_superclasses
    if (isset($row['SuperCategory']) && !empty($row['SuperCategory'])){
        $superclasses[] = array("SuperCategory"=>$row['SuperCategory'], "tid"=>$term['id'], "label"=>$term['label']);
    }
  }
}

foreach ($superclasses as $sup) {
  //get superclass_tid having SuperCategory label
  $sql = "select id, ilx, label, definition from terms " .
         "where lower(label) = '" . $mysqli->escape_string(strtolower($sup['SuperCategory'])) . "'";
  if ($result = $mysqli->query($sql)) {
    $num = 0;
    while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
       $num++;
       if ($num > 1) {
          echo "Bad ($num terms.label matches): SuperCategory.term_raw:" . $sup['SuperCategory'] . " term.id:" . $sup['tid'] . " term.label:" . $sup['label'] . "\n";
          continue;
       }

       //insert into term_superclasses
       $insert = "insert into term_superclasses (tid, superclass_tid, version, time) values (" .
                 $sup['tid'] . ", " . $row['id'] . ", 1, " . time() . ")";
       //echo $insert . "\n";
       $mysqli->query($insert);
    }
  } 
  else {
    echo "Bad (no terms.label match): SuperCategory.term_raw:" . $sup['SuperCategory'] . " term.id:" . $sup['tid'] . " term.label:" . $sup['label'] . "\n";
  }

}

$mysqli->close();


?>
