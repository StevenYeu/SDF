<?php

// settings
$docroot = "..";
$statuses_file =  $docroot . "/vars/data_statuses.php";
$localbaseurl = "https://localhost/uptime/test-data-services.php?host=";
$restart_time_limit = 180; // give three minutes for restarting

$data_services = Array(
    Array(
        "env" => "http://skaa.crbs.ucsd.edu:8080/services",
        "localurl" => "skaa.crbs.ucsd.edu:8080/services"
    ),
    Array(
        "env" => "http://skab.crbs.ucsd.edu:8080/services",
        "localurl" => "skab.crbs.ucsd.edu:8080/services"
    ),
);


if(file_exists($statuses_file)){
    $statuses = unserialize(file_get_contents($statuses_file));
}
if(!is_array($statuses)) $statuses = Array();

foreach($data_services as $service){
    $env = $service['env'];
    if(isset($statuses[$env]) && $statuses[$env]['status'] == "restart"){
        $current_time = time();
        $expire_time = $statuses[$env]['restart_time'] + $restart_time_limit;
        if($expire_time > $current_time) continue;
    }
    $url = $localbaseurl . $service['localurl'];
    if(checkLocalService($url)){
        $statuses[$env]['status'] = "up";
    }else{
        $statuses[$env]['status'] = "down";
    }
}

file_put_contents($statuses_file, serialize($statuses));

function checkLocalService($url){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $result = curl_exec($ch);
    curl_close($ch);

    if($result){
        if(strpos($result, '"status":"pass"') !== false){
            return true;
        }
    }
    return false;
}

?>
