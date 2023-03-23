<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$sql = "select * from term_curie_catalog";
$stmt = $mysqli->query($sql);
$curie_catalog = array();
while ($row = $stmt->fetch_assoc()){
  $curie_catalog[strtolower($row['prefix'])] = array('catalog_id'=>$row['id'], 'namespace'=>trim($row['namespace']), 'prefix'=>trim($row['prefix']));
} 
$stmt->close();

$rows = array();
$sql = 'select * from terms';
//print $sql;
if ($result = $mysqli->query($sql)) {
   while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
       //print_r($row);
       $rows[] = $row;
   }
}

foreach ($rows as $row){
    $sql2 = "select * from term_existing_ids where tid=" . $row['id'];
    $result2 = $mysqli->query($sql2);
    $row2 = $result2->fetch_assoc();
    if (!isset($row2['tid']) || $row2['tid'] < 0) {
        print $row['id'] . " " . $row['ilx'] . " " . $row['label'] . " is missing eid\n";

        preg_match('/ilx_([0-9]+)/',$row['ilx'], $m);
        $ilx = $m[1];
        $curie = 'ILX:' . $ilx;
        $iri = $curie_catalog['ilx']['namespace'] . $ilx;
        $catalog_id = $curie_catalog['ilx']['catalog_id'];
        $insert = "insert into term_existing_ids " .
            "(tid, curie, iri, curie_catalog_id, preferred, version, time)" .
            "values " .
            "(".$row['id'].",'".$curie."','".$iri."',".$catalog_id.",'1',1,".time().")";
        //print $insert . "\n";
        $result = $mysqli->query($insert);
        if (!$result) {
            printf("%s\n", $mysqli->error);
            exit();
        }

    } 
    $result2->close();
}

$mysqli->close();
?>
