<?php

include '../../classes/classes.php';
\helper\scicrunch_session_start();

$id = filter_var($_GET['collection'],FILTER_SANITIZE_NUMBER_INT);
if(!isset($_SESSION['user']) || !$_SESSION['user']->collections[$id]){
    exit();
}

$holder = new Sources();
$sources = $holder->getAllSources();
$collection = $_SESSION['user']->collections[$id];

header('Content-Type: application/csv');
header('Content-Disposition: attachment; filename='.str_replace(' ','_',$collection->name).'.csv');
header('Pragma: no-cache');

$output = fopen("php://output", "w");

$snippet = new Snippet();
$header_row = Array('title','description','citation','url','community','view id','source name','uuid');
fputcsv($output, $header_row);

$holder = new Item();
$items = $holder->getByCollection($collection->id, $_SESSION['user']->id);

foreach($items as $item){
    $xml = simplexml_load_string($item->snippet);
    if(!$xml) continue;

    $community = new Community();
    $community->getByID($item->community);
    $community_name = str_replace('"','""',$community->name);

    if($item->view == "literature") {
        $row = Array($xml->title, $xml->abstract, "PMID:" . $item->uuid, "http://www.ncbi.nlm.nih.gov/pubmed/" . $item->uuid, $community_name, $item->view, "", "PMID:" . $item->uuid);
        fputcsv($output, $row);
    } elseif($item->view == "view") {
        $row = Array($xml->title, $xml->description, "", $community->fullURL() . "/" . $community->portalName . "/data/source/" . $item->uuid . "/search", $community_name, $item->view, "", $item->uuid);
        fputcsv($output, $row);
    } else {
        if(!isset($sources[$item->view])) continue;
        $row = Array(
            fmtData($xml->title),
            fmtData($xml->description),
            fmtData($xml->citation),
            fmtData($xml->url),
            $community_name,
            $item->view,
            $sources[$item->view]->getTitle(),
            $item->uuid
        );
        fputcsv($output, $row);
    }
}

fclose($output);

function fmtData($data) {
    $new_data = str_replace("<br/>", " | ", $data);
    $new_data = strip_tags($new_data);
    $new_data = str_replace("\n", " | ", $new_data);
    $new_data = str_replace('"', '""', $new_data);
    return $new_data;
}

?>
