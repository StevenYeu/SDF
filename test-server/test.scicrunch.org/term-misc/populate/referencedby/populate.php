<?php 
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';

$mysqli = new mysqli($config['mysql-hostname'], $config['mysql-username'], $config['mysql-password'], $config['mysql-database-name']) or die("Error: cannot connect to the database - ". $mysqli->connect_error);

//$sql1 = "select count(*) as count from _term_mapping_dump";
//$stmt1 = $mysqli->query($sql1);
//$row1 = $stmt1->fetch_assoc();
//$count = $row1['count'];
//$stmt1->close();

//for ($i=1; $i<$count+1; $i++) {
  //if ($i == 10) { exit; };

  //$sql = "select * from _term_mapping_dump where id =" . $i;
  $sql = "select * from _term_mapping_dump where existing_id is not null";
  //print $sql . "\n";
  $stmt = $mysqli->query($sql);
  $numRows = $stmt->num_rows;
  if ($numRows > 0 ){ 
    while ($row = $stmt->fetch_assoc()){
      //print_r($rows);
      $tid = 0;
      if ( strlen($row['existing_id']) > 0 ) {
        $sql2 = "select tid from term_existing_ids where curie = '" . $row['existing_id'] . "'";
        $stmt2 = $mysqli->query($sql2);
        $row2 = $stmt2->fetch_assoc();
        $tid = $row2['tid'];
        $stmt2->close();
      }
      $matched_val = $row['matched_val'];
      if ($row['matched_val'] == 'null') {
        $matched_val = $row['val'];
      }
      $insert = "insert into term_mappings
        (tid,existing_id,source_id,source,value,matched_value,is_ambiguous,is_whole,method,curation_status,view_name,column_name,view_id,concept,concept_id,iri,uid) 
        values (" . 
          $tid .
          ", '" . $row['existing_id'] .
          "', '" . $row['source_nif_id'] .
          "', '" . $row['source_name'] .
          "', '" . $row['val'] .
          "', '" . $matched_val .
          "', '" . $row['is_ambiguous'] .
          "', '" . $row['is_whole'] .
          "', 'semi-automated'" .
          ", 'matched'" . 
          ", '" . $row['rel_name'] .
          "', '" . $row['column_name'] .
          "', '" . $row['rel_nif_id'] .
          "', '" . $row['concept_name'] .
          "', '" . $row['concept_id'] .
          "', '" . $row['iri'] .
          "', 0" . 
          ")";
      //print $i . ") " . $insert . "\n";
      $mysqli->real_escape_string($insert);
      $mysqli->query($insert);

      $insert2 = "insert into term_mapping_logs (tmid, uid, notes, curation_status, time) values (" . $mysqli->insert_id . ", 0, 'Initial upload', 'matched', " . time() . ")";
      //print $insert2 . "\n";
      $mysqli->query($insert2);
    }
  }
  $stmt->close();
//}
$mysqli->close();
exit;

