<?php
$docroot = "..";
include_once $docroot . "/classes/classes.php";

$disco_json = file_get_contents('http://disco.neuinfo.org/webportal/WebServices/REST/DISCOInfo/getNIFViewStatusJson');
$sc_json = file_get_contents(ENVIRONMENT . '/v1/federation/search.json');

//convert to an associative array
$disco_data = json_decode($disco_json, true);
$sc_data = json_decode($sc_json, true);

$disco_id_stack = [];
$sc_id_stack = [];

// create a list of nifids from disco
foreach ($disco_data['view'] as $key => $value) {
    $disco_nif_id = $disco_data['view'][$key]['@nif-view-id'];
    array_push($disco_id_stack, $disco_nif_id);
}

$sc_arr = array();

$date_checked = time();

// loop through nifIDs in scicrunch data
foreach ($sc_data['result']['results'] as $key => $value) {
    $sc_nif_id = $value['nifId'];
    if(isset($sc_arr[$sc_nif_id])) continue;
    $num_records = $value['count'];

    $sc_summary_string = $value['summaryString'];

    array_push($sc_id_stack, $sc_nif_id);

    // check if data is in disco
    if(in_array($sc_nif_id, $disco_id_stack)) {
        $indisco = 1;
    } else {
        $indisco = 0;
    }

    $insc = 1;

    $sc_search_url = PROTOCOL . "://" . FQDN . "/scicrunch/data/source/" . $sc_nif_id . "/search?q=%2A&l=";
    for ($i = 0; $i < 10; $i++) {
        $sc_data_json = file_get_contents(ENVIRONMENT . "/v1/federation/data/" . $sc_nif_id . ".json");
        if($sc_data_json) break;
    }

    $sc_data_content = json_decode($sc_data_json, true);
    $check_data = $sc_data_content['result']['result'][0];

    // check if there's data
    if (!($check_data)) {
        $sc_has_data = 0;
    } else {
        $sc_has_data = 1;
    }

    /* check first row links */
    $sc_first_row = $sc_data_content['result']['result'][0];
    $imploded_first_row = "<div><div>" . implode("</div><div>", $sc_first_row) . "</div></div>";
    $dom_data = new DOMDocument();
    $dom_data->loadHTML($imploded_first_row);
    $sc_valid_link = 1;
    foreach($dom_data->getElementsByTagName("a") as $anchor) {
        if(trim($anchor->textContent) == "") {
            continue;
        }
        $href = $anchor->getAttribute("href");
        if($anchor->nodeValue && !checkURLStatus($href)) {
            $sc_valid_link = 0;
            break;
        }
    }

    // check if scicrunch url is valid
    $sc_page_loads = checkURLStatus($sc_search_url);

    // contains at least three letters
    $regex_three_letters = "/[a-zA-Z]{3}/";

    $content = file_get_contents($sc_search_url);

    $dom_site = new DOMDocument();
    $dom_site->loadHTML($content);

    $title_element = $dom_site->getElementByID("sc-title");
    if(is_null($title_element) || strlen(strip_tags($title_element->nodeValue)) < 3) {
        $sc_title = 0;
    } else {
        $sc_title = 1;
    }

    $descr_element = $dom_site->getElementByID("sc-descr");
    if(is_null($descr_element) || strlen(strip_tags($descr_element->nodeValue)) < 3) {
        $sc_descr = 0;
        $sc_descr_link = 0;
    } else {
        $sc_descr = 1;
        $sc_descr_link = 1;
        foreach($descr_element->getElementsByTagName("a") as $anchor) {
            if(trim($anchor->textContent) == "") {
                continue;
            }
            $href = $anchor->getAttribute("href");
            if($anchor->nodeValue && !checkURLStatus($href)) {
                $sc_descr_link = 0;
                break;
            }
        }
    }

    $sc_arr_obj = array(
        "date_checked" => $date_checked,
        "insc" => $insc,
        "indisco" => $indisco,
        "num_records" => $num_records,
        "sc_page_loads" => $sc_page_loads,
        "sc_title" => $sc_title,
        "sc_descr" => $sc_descr,
        "sc_has_data" => $sc_has_data,
        "sc_valid_link" => $sc_valid_link,
        "sc_descr_link" => $sc_descr_link
    );

    $sc_arr[$sc_nif_id] = $sc_arr_obj;
}


$disco_arr = array();

foreach ($disco_data['view'] as $key => $value) {

    $disco_nif_id = $value['@nif-view-id'];
    $production_date = strtotime($value['production-status']['@date']);
    $curr_date = strtotime($value['lifecycle-status']['@date']);
    $curr_status = $value['lifecycle-status']['@status'];
    $record_count = $value['production-status']['@records'];


    array_push($disco_id_stack, $disco_nif_id);

    // check if data is in scicrunch
    if(in_array($disco_nif_id, $sc_id_stack)) {
        $insc = 1;
    } else {
        $insc = 0;
    }

    $indisco = 1;


    $disco_arr_obj = array(
        "date_checked" => $date_checked,
        "production_date" => $production_date,
        "curr_date" => $curr_date,
        "curr_status" => $curr_status,
        "insc" => $insc,
        "indisco" => $indisco,
        "record_count" => $record_count
    );

    $disco_arr[$disco_nif_id] = $disco_arr_obj;

}

$union_sc_disco = array();

foreach($sc_arr as $nif_id_1 => $value1) {
    if (array_key_exists($nif_id_1, $disco_arr)) {
        $union_sc_disco[$nif_id_1] = array_merge($value1, $disco_arr[$nif_id_1]);
    } else {
        $union_sc_disco[$nif_id_1] = $value1;
    }
}

foreach($disco_arr as $nif_id_2 => $value2) {
    if (!array_key_exists($nif_id_2, $sc_arr)) {
        $union_sc_disco[$nif_id_2] = $value2;
    }
}

var_dump($union_sc_disco);

$cxn = new Connection();
$cxn->connect();

foreach($union_sc_disco as $key => $value) {
    $cxn->insert("viewstatuses", "isiiisiiiiiiiiii", Array(null, $key, $value['date_checked'], $value['production_date'], $value['curr_date'], $value['curr_status'], $value['insc'], $value['indisco'], $value['num_records'], $value['sc_page_loads'], $value['sc_title'], $value['sc_descr'], $value['sc_has_data'], $value['sc_valid_link'], $value['sc_descr_link'], $value['record_count']));
}

$cxn->close();

function checkURLStatus($url) {
    $url = trim($url);
    $url = str_replace(" ", "%20", $url);
    if(!$url) return true;  // just return true for empty urls
    if(\helper\startsWith($url, "ftp")) return true;    // assume ftp is true
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_HTTPHEADER, Array("User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/54.0."));
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    $result = curl_exec($ch);
    $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if(($status_code >= 200 && $status_code <= 299) || $status_code == 302) return true;
    return false;
}

?>
