<?php

function getScigraphResponse($user, $api_key, $path) {
    if(!\APIPermissionActions\checkAction("scigraph-wrapper", $api_key, $user)) return APIReturnData::quick403();
    $url = APIURL . "/scigraph/" . $path;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_VERBOSE, 1);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    //curl_setopt($ch, CURLOPT_HTTPHEADER, Array(
    //    "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8"
    //));
    curl_setopt($ch, CURLOPT_HEADER, 1);
    $curl_response = curl_exec($ch);
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    $header = substr($curl_response, 0, $header_size);
    $body = substr($curl_response, $header_size);
    $parsed_header = parseHeader($header);

    return Array("header" => $parsed_header, "body" => $body);
}

function parseHeader($header) {
    $headers = Array();
    $header_text_split = explode("\r\n\r\n", $header);
    if(count($header_text_split) < 2) return $headers;
    $header_split = explode("\r\n", $header_text_split[count($header_text_split) - 2]);

    foreach($header_split as $i => $line) {
        if($i === 0) {
            $split = explode(" ", $line);
            $headers["http_code"] = (int) $split[1];
        } else {
            $vals = explode(":", $line);
            $headers[$vals[0]] = $vals[1];
        }
    }

    return $headers;
}

?>
