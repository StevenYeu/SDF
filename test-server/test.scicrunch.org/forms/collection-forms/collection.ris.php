<?php

include '../../classes/classes.php';
\helper\scicrunch_session_start();

$pmids_per_web_request = 10;

$id = $_GET["collection"];
if(!isset($_SESSION["user"]) || !$_SESSION["user"]->collections[$id]) {
    exit;
}

$collection = $_SESSION["user"]->collections[$id];

header('Content-Type: application/x-research-info-systems');
header('Content-Disposition: attachment; filename='.str_replace(' ','_',$collection->name).'.ris');
header('Pragma: no-cache');

$output = fopen("php://output", "w");

$holder = new Item();
$items = $holder->getByCollection($collection->id, $_SESSION["user"]->id);
$pmid_arrays = Array(Array());

foreach($items as $item) {
    if($item->view != "literature") continue;
    $last_idx = count($pmid_arrays) - 1;
    if(count($pmid_arrays[$last_idx]) >= $pmids_per_web_request) {
        $pmid_arrays[] = Array();
        $last_idx += 1;
    }
    $pmid_arrays[$last_idx][] = $item->uuid;
}

$urls = Array();
foreach($pmid_arrays as $pmid_array) {
    $url = Connection::environment() . "/v1/literature/pmid.ris?";
    foreach($pmid_array as $i => $pmid) {
        if($i != 0) $url .= "&";
        $url .= "pmid=" . $pmid;
    }
    $urls[] = $url;
}
$results = Connection::multi($urls);

foreach($results as $res) {
    fputs($output, $res);
}

fclose($output);

?>
