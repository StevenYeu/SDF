<?php

include '../classes/classes.php';
\helper\scicrunch_session_start();

function previousPage() {
    $previous_page = $_SERVER["HTTP_REFERER"];
    header("location:" . $previous_page);
    exit;
}

function getArticleURL($pub_id, $pub_type, $pub_split) {
    if(count($pub_split) < 2) return NULL;

    switch($pub_type){
    case "DOI":
        return "http://dx.doi.org/" . $pub_split[1];
    case "PMID":
        return "http://www.ncbi.nlm.nih.gov/pubmed/" . $pub_split[1];
    default:
        return NULL;
    }
}

function generateServiceURLs($mentions) {
    $mentions_chunked = array_chunk($mentions, 10);

    $urls = Array();
    $mentions_count = count($mentions_chunked);
    for($i = 0; $i < $mentions_count; $i++) {
        $pmids = Array();
        $mentions_chunk = $mentions_chunked[$i];
        $mentions_chunk_count = count($mentions_chunk);
        for($j = 0; $j < $mentions_chunk_count; $j++) {
            $pmids[] = str_replace("PMID:", "pmid=", $mentions_chunk[$j]->getMentionID());
        }
        $pmids_string = implode("&", $pmids);
        $url = Connection::environment() . "/v1/literature/pmid?" . $pmids_string;
        $urls[] = $url;
    }

    return $urls;
}

function getServiceArticles($mentions) {
    $urls = generateServiceURLs($mentions);
    $results = Connection::multi($urls);
    $papers = Array();
    foreach($results as $result) {
        $xml = simplexml_load_string($result);
        if(!$xml) continue;
        foreach($xml->publication as $pub) {
            $paper = Array();
            $pmid = (string) $pub["pmid"];
            $paper["title"] = (string) $pub->title;
            $paper["abstract"] = (string) $pub->abstract;
            $paper["journal"] = (string) $pub->journal;
            $paper["authors"] = Array();
            foreach($pub->authors->author as $author) {
                $paper["authors"][] = (string) $author;
            }
            $papers[$pmid] = $paper;
        }
    }
    return $papers;
}

$resource_id = \helper\aR($_GET["rid"], "s");
$rid = \helper\getIDFromRID($resource_id);
if(is_null($rid)) previousPage();
$resource = new Resource();
$resource->getByID($rid);
if(!$resource->id) previousPage();
$resource->getColumns();
$resource_name = $resource->columns["Resource Name"];

$mentions = ResourceMention::factoryByRID($rid);

header("Content-Type: text/csv");
header("Content-Disposition: attachment; filename=" . $resource->rid . ".csv");

$output = fopen("php://output", "w");
$header_row = Array("Resource ID", "Publication ID", "Publication type", "Title", "Journal", "First author", "Article link", "Snippet", "Quality score", "Verified", "Abstract");
fputcsv($output, $header_row);
$papers = getServiceArticles($mentions);
foreach($mentions as $men){
    $pub_id = $men->getMentionID();
    $pub_split = explode(":", $pub_id);
    $pub_type = $pub_split[0];
    $pub_id_number = (int) $pub_split[1];
    $article_link = getArticleURL($pub_id, $pub_type, $pub_split);
    $snippet = $men->getSnippet();
    $quality_score = $men->getConfidence();
    $verified = $men->getRating();
    if(isset($papers[$pub_id_number])) {
        $paper = $papers[$pub_id_number];
        $title = $paper["title"];
        $abstract = $paper["abstract"];
        $journal = $paper["journal"];
        $author = isset($paper["authors"][0]) ? $paper["authors"][0] : "";
    } else {
        $title = "";
        $abstract = "";
        $journal = "";
        $author = "";
    }
    $row = Array(
        $resource->rid,
        $pub_id,
        $pub_type,
        $title,
        $journal,
        $author,
        $article_link,
        $snippet,
        $quality_score,
        $verified,
        $abstract,
    );
    fputcsv($output, $row);
}
fclose($output);

?>
