<?php 
error_reporting(E_ERROR);
$docroot = "../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

$sql = "select * from term_existing_ids";
//print $sql . "\n";
$stmt = $mysqli->query($sql);
$numRows = $stmt->num_rows;
$count = 0;
$unique = array();
if ($numRows > 0 ){ 
  while ($row = $stmt->fetch_assoc()){
    $count ++;
    //print $count . "\n";
    //print_r($row);
    $curie = preg_replace('/^NLXWIKI:/','', $row['curie']);
//print $curie; 
    if (preg_match('/(T3D)([0-9]+)/',$curie, $m0)){
       $unique['T3D'] = $m0[2];
       //print_r($m0);
       //print ' category 1';
    }
    elseif (preg_match('/([a-zA-Z]+): *([0-9]+)/',$curie, $m1)){
       $word = strtoupper($m1[1]);
       $unique[$word] = $m1[2];
       //print_r($m1);
       //print ' category 1';
    }
    elseif (preg_match('/([a-zA-Z_]+)_([0-9]+)/',$curie, $m2)){
       $word = strtoupper($m2[1]);
       $unique[$word] = $m2[2];
       //print_r($m2);
       //print ' category 2';
    }
    elseif (preg_match('/([a-zA-Z]+) ([0-9]+)/',$curie, $m3)){
       $word = strtoupper($m3[1]);
       $unique[$word] = $m3[2];
       //print_r($m3);
       //print ' category 5';
    }
    elseif (preg_match('/([a-z-A-Z]+)-([0-9]+)/',$curie, $m4)){
       $word = strtoupper($m4[1]);
       $unique[$word] = $m4[2];
       //print_r($m4);
       //print ' category 3';
    }
    elseif (preg_match('/([a-zA-Z]+)([0-9]+)/',$curie, $m5)){
       $word = strtoupper($m5[1]);
       $unique[$word] = $m5[2];
       //print_r($m5);
       //print ' category 4';
    }
    else {
       //$unique[$curie] = 0;
       //print ' no category';
    }
//print "\n";
    //$parts = explode(":", $curie);

    //print $update . "\n";
    //$mysqli->query($update);
    //$stmt2->close();
  }
}
ksort($unique);
print_r($unique);
$stmt->close();
$mysqli->close();
exit;

