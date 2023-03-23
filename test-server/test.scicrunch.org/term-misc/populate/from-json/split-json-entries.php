<?php

$files = array(
                "./data/neurolex0.json",
                "./data/neurolex1.json",
                "./data/neurolex2.json",
                "./data/neurolex3.json",
                "./data/neurolex4.json",
                "./data/neurolex5.json",
                "./data/neurolex6.json",
                "./data/neurolex7.json",
                "./data/neurolex8.json",
                "./data/neurolex9.json",
                "./data/neurolex10.json",
                "./data/neurolex11.json",
                "./data/neurolex12.json",
                "./data/neurolex13.json",
              );

$dir = "./data/files/";
foreach($files as $file){
   print "reading file: " . $file . "\n";
   $json = file_get_contents($file);
   $data = json_decode($json, true);

   foreach ($data['pages'] as $p){
      foreach ($p as $k=>$v){
         if (!array_key_exists("Id",$v)){
            print "'" . $k . "' doesn't have ID\n";
            continue;
         }
         if (!preg_match("/[\w_:]/",$v['Id'])) {
            print "'" . $v['Id'] . "' NOT ALPHA\n";
         }
         //$filename = $dir . str_replace(":","__", trim($v['Id']));
         $filename = $dir . trim($v['Id']) . ".json";
         //print $filename . "\n";
         file_put_contents($filename, json_encode($p));
      }
   }
}

?>
