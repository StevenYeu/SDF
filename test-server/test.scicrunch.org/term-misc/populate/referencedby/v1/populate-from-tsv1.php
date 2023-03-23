<?php 
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';


$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);


$file = "./key_term_mapping.tsv";
$contentsOfFile = file_get_contents($file);
$delimiter = "\n";
$splitcontents = explode($delimiter, $contentsOfFile);
$titles = array();
$index = 0;
foreach ( $splitcontents as $line )
{
    $index++;
    $bits = explode("\t", $line);
    if ($index == 1){
       $titles = $bits;
       //print_r($titles);
       continue;
    }
    if (sizeof($bits) != sizeof($titles)) {
       //echo "Bad: line " . $index . "\n";
       //echo sizeof($bits) . " != " . sizeof($titles) . " ";
       //print_r($bits);
       continue;
    }
//    var_dump($bits);
/*
    [0] => id
    [1] => ont_id
    [2] => concept_id
    [3] => concept_name
    [4] => source_name
    [5] => view_name
    [6] => column_name
    [7] => val
    [8] => data_type
    [9] => is_whole
    [10] => element_path
    [11] => text_path
    [12] => text_block_preceeding
    [13] => text_block_following
    [14] => rel_expr
    [15] => notes
    [16] => last_changed_by
    [17] => version_time
*/
    $num = $index - 1;
    $sql = "insert into _key_term_mapping values (" . $num . ", " .
            str_replace("(null)", NULL, $bits[1]) . ", '" . 
            str_replace("(null)", NULL, $bits[2]) . "', '" .
            str_replace("(null)", NULL, $bits[3]) . "', '" .
            str_replace("(null)", NULL, $bits[4]) . "', '" .
            str_replace("(null)", NULL, $bits[5]) . "', '" .
            str_replace("(null)", NULL, $bits[6]) . "', '" .
            str_replace("(null)", NULL, $bits[7]) . "', '" .
            str_replace("(null)", NULL, $bits[8]) . "', " .
            str_replace("(null)", NULL, $bits[9]) . ", '" .
            str_replace("(null)", NULL, $bits[10]) . "', '" .
            str_replace("(null)", NULL, $bits[11]) . "', '" .
            str_replace("(null)", NULL, $bits[12]) . "', '" .
            str_replace("(null)", NULL, $bits[13]) . "', '" .
            str_replace("(null)", NULL, $bits[14]) . "', '" .
            str_replace("(null)", NULL, $bits[15]) . "', '" .
            str_replace("(null)", NULL, $bits[16]) . "', '" .
            $bits[17] . "')\;";
    //$sql = $mysqli->real_escape_string($sql);
print $sql . "\n";
     //$mysqli->query($sql);
    //$stmt = $mysqli->prepare($sql);
    //$stmt->execute();
    //$stmt->close();
}

$mysqli->close();

?>
