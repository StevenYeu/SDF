<?php

error_reporting(E_ALL);
ini_set("display_errors", 1);
$file = file_get_contents('http://beta.neuinfo.org/services/v1/federation/data/nlx_144509-1.xml?q=*&exportType=data&count=14000&apikey=458cb849-1287-5606-8d64-3a153ed9452a');
file_put_contents('registry.xml',$file);

?>