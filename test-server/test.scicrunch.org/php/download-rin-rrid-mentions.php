<?php

include "../classes/classes.php";
\helper\scicrunch_session_start();

function previousPage() {
    $previous_page = $_SERVER["HTTP_REFERER"];
    header("location:" . $previous_page);
    exit;
}

$viewid = $_GET["viewid"];
$rrid = $_GET["rrid"];
$rrids = explode(",", $rrid);   ## download co-mentions, multiple rrids -- Vicky-2019-3-15
$publicationYear = $_GET["year"];
$place = $_GET["place"];
$city = $_GET["city"];
$region = $_GET["region"];
$mode = $_GET["mode"];
$tab = $_GET["tab"];
$om_rrids = $_GET["om_rrids"];

$search_manager = ElasticRRIDManager::managerByViewID($viewid);
if(is_null($search_manager)) {
    previousPage();
}

$results = $search_manager->searchRRID(str_replace("RRID:", "", $rrids[0]));    ## download co-mentions, multiple rrids -- Vicky-2019-3-15
$record = $results->getByIndex(0);
if(is_null($record)) {
    previousPage();
}

if($tab == "organization-mentions") $mentions = \search\searchOrganizationMentions($viewid, $om_rrids, 0, 1000);
else $mentions = \search\searchSingleItemMentions($viewid, $rrid, $publicationYear, $place, $city, $region, $mode, 0, 1000);

header("Content-Type: text/csv");
if ($_GET["cn"] == "") header("Content-Disposition: attachment; filename=" . str_replace(",", " ", $record->getRRIDField("name")) . "(" . str_replace("RRID:", "", $rrid) . ")" . ".csv");
else header("Content-Disposition: attachment; filename=" . str_replace(",", " ", $record->getRRIDField("name")) . "(" . str_replace("RRID:", "", $rrids[0]) . ")" . "_" . str_replace(",", " ", $_GET["cn"]) . "(" . str_replace("RRID:", "", $rrids[1]) . ")" . ".csv");
$output = fopen("php://output", "w");
$header_row = Array("Publication ID", "Title", "First author", "Publication Year", "Journal", "Article link");
fputcsv($output, $header_row);

foreach($mentions["hits"]["hits"] as $hit) {
    $pub = $hit["_source"];
    $pmid_split = explode(":", $pub["pmid"]);
    $pub_id = $pub["pmid"];
    $title = $pub["dc"]["title"];
    $pub_year = $pub["dc"]["publicationYear"];
    $pub_journal= $pub["dc"]["publishers"][0]["name"];
    $author = $pub["dc"]["creators"][0]["name"] ?: "";
    $article_link = "http://www.ncbi.nlm.nih.gov/pubmed/" . $pmid_split[1];

    $row = Array(
        $pub_id,
        $title,
        $author,
        $pub_year,
        $pub_journal,
        $article_link,
    );
    fputcsv($output, $row);
}

?>
