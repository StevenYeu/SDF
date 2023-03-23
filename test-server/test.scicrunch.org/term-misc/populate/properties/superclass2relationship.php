<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
require_once $docroot . "/api-classes/ilx_add.php";
require_once $docroot . "/api-classes/term/add_term.php";
require_once $docroot . "/api-classes/term/add_term_relationship.php";
require_once $docroot . "/api-classes/term/term_by_ilx.php";
require_once $docroot . "/api-classes/term/term_by_id.php";

$UID = 247;
$USER = new User();
$USER->getByID($UID);
$CID = '1';
$API_KEY = NULL;

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

// add subClassOf
$obj = array();
$obj['label'] = 'subClassOf';
$obj['type'] = 'relationship';
$obj['version'] = '1';
$obj['status'] = '0';
$obj['display_superclass'] = '1';

$return = ilxAdd($USER,$API_KEY,$obj['label'],NULL,NULL);
//   print_r($return);
$ilx = $return->data->fragment;
$obj["ilx"] = $ilx;

//add term
$rel_term = addTerm($USER, $API_KEY, $CID, $obj, '0');
if ( $rel_term->id < 1 ) {
//      print "duplicate term entry '" . $obj['label'] . "'\n";
    $rel_term = getTermByIlx($USER, $API_KEY, $ilx, '0', '0');
}
//print_r($rel_term);

$rows = array();
$sql = "select * from term_superclasses";
if ($result = $mysqli->query($sql)) {
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
       //print_r($row);
       $rows[] = $row;
   }
}
//print_r($rows);

foreach ($rows as $row){

    $term = getTermById($USER, $API_KEY, $row['tid'], '0', '0');
    $sup_term = getTermById($USER, $API_KEY, $row['superclass_tid'], '0', '0');

    $sql2 = "select count(*) as count from term_relationships where term1_id = " . $term['id'] . " and term2_id = " . $sup_term['id'] .
                      " and relationship_tid = " . $rel_term['id'];

    //print $sql2 . "\n"; 
    $exists = '0';
    if ($result2 = $mysqli->query($sql2)) { 
        $row2 = $result2->fetch_array(MYSQLI_ASSOC);
        if ($row2['count'] > 0){
            print "term1_id: " . $term['id'] . " and term2_id: " . $sup_term['id'] . " and relationship_tid: " . $rel_term['id'] . " already exist in term_relationships\n";
            $exists = '1';
        }
    }

    if ($exists == '0'){
        $relation = array();
        $relation['relationship_tid'] = $rel_term['id'];
        $relation['relationship_term_version'] = $rel_term['version'];
        $relation['term1_id'] = $term['id'];
        $relation['term1_version'] = $term['version'];
        $relation['term2_id'] = $sup_term['id'];
        $relation['term2_version'] = $sup_term['version'];

        $return = array();
        if ($relation['term1_id'] < 1 || $relation['term2_id'] < 1 || $relation['relationship_tid'] < 1 ){
           print "BAD: missing one of the relationship id: tid:" . $row['tid'] . " sup_tid:" . $row['superclass_tid'] . "\n";
        } else {
           $return = addTermRelationship($USER, $API_KEY, $relation, '0');
        }
        $return['term1_label'] = $term['label'];
        $return['term2_label'] = $sup_term['label'];
        $return['relationship_term_label'] = $rel_term['label'];
        print_r($return);
    }
}

$mysqli->close();

?>
