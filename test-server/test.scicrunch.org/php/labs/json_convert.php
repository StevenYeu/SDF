<?php

$build = array();
$build['@context'] = 'http://schema.org';
$build['@type'] = 'Dataset';
$build['@id'] = 'http://doi.org/' . $doi_overview[0]['hidden_doi'];
$build['version'] = '1.0';
$build['datePublished'] = $date_published;
$build['license'] = 'https://creativecommons.org/licenses/by/4.0/';
$build['headline'] = $doi_overview[0]['title'];
$build['name'] = $doi_overview[0]['title'];
$build['description'] = 'STUDY PURPOSE: ' . $doi_abstract[0]['study_purpose'];
$build['description'] .= 'DATA COLLECTED: ' . $doi_abstract[0]['data_collected'];
$build['description'] .= 'DATA USAGE NOTES: ' . $doi_abstract[0]['data_usage_notes'];
$build['keywords'] = $keyy_string;
$build['identifier'] = array('http://doi.org/' . $doi_overview[0]['doi'], $community->fullURL() . '/data/' . $_GET['dataset_id']);

$build['sponsor'] = array();
for ($i=0; $i<sizeof($doi_funding); $i++) {
    $build['sponsor'][] = array('@type'=>'Organization', 'name'=>$doi_funding[$i]['agency']);
}

$build['accountablePerson']['@type'] = 'Person';
$build['accountablePerson']['familyName'] = $doi_contributor[$contact_position]['lastname'];
$build['accountablePerson']['givenName'] = $doi_contributor[$contact_position]['firstname'];
$build['accountablePerson']['name'] = $doi_contributor[$contact_position]['firstname'] . " " . $doi_contributor[$contact_position]['lastname'];
$build['accountablePerson']['email'] = $doi_contributor[$contact_position]['email'];
$build['accountablePerson']['affiliation'] = $doi_contributor[$i]['affiliation'];

if (isset($doi_contributor[$i]['orcid']) && strlen($doi_contributor[$i]['orcid']))
    $build['accountablePerson']['identifier'] = $doi_contributor[$i]['orcid'];

$build['citation'] = array();
for ($i=0; $i<sizeof($doi_publication); $i++) {
    $pubArray['@type'] = 'CreativeWork';
    $pubArray['additionalType'] = 'ScholarlyArticle';

    if (isset($doi_publication[$i]['publication_doi']) && strlen($doi_publication[$i]['publication_doi'])) {
        $doi_pmid = $doi_publication[$i]['publication_doi'];
        $pubArray['@id'] = 'http://doi.org/' . $doi_pmid;
    } elseif (isset($doi_publication[$i]['publication_pmid']) && strlen($doi_publication[$i]['publication_pmid'])) {
        $doi_pmid = $doi_publication[$i]['publication_pmid'];
        $pubArray['@id'] = "https://pubmed.ncbi.nlm.nih.gov/" . $doi_pmid;
    }

    $pubArray['headline'] = $doi_publication[$i]['publication'];
    $pubArray['name'] = $doi_publication[$i]['publication'];
    $pubArray['identifier'] = array($pubArray['@id']);
    $pubArray['description'] = $doi_publication[$i]['citation_relevance'];

    $build['citation'][] = $pubArray;
    unset($pubArray);
}

$build['author'] = array();
$build['contributor'] = array();
for ($i=0; $i<sizeof($doi_contributor); $i++) {
    $contribArray['@type'] = 'Person';
    $contribArray['familyName'] = $doi_contributor[$i]['lastname'];
    $contribArray['givenName'] = $doi_contributor[$i]['firstname'];
    $contribArray['name'] = $doi_contributor[$i]['firstname'] . " " . $doi_contributor[$i]['lastname'];
    $contribArray['affiliation'] = $doi_contributor[$i]['affiliation'];

    if (isset($doi_contributor[$i]['orcid']) && strlen($doi_contributor[$i]['orcid'])) 
        $contribArray['identifier'] = 'https://orcid.org/' . $doi_contributor[$i]['orcid'];

    if ($doi_contributor[$i]['author'] == 1)
        $build['author'][] = $contribArray;

    $build['contributor'][] = $contribArray;        
    unset($contribArray);
}

$build['creator'] = $build['author'];

$meta_title = '<meta property="og:title" content="' . $doi_overview[0]['title'] . '">' . "\n";
$meta_description = '<meta property="og:description" content="' . $build['description'] . '">' . "\n";
$output = '<script type="application/ld+json">' . json_encode($build, JSON_UNESCAPED_SLASHES)  . '</script>';
file_put_contents($base_dir . 'dataset_' . $dataset_id . '/json_' . $dataset_id . '.json', $meta_title . $meta_description . $output);
//$json_array = json_decode($json, true);

//print_r($json_array);