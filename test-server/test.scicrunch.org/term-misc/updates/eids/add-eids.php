<?php 
error_reporting(E_ERROR);
$docroot = "../..";
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
//print_r($curie_catalog);

$sql = "select t.ilx, tei.tid, tei.curie, tei.iri from term_existing_ids tei, terms t where t.id = tei.tid";
//print $sql . "\n";
$stmt = $mysqli->query($sql);
$numRows = $stmt->num_rows;
$count = 0;
$unique = array();
$odds = array();
if ($numRows > 0 ){ 
  while ($row = $stmt->fetch_assoc()){
    $count ++;
    //if ( $count > 1 ) { exit; }
    //print $count . "\n";
    //print_r($row);
    $curie = preg_replace('/^NLXWIKI:/','', $row['curie']);
    $curie = trim($curie);

    if (preg_match('/nif-0000-03485/',$curie, $m)){
      continue;
    }

    if (preg_match('/T3D([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['t3d']['namespace']. $m[1];
       $curie_catalog_id = $curie_catalog['t3d']['catalog_id'];
       $unique['t3d'][] = array('ilx'=> $row['ilx'], 'curie'=>'T3D:'.$m[1], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/sao-*(.+)/',$curie, $m)){
//https://github.com/tgbugs/nlxeol/blob/master/total_curie_fragment.json
       // skip for now
      $odds[] = array('ilx'=>$row['ilx'], 'tid'=>$row['tid']);
    }
    elseif (preg_match('/(PR_)([0-9]+)/',$curie, $m) or preg_match('/(PRO:)([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['pr']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['pr']['catalog_id'];
       $unique['pr'][] = array('ilx'=> $row['ilx'], 'curie'=>'PR:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(topic)_([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['topic']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['topic']['catalog_id'];
       $unique['topic'][] = array('ilx'=> $row['ilx'], 'curie'=>'TOPIC:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/PAR:([0-9]+)/',$curie, $m)){
      // skip for now
      $odds[] = array('ilx'=>$row['ilx'], 'tid'=>$row['tid']);
    }
    elseif (preg_match('/IMR_([0-9]+)/',$curie, $m)){
      // skip for now
      $odds[] = array('ilx'=>$row['ilx'], 'tid'=>$row['tid']);
    }
    elseif (preg_match('/Tri([0-9]+)/',$curie, $m)){
      // skip for now
      $odds[] = array('ilx'=>$row['ilx'], 'tid'=>$row['tid']);
    }
    elseif (preg_match('/(FMA[_:])([0-9]+)/',$curie, $m) or preg_match('/(FMAID:) *([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['fma']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['fma']['catalog_id'];
       $unique['fma'][] = array('ilx'=> $row['ilx'], 'curie'=>'FMA:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(MA:)([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['ma']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['ma']['catalog_id'];
       $unique['ma'][] = array('ilx'=> $row['ilx'], 'curie'=>'MA:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(obi:IAO_)([0-9]+)/',$curie, $m) or preg_match('/(IAO_)([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['iao']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['iao']['catalog_id'];
       $unique['iao'][] = array('ilx'=> $row['ilx'], 'curie'=>'IAO:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(GO_)([0-9]+)/',$curie, $m) or preg_match('/(GO:)([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['go']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['go']['catalog_id'];
       $unique['go'][] = array('ilx'=> $row['ilx'], 'curie'=>'GO:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(Nlx_)([0-9]+)/',$curie, $m) or preg_match('/(nlx_)([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['nlx']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['nlx']['catalog_id'];
       $unique['nlx'][] = array('ilx'=> $row['ilx'], 'curie'=>'NLX:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(OBI[_:])([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['obi']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['obi']['catalog_id'];
       $unique['obi'][] = array('ilx'=> $row['ilx'], 'curie'=>'OBI:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(UBERON[_:])([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['uberon']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['uberon']['catalog_id'];
       $unique['uberon'][] = array('ilx'=> $row['ilx'], 'curie'=>'UBERON:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(SIO[_:])([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['sio']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['sio']['catalog_id'];
       $unique['sio'][] = array('ilx'=> $row['ilx'], 'curie'=>'SIO:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(CHEBI[_:])([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['chebi']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['chebi']['catalog_id'];
       $unique['chebi'][] = array('ilx'=> $row['ilx'], 'curie'=>'CHEBI:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(DOID[_:])([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['doid']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['doid']['catalog_id'];
       $unique['doid'][] = array('ilx'=> $row['ilx'], 'curie'=>'DOID:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(ERO)[_:]([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['ero']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['ero']['catalog_id'];
       $unique['ero'][] = array('ilx'=> $row['ilx'], 'curie'=>'ERO:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(oen[_:])([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['oen']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['oen']['catalog_id'];
       $unique['oen'][] = array('ilx'=> $row['ilx'], 'curie'=>'OEN:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(PATO[_: ])([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['pato']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['pato']['catalog_id'];
       $unique['pato'][] = array('ilx'=> $row['ilx'], 'curie'=>'PATO:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(SO)_([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['so']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['so']['catalog_id'];
       $unique['so'][] = array('ilx'=> $row['ilx'], 'curie'=>'SO:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(NEMO)_([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['nemo']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['nemo']['catalog_id'];
       $unique['nemo'][] = array('ilx'=> $row['ilx'], 'curie'=>'NEMO:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(CAO)_([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['cao']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['cao']['catalog_id'];
       $unique['cao'][] = array('ilx'=> $row['ilx'], 'curie'=>'CAO:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(birnlex)_([0-9]+)/i',$curie, $m)){
       $iri = $curie_catalog['birnlex']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['birnlex']['catalog_id'];
       $unique['birnlex'][] = array('ilx'=> $row['ilx'], 'curie'=>'BIRNLEX:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(nifext)_([0-9]+)/i',$curie, $m)){
       $iri = $curie_catalog['nifext']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['nifext']['catalog_id'];
       $unique['nifext'][] = array('ilx'=> $row['ilx'], 'curie'=>'NIFEXT:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(obo:OGMS_)([0-9]+)/',$curie, $m) or preg_match('/(OGMS_)([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['ogms']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['ogms']['catalog_id'];
       $unique['ogms'][] = array('ilx'=> $row['ilx'], 'curie'=>'OGMS:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(efo:EFO_)([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['efo']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['efo']['catalog_id'];
       $unique['efo'][] = array('ilx'=> $row['ilx'], 'curie'=>'EFO:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/(Taxonomy ID:) ([0-9]+)/',$curie, $m) or preg_match('/(NCBITaxonID:)([0-9]+)/',$curie, $m) or preg_match('/(NCBITaxon:) *([0-9]+)/',$curie, $m)){
       $iri = $curie_catalog['ncbitaxon']['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog['ncbitaxon']['catalog_id'];
       $unique['ncbitaxon'][] = array('ilx'=> $row['ilx'], 'curie'=>'NCBITaxon:'.$m[2], 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/([a-zA-Z_]+)_([0-9]+)/',$curie, $m)){
       if (strpos($curie, 'nlx_anat_') !== false) {
       }
       elseif (strpos($curie, 'nlx_br_') !== false) {
       }
       elseif (strpos($curie, 'nlx_cell_') !== false) {
       }
       elseif (strpos($curie, 'nlx_subcell_') !== false) {
       }
       elseif (strpos($curie, 'nlx_chem_') !== false) {
       }
       elseif (strpos($curie, 'nlx_dys_') !== false) {
       }
       elseif (strpos($curie, 'nlx_func_') !== false) {
       }
       elseif (strpos($curie, 'nlx_inv_') !== false) {
       }
       elseif (strpos($curie, 'nlx_mol_') !== false) {
       }
       elseif (strpos($curie, 'nlx_organ_') !== false) {
       }
       elseif (strpos($curie, 'nlx_res_') !== false) {
       }
       elseif (strpos($curie, 'nlx_sub_') !== false) {
       }
       elseif (strpos($curie, 'nlx_qual_') !== false) {
       }
       $frag = $iri = $curie_catalog[strtolower($m[1])]['prefix'] . ":" . $m[2];
       $iri = $curie_catalog[strtolower($m[1])]['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog[strtolower($m[1])]['catalog_id'];
       $unique[strtolower($m[1])][] = array('ilx'=> $row['ilx'], 'curie'=>$frag, 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
// BAMSC, C, D, DB, sao, T3D, and Tri. Tri and sao taken care of
    elseif (preg_match('/([a-zA-Z]+)([0-9]+)/',$curie, $m)){
       $frag = $iri = $curie_catalog[strtolower($m[1])]['prefix'] . ":" . $m[2];
       $iri = $curie_catalog[strtolower($m[1])]['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog[strtolower($m[1])]['catalog_id'];
       $unique[strtolower($m[1])][] = array('ilx'=> $row['ilx'], 'curie'=>$frag, 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    elseif (preg_match('/([a-zA-Z]+):([0-9]+)/',$curie, $m)){
       $frag = $iri = $curie_catalog[strtolower($m[1])]['prefix'] . ":" . $m[2];
       $iri = $curie_catalog[strtolower($m[1])]['namespace']. $m[2];
       $curie_catalog_id = $curie_catalog[strtolower($m[1])]['catalog_id'];
       $unique[strtolower($m[1])][] = array('ilx'=> $row['ilx'], 'curie'=>$frag, 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
    }
    else {
      // skip for now
      $odds[] = array('ilx'=>$row['ilx'], 'tid'=>$row['tid']);
/*
       if (strlen(trim($curie)) > 0){
         $iri = 'http://uri.neuinfo.org/nif/nifstd/' . $curie;
         $curie_catalog_id = 1;
         $unique['odds'][] = array('curie'=>$curie, 'tid'=> $row['tid'], 'iri'=>$iri, 'curie_catalog_id'=>$curie_catalog_id);
       }
*/
    }

  }
}
$stmt->close();

foreach ($odds as $odd){
  preg_match('/ilx_([0-9]+)/',$odd['ilx'], $m);
  $ilx = $m[1];
  $curie = 'ILX:' . $ilx;
  $iri = $curie_catalog['ilx']['namespace'] . $ilx;
  $catalog_id = $curie_catalog['ilx']['catalog_id'];
  $insert = "insert into term_existing_ids " .
            "(tid, curie, iri, curie_catalog_id, preferred, version, time)" .
            "values " .
            "(".$odd['tid'].",'".$curie."','".$iri."',".$catalog_id.",'1',1,".time().")";
  //print $insert . "\n";
  $result = $mysqli->query($insert);
  if (!$result) {
     printf("%s\n", $mysqli->error);
     exit();
  }
}

ksort($unique);
foreach ($unique as $k => $v){
  //print $v[0]['ilx'] . "\t" . $v[0]['curie'] . "\t\t\t" . $v[0]['iri'] . "\t\t" . $v[0]['tid'] . "\t\t" . $v[0]['curie_catalog_id'] . "\n";
  //print count($v) . "\t" . $k . "\n";
  //print $k . "\n";
  //print_r($v);
  //$insert = "insert into term_existing_ids " .
  //          "(tid, curie, iri, curie_catalog_id, preferred, version, time)" .
  //          "values " .
  //          "(".$v[0]['tid'].",".$v[0]['curie'].",".$v[0]['iri'].",".$v[0]['curie_catalog_id'].",1,1,".time().")";
  //$insert = $mysqli->escape_string($insert);
  //print $insert . "\n";
  $count = 0;
  foreach ($v as $record) {
    $count++;
    //if ($count > 1) { continue; }

    $insert1 = "insert into term_existing_ids " .
            "(tid, curie, iri, curie_catalog_id, preferred, version, time)" .
            "values " .
            "(".$record['tid'].",'".$record['curie']."','".$record['iri']."',".$record['curie_catalog_id'].",'1',1,".time().")";
    //$insert1 = $mysqli->escape_string($insert1);
    //print $insert1 . "\n";
    $result = $mysqli->query($insert1);
    if (!$result) {
       printf("%s\n", $mysqli->error);
       exit();
    }

    preg_match('/ilx_([0-9]+)/',$record['ilx'], $m);
    $ilx = $m[1];
    $curie = 'ILX:' . $ilx;
    $iri = $curie_catalog['ilx']['namespace'] . $ilx;
    $catalog_id = $curie_catalog['ilx']['catalog_id'];
    $insert2 = "insert into term_existing_ids " .
            "(tid, curie, iri, curie_catalog_id, preferred, version, time)" .
            "values " .
            "(".$record['tid'].",'".$curie."','".$iri."',".$catalog_id.",'0',1,".time().")";
    //$insert2 = $mysqli->escape_string($insert2);
    //print $insert2 . "\n";
    $result = $mysqli->query($insert2);
    if (!$result) {
       printf("%s\n", $mysqli->error);
       exit();
    }
  }
}
$mysqli->close();

