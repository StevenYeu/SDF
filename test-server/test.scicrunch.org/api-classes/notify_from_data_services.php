<?php

function notifyFromDataServices($user, $api_key, $id, $message, $host){
    $approved_hosts = Array(
        "nif-apps1" => "http://nif-apps1.crbs.ucsd.edu/servicesv1",
        "nif-apps2" => "http://nif-apps2.crbs.ucsd.edu/servicesv1",
        "nif-services" => "http://nif-services.neuinfo.org/servicesv1"
    );
    $approved_id = "vbKaFJiiBcRzRh6K8";
    $statuses_file = $_SERVER["DOCUMENT_ROOT"] . "/vars/data_statuses.php";

    if($id !== $approved_id) return APIReturnData::build(false, true);
    if(!in_array($host, array_keys($approved_hosts))) return APIReturnData::build(NULL, false, 400, "unrecognized host");
    $host_env = $approved_hosts[$host];

    $statuses = unserialize(file_get_contents($statuses_file));
    if($message == "restart"){
        $other_running = false; // status if at least one other data service is up and running
        foreach($statuses as $env => $status){
            if($env == $host_env) continue;
            if($status['status'] == 'up'){
                $other_running = true;
                break;
            }
        }
        if($other_running){
            $statuses[$host_env]['status'] = "restart";
            $statuses[$host_env]['restart_time'] = time();
        }
        updateStatusesFile($statuses, $statuses_file);
        return APIReturnData::build($other_running, true);
    }elseif($message == "started"){
        $statuses[$host_env]["status"] = "up";
        updateStatusesFile($statuses, $statuses_file);
        return APIReturnData::build(true, true);
    }elseif($message == "stopped"){
        $statuses[$host_env]["status"] = "down";
        updateStatusesFile($statuses, $statuses_file);
        return APIReturnData::build(true, true);
    }
    return APIReturnData::build(false, true);
}

function updateStatusesFile($statuses, $statuses_file){
    file_put_contents($statuses_file, serialize($statuses));
}

?>
