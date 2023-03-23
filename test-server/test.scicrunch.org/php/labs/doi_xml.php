<?php
//error_reporting(E_ALL ^ E_WARNING ^ E_NOTICE);
//ini_set("display_errors", 1);
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/classes.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/connection.class.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/api-classes/datasets.php";

\helper\scicrunch_session_start();

// abel, mike, austin, jeff, romana, michael
/*
if (!in_array($_SESSION['user']->id, array(34206, 31651, 35258, 247, 35485, 36968, 33464)))
    die("access denied");
*/    
    
//include 'example.php';
$xml_string = '<?xml version="1.0" standalone="yes"?>
<resource xmlns="http://datacite.org/schema/kernel-4" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://datacite.org/schema/kernel-4 http://schema.datacite.org/meta/kernel-4/metadata.xsd">
</resource>';

if( ! $foo = simplexml_load_string( $xml_string ) ) {
    echo 'Unable to load XML string';
} else {
//    header('Content-type: text/xml');
    header('Content-type: text/html');
}

$token = \helper\getOrcidOauthAccessToken();

// if called from curator dashboard, use $_GET. else it came from datasets.php and should use $dataset->id
if (isset($_GET['dataset_id']))
    $dataset_id = $_GET['dataset_id'];
else
    $dataset_id = $dataset->id;

$doi_keyvalues = DatasetDoiKeyValues::loadArrayBy(Array("dataset_id"), Array($dataset_id));

$doi_contributor = Array();
$doi_overview = Array();
$doi_funding = Array();
$doi_author = Array();
$doi_abstract = Array();
$doi_keyword = Array();
$doi_publication = Array();
$die_error = Array();

$dataset =  Dataset::loadBy(Array("id"), Array($dataset_id));
$community = Community::getByIDStatic($dataset->lab()->cid);
$date_published = date("Y-m-d");

foreach ($doi_keyvalues as $line) {
    switch ($line->type) {
        case 'contributor':
            $doi_contributor[$line->position][$line->subtype] = $line->text;
            break;

        case 'overview':
            $doi_overview[$line->position][$line->subtype] = $line->text;
            break;

        case 'funding':
            $doi_funding[$line->position][$line->subtype] = $line->text;            
            break;

        case 'author':
            $doi_author[$line->position][$line->subtype] = $line->text;            
            break;

        case 'abstract':
            $doi_abstract[$line->position][$line->subtype] = $line->text;            
            break;

        case 'keyword':
            $doi_keyword[$line->position][$line->subtype] = $line->text;            
            break;
                        
        case 'publication':
            $doi_publication[$line->position][$line->subtype] = $line->text;            
            break;
    }
}

//$foo = new SimpleXMLElement($xmlstr);
$foo->addChild('identifier', $doi_overview[0]['hidden_doi']);
$foo->identifier->addAttribute('identifierType', 'DOI');

$foo->addChild('creators');
for ($i=0; $i<sizeof($doi_contributor); $i++) {
    $foo->creators->addChild('creator');
    $foo->creators->creator[$i]->addChild('creatorName', $doi_contributor[$i]['name']);
    if (isset($doi_contributor[$i]['orcid']) && strlen($doi_contributor[$i]['orcid'])) {
        if ($request_data = getNameDataFromORCID(str_replace("https://orcid.org/", "", $doi_contributor[$i]['orcid']))) {
            $foo->creators->creator[$i]->addChild('givenName', $request_data['name']['given-names']['value']);
            $foo->creators->creator[$i]->addChild('familyName', $request_data['name']['family-name']['value']);
        }
        $foo->creators->creator[$i]->addChild('nameIdentifier', str_replace("https://orcid.org/", "", $doi_contributor[$i]['orcid']));
        $foo->creators->creator[$i]->nameIdentifier->addAttribute('nameIdentifierScheme', 'ORCID');
        $foo->creators->creator[$i]->nameIdentifier->addAttribute('schemeURI', 'orcid.org');
    }
    $foo->creators->creator[$i]->addChild('affiliation', $doi_contributor[$i]['affiliation']);
}
$foo->addChild('titles');
$foo->titles->addChild('title', $doi_overview[0]['title']);
//$foo->titles->title->addAttribute('xml:lang', 'en-us');
$foo->addChild('publisher', $community->name . ' (' . strtoupper($community->portalName) . ')');
$foo->addChild('publicationYear', date("Y"));
$foo->addChild('resourceType', 'Tabular');
$foo->resourceType->addAttribute('resourceTypeGeneral', 'Dataset');



$foo->addChild('subjects');
for ($i=0; $i<sizeof($doi_keyword); $i++) {
    $foo->subjects->addChild('subject', $doi_keyword[$i]['keyword']);
}    

$foo->addChild('alternateIdentifiers');
$foo->alternateIdentifiers->addChild('alternateIdentifier', $community->portalName . ':' . $dataset_id);
$foo->alternateIdentifiers->alternateIdentifier->addAttribute('alternateIdentifierType', "local accession number");

$foo->addChild('relatedIdentifiers');
for ($i=0; $i<sizeof($doi_publication); $i++) {
    if (isset($doi_publication[$i]['publication_doi']) && strlen($doi_publication[$i]['publication_doi'])) {
        $foo->relatedIdentifiers->addChild('relatedIdentifier', $doi_publication[$i]['publication_doi']);
        $foo->relatedIdentifiers->relatedIdentifier[$i]->addAttribute('relatedIdentifierType', "DOI");
    } elseif (isset($doi_publication[$i]['publication_pmid']) && strlen($doi_publication[$i]['publication_pmid'])) {
        $foo->relatedIdentifiers->addChild('relatedIdentifier', $doi_publication[$i]['publication_pmid']);
        $foo->relatedIdentifiers->relatedIdentifier[$i]->addAttribute('relatedIdentifierType', "PMID");
    } else {
        // if no DOI or PMID, add error
        $foo->relatedIdentifiers->addChild('relatedIdentifier', "***No DOI/PMID Error ***");
        $die_error[] = 'Publication ' . $i . ' is missing DOI / PMID. Look for ***No DOI/PMID Error *** in XML.';
        //break;
    }
    $foo->relatedIdentifiers->relatedIdentifier[$i]->addAttribute('relationType', "IsDocumentedBy");
}    

$foo->addChild('formats');
$foo->formats->addChild('format', 'text/csv');
$foo->formats->addChild('format', 'application/zip');
$foo->formats->addChild('format', 'x-zip-compressed');

$foo->addChild('rightsList');
$foo->rightsList->addChild('rights', 'Creative Commons Attribution 4.0 International Public License');
$foo->rightsList->rights->addAttribute('rightsURI', "https://creativecommons.org/licenses/by/4.0/legalcode");

$foo->addChild('descriptions');
$foo->descriptions->addChild('description', 'STUDY PURPOSE: '. $doi_abstract[0]['study_purpose'] . " DATA COLLECTED: " . $doi_abstract[0]['data_collected'] . " DATA USAGE NOTES: " . $doi_abstract[0]['data_usage_notes']);
$foo->descriptions->description->addAttribute('descriptionType', 'Abstract');
//$foo->descriptions->description->addAttribute('xml:lang', 'en-us');

$foo->addChild('fundingReferences');
for ($i=0; $i<sizeof($doi_funding); $i++) {
    $foo->fundingReferences->addChild('fundingReference');
    $foo->fundingReferences->fundingReference[$i]->addChild('funderName', $doi_funding[$i]['agency']);
    $foo->fundingReferences->fundingReference[$i]->addChild('funderIdentifier', $doi_funding[$i]['initials']);
    $foo->fundingReferences->fundingReference[$i]->funderIdentifier->addAttribute('funderIdentifierType', 'Other');
}    

$foo->addChild('dates');
$foo->dates->addChild('date', $doi_overview[0]['date_issued']);
$foo->dates->date->addAttribute('dateType', "Issued");

$foo->addChild('language', 'en-us');
$foo->addChild('version', '1.0');

$output = $foo->asXML();
//echo $output; 

function getNameDataFromORCID($orcid, $token) {
//    $base_url = 'https://api.sandbox.orcid.org/v2.0/' . $orcid . '/personal-details';
        $request = \helper\sendGetRequest(
            "https://pub.orcid.org/v3.0/" . $orcid . "/personal-details",
            Array(),
            Array(
                "Content-Type: application/orcid+json",
                "Authorization: Bearer " . $token
            )
        );
        $request_data = json_decode($request, true);
        return $request_data;
}

$base_dir = $_SERVER["DOCUMENT_ROOT"] . "/../doi-datasets/";
if (!is_dir($base_dir . "dataset_" . $dataset_id))
    mkdir($base_dir . "dataset_" . $dataset_id, 0777, true);

file_put_contents($base_dir . 'dataset_' . $dataset_id . '/xml_' . $dataset_id . '.xml', $output);

// if publishing, then don't run this block
//if ((isset($_GET['flag'])) && ($_GET['flag'] == 'publish')) {
if ($status === Dataset::LAB_STATUS_APPROVED) {
    // echo "DONE";
} else {
    $returned = updateDOI($base_dir . 'dataset_' . $dataset_id . '/xml_' . $dataset_id . '.xml', $doi_overview[0]['hidden_doi'], $community->fullURL(), $dataset_id);
    if ($returned != 'success') {
        $die_error[] = "Could not update EZID ... " . $returned;
    }

    require_once $_SERVER["DOCUMENT_ROOT"] . "/php/labs/json_convert.php";
    $mega_json = file_get_contents($base_dir . 'dataset_' . $dataset_id . '/json_' . $dataset_id . ".json");
    require $_SERVER["DOCUMENT_ROOT"] . "/php/labs/doi_html.php";
    require $_SERVER["DOCUMENT_ROOT"] . "/php/labs/doi_make_stub.php";

    // save CSV
    $saveonly["path"] = $base_dir . "dataset_" . $dataset_id . "/";
    $saveonly["datasetid"] = $dataset_id;

    include $_SERVER["DOCUMENT_ROOT"] . "/php/dataset-csv.php";

    $zip = new ZipArchive();
    $filename = $saveonly["path"] . $community->portalName . "_" . $datasetid . ".zip";

    if (is_file($filename))
        unlink($filename);

    if ($zip->open($filename, ZipArchive::CREATE)!==TRUE) {
        exit("cannot open <$filename>\n");
    }

    $zip->addFile($saveonly["path"] . $saveonly["outfile"], $community->portalName . "_". $datasetid . ".csv");
    
    DatasetDoiKeyValues::createNewObj($dataset, $dataset_id, $saveonly["outfile"], 'overview', 'csv', 0);

    if (sizeof($die_error)) {
        echo "<h1>Validation errors</h1>\n";
        echo "<ul>\n";
        foreach ($die_error as $error) {
            echo "<li>" . $error . "</li>\n";
        }
        echo "</ul>\n";
    } else {
        echo "<h1>No errors found</h1>\n";
        echo "You can close this window and reload the curator tool.";
    }
}

?>
