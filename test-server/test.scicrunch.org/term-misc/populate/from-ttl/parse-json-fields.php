<?php

$file = "./neurolex_basic_pages.json";
$json = file_get_contents($file);
$data = json_decode($json, true);

$unique = array();
foreach ($data['pages'] as $p){
   //print_r($p);
   foreach ($p as $k=>$v){
      //print "key: \n";
      //print "value: \n";
      //print_r($v);
      foreach($v as $field=>$value){
        $unique[trim($field)] = 1;
      }
   }
}

//print_r(array_keys($unique));
$keys = array_keys($unique);
sort($keys);
//print_r($keys);

foreach( $keys as $k){
   echo $k . "\n";
}
?>
