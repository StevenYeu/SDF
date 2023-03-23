<?php

$FILE = "./neurolex_properties.txt";
$ONTURL = "https://raw.githubusercontent.com/tgbugs/nlxeol/master/neurolex_full.csv";

$annotation_properties = array();
$object_properties = array();

if (($handle = fopen($FILE, "r")) !== FALSE) {
    while (($data = fgetcsv($handle, 1000, "\t")) !== FALSE) {
       if (strlen(trim($data[2])) < 1){ continue; }

       $type = trim($data[2]);
       if ($type == "annotation property"){
           $annotation_properties[] = array('property'=>$data[0],'term'=>$data[1]);
       }
       if ($type == 'object property'){
           $object_properties[] = array('property'=>$data[0],'term'=>$data[1]);
       }
    }
    fclose($handle);
}

print_r($annotation_properties);
print_r($object_properties);

?>
