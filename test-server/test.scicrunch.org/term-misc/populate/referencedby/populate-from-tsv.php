<?php 
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$file = "./value_mapping.tsv";
/*
    [0] => id
    [1] => concept_id
    [2] => concept_name
    [3] => is_whole
    [4] => notes
    [5] => val
    [6] => column_id
    [7] => column_name
    [8] => rel_name
    [9] => rel_nif_id
    [10] => source_nif_id
    [11] => source_name
    [12] => is_ambiguous
    [13] => iri
    [14] => matched_val

Example:
    [0] => 38355
    [1] => BIRNOBO:birnlex_4
    [2] => Organ
    [3] => false
    [4] => null
    [5] => organ abnormality
    [6] => 705
    [7] => aspect_text
    [8] => DiseasePhenotypes
    [9] => nlx_151835-1
    [10] => nlx_151835
    [11] => HPO Annotations
    [12] => true
    [13] => http://ontology.neuinfo.org/NIF/Backend/BIRNLex-OBO-UBO.owl#birnlex_4
    [14] => organ
*/
$found = 0;
$notFound = 0;
$handle = fopen($file, "r");
if ($handle) {
    $index = 0;
    while (($line = fgets($handle)) !== false) {
        $index++;
        $line = trim($line);
        $line = str_replace("\n", "", $line);
//print $line . "\n";
//if ($index > 100) { exit; }
        $bits = explode("\t", $line);
        if ($index == 1){
           $titles = $bits;
           //print_r($titles);
           continue;
        }
        if (sizeof($bits) != sizeof($titles)) {
           echo "Bad: line " . $index . "\n";
           echo sizeof($bits) . " != " . sizeof($titles) . " ";
           print_r($bits);
           continue;
        }

        $ar = explode(":", $bits[1]);
        $id = $ar[1];
        $nlx = 'NLXWIKI:' . $bits[1];
        $sql = "select * from term_existing_ids where curie = '" . $nlx . "' or curie like '%:" . $id . "'";
//print $sql . "\n";
        $stmt = $mysqli->query($sql);
        $numRows = $stmt->num_rows;
        if ($numRows > 0 ){ 
           $found++;
           //print "found " . $bits[1] . "\n";
           while ($row = $stmt->fetch_assoc()){
              //print_r($rows);
              $insert = "insert into _term_mapping_dump 
                (existing_id,concept_id,concept_name,is_whole,notes,val,column_id,column_name,rel_name,rel_nif_id,source_nif_id,source_name,is_ambiguous,iri,matched_val) 
                values (" . 
                       " '" . $row['existing_id'] .
                       "', '" . $bits[1] .
                       "', '" . $bits[2] .
                       "', '" . $bits[3] .
                       "', '" . $bits[4] .
                       "', '" . $bits[5] .
                       "', '" . $bits[6] .
                       "', '" . $bits[7] .
                       "', '" . $bits[8] .
                       "', '" . $bits[9] .
                       "', '" . $bits[10] .
                       "', '" . $bits[11] .
                       "', '" . $bits[12] .
                       "', '" . $bits[13] .
                       "', '" . $bits[14] .
                       "')";
           }
        }
        else {
           $notFound++;
           $insert = "insert into _term_mapping_dump 
                (concept_id,concept_name,is_whole,notes,val,column_id,column_name,rel_name,rel_nif_id,source_nif_id,source_name,is_ambiguous,iri,matched_val) 
                values (" . 
                       "'" . $bits[1] .
                       "', '" . $bits[2] .
                       "', '" . $bits[3] .
                       "', '" . $bits[4] .
                       "', '" . $bits[5] .
                       "', '" . $bits[6] .
                       "', '" . $bits[7] .
                       "', '" . $bits[8] .
                       "', '" . $bits[9] .
                       "', '" . $bits[10] .
                       "', '" . $bits[11] .
                       "', '" . $bits[12] .
                       "', '" . $bits[13] .
                       "', '" . $bits[14] .
                       "')";
           //print "notFound " . $bits[1] . "\n";
        }
        //print $insert . "\n";
        $mysqli->real_escape_string($insert);
        $mysqli->query($insert);

        $stmt->close();
    }
    fclose($handle);
}
print "found: " . $found . "\n";
print "notFound: " . $notFound . "\n";

$mysqli->close();
exit;

