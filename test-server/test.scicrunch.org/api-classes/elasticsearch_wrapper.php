<?php

require_once __DIR__ . "/nifservices_wrapper.php";

function elasticRequest($url, $http_login, $method, $get_params, $post_string) {
    if(!is_null($get_params) && !empty($get_params)) {
        $encoded_get_params = Array();
        foreach($get_params as $key => $val) {
            $encoded_get_params[urlencode($key)] = urlencode($val);
        }
        $url .= "?" . http_build_query($encoded_get_params);
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    if($http_login) {
        curl_setopt($ch, CURLOPT_USERPWD, $http_login);
    }
    curl_setopt($ch, CURLOPT_HEADER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, Array(
        "Content-Type: application/json",
    ));
    if($post_string) {
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
    }
    if($method == "GET") {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    } elseif($method == "POST") {
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
    }
    $curl_response = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $header = substr($curl_response, 0, $header_size);
    $body = substr($curl_response, $header_size);
    $parsed_header = parseHeader($header);

    $success = $status_code >= 200 && $status_code <= 299;

    return Array(
        "body" => $body,
        "header" => $parsed_header,
        "status-code" => $status_code,
        "success" => $success,
    );
}

function indexOverview($user, $api_key, $index, $method, $get_params, $post_string, $elastic_type="normal") {
    if($elastic_type == "ilx") {
        $url = $GLOBALS["config"]["elasticsearch"]["protocol"] . "://" . $GLOBALS["config"]["elasticsearch"]["host"];
        $http_login = NULL;
    } else {
        $ec = $GLOBALS["config"]["elastic-search"]["api"];
        $url = $ec["base-url"];
        $http_login = $ec["user"] . ":" . $ec["password"];
    }
    $url .= "/" . urlencode($index) ;

    $res = elasticRequest($url, $http_login, $method, $get_params, $post_string);

    return APIReturnData::build($res, $res["success"], $res["status-code"]);
}

function indexSearch($user, $api_key, $index, $action, $method, $get_params, $post_string, $elastic_type="normal") {
    if($elastic_type == "ilx") {
        $url = $GLOBALS["config"]["elasticsearch"]["protocol"] . "://" . $GLOBALS["config"]["elasticsearch"]["host"];
        $http_login = NULL;
    } else {
        $ec = $GLOBALS["config"]["elastic-search"]["api"];
        $url = $ec["base-url"];
        $http_login = $ec["user"] . ":" . $ec["password"];
    }
    $url .= "/" . urlencode($index) . "/" . $action;

    $res = elasticRequest($url, $http_login, $method, $get_params, $post_string);

    return APIReturnData::build($res, $res["success"], $res["status-code"]);
}

function typeSearch($user, $api_key, $index, $type, $action, $method, $get_params, $post_string, $elastic_type="normal") {
    if($elastic_type == "ilx") {
        $url = $GLOBALS["config"]["elasticsearch"]["protocol"] . "://" . $GLOBALS["config"]["elasticsearch"]["host"];
        $http_login = NULL;
    } else {
        $ec = $GLOBALS["config"]["elastic-search"]["api"];
        $url = $ec["base-url"];
        $http_login = $ec["user"] . ":" . $ec["password"];
    }
    $url .= "/" . urlencode($index) . "/" . urlencode($type) . "/" . $action;

    $res = elasticRequest($url, $http_login, $method, $get_params, $post_string);

    return APIReturnData::build($res, $res["success"], $res["status-code"]);
}

function allSearch($user, $api_key, $action, $method, $get_params, $post_string, $elastic_type="normal") {
    if($elastic_type == "ilx") {
        $url = $GLOBALS["config"]["elasticsearch"]["protocol"] . "://" . $GLOBALS["config"]["elasticsearch"]["host"];
        $http_login = NULL;
    } else {
        $ec = $GLOBALS["config"]["elastic-search"]["api"];
        $url = $ec["base-url"];
        $http_login = $ec["user"] . ":" . $ec["password"];
    }
    $url .= "/" . $action;

    $res = elasticRequest($url, $http_login, $method, $get_params, $post_string);

    return APIReturnData::build($res, $res["success"], $res["status-code"]);
}

function getIndices($user, $api_key, $method, $elastic_type="normal") {
    if($elastic_type == "ilx") {
        $url = $GLOBALS["config"]["elasticsearch"]["protocol"] . "://" . $GLOBALS["config"]["elasticsearch"]["host"];
        $http_login = NULL;
    } else {
        $ec = $GLOBALS["config"]["elastic-search"]["api"];
        $url = $ec["base-url"];
        $http_login = $ec["user"] . ":" . $ec["password"];
    }
    $url .= "/_cat/indices";

    $res = elasticRequest($url, $http_login, $method, NULL, NULL);
    if(!$res["success"]) return APIReturnData::quick400("could not return data");

    $data = Array();
    $body = $res["body"];
    $body_split = explode("\n", $body);
    foreach($body_split as $bs) {
        if(!$bs) continue;
        $bs_split = preg_split("/[ ]+/", $bs);
        if(count($bs_split) < 10) continue;
        if(\helper\startsWith($bs_split[2], ".")) continue;
        $data[] = Array(
            "health" => $bs_split[0],
            "status" => $bs_split[1],
            "index" => $bs_split[2],
            "docs.count" => $bs_split[6],
            "pri.store.size" => $bs_split[9],
        );
    }

    return APIReturnData::build($data, true);
}

?>
