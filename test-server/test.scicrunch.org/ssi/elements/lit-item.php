<?php

$pmid = $data["pmid"];

$url = Connection::environment() . '/v1/literature/pmid.json';
$jdata = \helper\sendGetRequest($url, Array("pmid" => $pmid));
$data = json_decode($jdata, true);
if(empty($data)) {
    return;
}
$record = $data[0];

$title = $record["title"];
$first_author = $record["authors"][0];
if(count($record["authors"]) > 1) {
    $multiple_authors = true;
} else {
    $multiple_authors = false;
}
$date = $record["year"] . " " . date("M", mktime(0, 0, 0, $record["month"], 0, 0)) . $record["day"];
$journal = $record["journal"];

$rrid_mentions = RRIDMention::getMentionedRRIDs($pmid);
$resource_mentions = ResourceMention::getRDWResources($pmid, $rrid_mentions);

$total_mentions = count($rrid_mentions["rrids"]) + count($resource_mentions);

$ocrc_link = WorldCatInterface::getHTML($pmid);
if(is_null($ocrc_link)) $ocrc_link = "";

?>

<style>
    .element-lit-item {
        border: solid black 1px;
        max-width: 400px;
    }
</style>

<div class="element-lit-item">
    <div class="panel panel-default">
        <div class="panel-heading"></div>
        <div class="panel-body">
            <a href="/<?php echo $pmid ?>"><h3><?php echo $title ?></h3></a>
            <p><a target="_blank" href="http://www.ncbi.nlm.nih.gov/pubmed/<?php echo $pmid ?>"><img src="/images/US-NLM-PubMed-Logo.svg" style="height: 25px" /> <i class="fa fa-external-link"></i></a><?php echo $ocrc_link ?></p>
            <p>
                <?php if($mulitple_authors): ?>
                    <?php echo $first_author ?>, et al.
                <?php else: ?>
                    <?php echo $first_author ?>.
                <?php endif ?>
                <?php echo $title ?>.
                <?php echo $journal ?>
                <?php echo $date ?>;
                PubMed PMID: <?php echo $pmid ?>
            </p>
            <p>Mentions count <?php echo $total_mentions ?></p>
        </div>
    </div>
</div>
