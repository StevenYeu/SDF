<?php

include '../classes/classes.php';

$vars = array(
    'uid'=>0,
    'cid'=>55,
    'type'=>'Consortium',
    'typeID'=>17
);
$columnMap = array(
    'resource_name'=>'Resource Name',
    'description'=>'Description',
    'url'=>'Resource URL',
    'keyword'=>'Keywords',
    'resource_pubmedids_link'=>'Defining Citation',
    'relatedto'=>'Related To',
    'parent_organization'=>'Parent Organization',
    'abbrev'=>'Abbreviation',
    'synonym'=>'Synonyms',
    'grants'=>'Funding Information',
    'supporting_agency'=>'Supporting Agency',
    'availability'=>'Availability',
    'alturl'=>'Alt URL',
    'related_disease'=>'Disease',
    'resource_type'=>'Resource Type',
    'valid_status'=>'Web Status',
    'oldurl'=>'Previous URLs',
    'comment'=>'Comments'
);
$resourceMap = array(
    'Resource Name'=>null,
    'Description'=>null,
    'Resource URL'=>null,
    'Keywords'=>null,
    'Defining Citation'=>null,
    'Related To'=>null,
    'Parent Organization'=>null,
    'Abbreviation'=>null,
    'Synonyms'=>null,
    'Funding Information'=>null,
    'Address'=>null,
    'Associated Resources'=>null,
    'Supporting Agency'=>null,
    'Availability'=>null,
    'Alt URL'=>null,
    'Twitter'=>null,
    'Governance'=>null,
    'History'=>null,
    'Mission'=>null,
    'Qualified Tool'=>null,
    'Disease'=>null,
    'Start Date'=>null,
    'Country Started'=>null,
    'Resource Type'=>null,
    'Web Status'=>null,
    'Previous URLs'=>null,
    'Comments'=>null,
);
$url = ENVIRONMENT . '/v1/federation/data/nlx_144509-1.xml?q=*&exportType=all&filter=listedby:Consortia-pedia&count=110';
$xml = simplexml_load_file($url);
if($xml){
    foreach($xml->result->results->row as $row){
        $resource = new Resource();
        $resource->create($vars);
        $resource->insertDB();
        $resource->columns = $resourceMap;
        foreach($row->data as $data){
            $resource->columns[$columnMap[(string)$data->name]] = (string)$data->value;
        }
        $resource->insertColumns();
    }
}

?>
