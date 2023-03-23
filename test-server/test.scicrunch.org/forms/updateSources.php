<?php

include '../classes/classes.php';
\helper\scicrunch_session_start();


set_time_limit(0);
$holder = new Sources();
$sources = $holder->getAllSources();


$dbImgThumbnailLocation = '/upload/source-images/';
if(isset($_SESSION["betaenvironment"])) {
    $url = BETAENVIRONMENT . "/v1/federation/search.xml?q=*";
    $service_host = "http://grefine.neuinfo.org:8080";
} else {
    $url = ENVIRONMENT . '/v1/federation/search.xml?q=*';
    $service_host = "http://cm.neuinfo.org:8080";
}
$xml = simplexml_load_file($url);

$done = array();
$view_categories = Array();

if ($xml) {
    foreach ($xml->result->results->result as $result) {
        $view_categories[] = Array("viewid" => (string) $result["nifId"], "category" => (string) $result["category"], "parentCategory" => (string) $result["parentCategory"]);
        if (!isset($done[(string)$result['nifId']])) {
            $vars = array();
            $vars['nif'] = (string)$result['nifId'];
            $vars['source'] = (string)$result['db'];
            $vars['view'] = (string)$result['indexable'];
            $vars['data'] = (int)$result->count;
            $vars["description_encoded"] = isset($sources[$vars["nif"]]) ? $sources[$vars["nif"]]->description_encoded : 0;
            $vars["description"] = isset($sources[$vars["nif"]]) ? $sources[$vars["nif"]]->description : "";
            $vars["image"] = isset($sources[$vars["nif"]]) ? $sources[$vars["nif"]]->image : "";

            $update[] = $vars;
            $urls[] = $service_host . '/cm_services/sources/summary?viewNifId=' . $vars['nif'];
            $done[$vars['nif']] = true;
        }
    }

    // update the view categories
    Sources::updateViewCategories($view_categories);

    $files = Connection::multi($urls);
    if (count($files) > 0) {
        foreach ($files as $i => $file) {
            $xml = simplexml_load_string($file);
            if ($xml) {
                foreach ($xml->views->view as $view) {
                    if ($view['nifId'] == $update[$i]['nif']) {
                        $descHolder = \helper\char2Entity(strip_tags((string)$view->description, '<a><img><i><br>'));
                    }
                }
                if(strpos($descHolder, "<img") === false) {
                    $newDesc = $descHolder;
                } else {
                    $splits = end(explode("<a", $descHolder));
                    $tutSplit = split('<img', $splits);
                    $counts = explode("<a", $descHolder);
                    $j = count($counts) - 1;
                    if (count($tutSplit) == 1) {
                        $split2 = explode("<a", $descHolder);
                        $j = count($split2) - 2;
                        $splits = $split2[$j + 1];
                    }
                    $thisTut = "<a" . $splits;
                    $newSplits = split('<a', $descHolder);
                    $newDesc = $newSplits[0];
                    for ($k = 1; $k < $j; $k++) {
                        $newDesc .= "<a" . $newSplits[$k];
                    }
                }
                if($newDesc) {
                    $update[$i]['description'] = $newDesc;
                }

                $dashSplit = explode('-', $update[$i]['nif']);
                $realId = join('-', array_slice($dashSplit, 0, count($dashSplit) - 1));

                $image_exts = Array("png", "PNG", "jpg", "gif");
                $found = false;
                foreach($image_exts as $iexts){
                    $fullname = $dbImgThumbnailLocation . $realId . '.' . $iexts;
                    if(file_exists($_SERVER["DOCUMENT_ROOT"] . $fullname)){
                        $update[$i]['image'] = $fullname;
                        $found = true;
                    }
                }
                if(!$found) $update[$i]['image'] = $realId;
                $update[$i]['active'] = 1;
            }
        }
    }
    $html = '';
    foreach ($update as $row) {
        if ($sources[$row['nif']]) {
            $sources[$row['nif']]->updateData($row);
            $sources[$row['nif']]->updateDB();
        } else {
            $source = new Sources();
            $source->create($row);
            $source->insertDB();
        }
    }

    $obsolete_sources = Array();
    foreach(array_keys($sources) as $s) {
        if(!isset($done[$s])) $obsolete_sources[] = $s;
    }

    // inactivate sources that no longer exist
    foreach($obsolete_sources as $os) {
        $sources[$os]->setActive(false);
    }
}

// getting the last updated time
$url = "http://disco.neuinfo.org/webportal/WebServices/REST/DISCOInfo/getNIFViewStatus";
$xml = simplexml_load_file($url);
if($xml){
    $nif_updated_times = Array();
    foreach($xml->view as $dsource){
        $prod_status = $dsource->{"production-status"};
        $nifid = (string) $dsource["nif-view-id"];
        $date = (string) $prod_status["date"];
        $int_date = NULL;
        if($date !== ""){
            $int_date = strtotime($date);
            if($int_date){
                $nif_updated_times[$nifid] = $int_date;
            }
        }
    }

    foreach($sources as $nif => $source){
        if(isset($nif_updated_times[$nif])){
            $source->data_last_updated = $nif_updated_times[$nif];
            $source->updateDB();
        }
    }
}

header('location:/account/scicrunch/sources');
?>
