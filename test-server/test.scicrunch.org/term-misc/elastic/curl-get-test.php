<?php
$docroot = "../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';

$host = $config['elasticsearch']['host'] . "/" . $config['elasticsearch']['index'] . "/" . $config['elasticsearch']['type'];
//$host = 'http://interlex.scicrunch.io:80/scicrunch_stage/term';
$cmd = $host . "/_search";

$json =   '
    {
        "from" : 0,
        "size" : 100,
        "query": { "bool":{ "must":[{"multi_match": {"query": "neuron","fields":[
        "ilx^3",
        "label^4",
        "type^2",
        "definition^2",
        "comment",
        "ontologies.url^2",
        "synonyms.literal^3",
        "existing_ids.curie^2",
        "existing_ids.iri^2",
        "superclasses.ilx",
        "superclasses.label",
        "relationships.term1_label",
        "relationships.term1_ilx",
        "relationships.term12_label",
        "relationships.term2_ilx",
        "relationships.relationship_term_label",
        "relationships.relationship_term_ilx",
        "annotations.term_label",
        "annotations.term_ilx",
        "annotations.annotation_term_label",
        "annotations.annotation_term_ilx",
        "annotations.value"
    ]}},{},{}]}},
        "highlight": {
        "fields":{
            "label" : {},
            "type" : {},
            "definition": {},
            "ilx": {},
            "ontology.url": {},
            "synonyms.literal": {},
            "existing_ids.curie": {},
            "existing_ids.iri": {},
            "comment": {},
            "superclass.ilx": {},
            "superclasses.label": {},
            "relationships.term1_label": {},
            "relationships.term2_label": {},
            "relationships.term1_ilx": {},
            "relationships.term2_ilx": {},
            "relationships.relationship_term_label": {},
            "relationships.relationship_term_ilx": {},
            "annotations.term_label": {},
            "annotations.term_ilx": {},
            "annotations.annotation_term_label": {},
            "annotations.annotation_term_ilx": {},
            "annotations.value": {}
        }
    }
    }
    ';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$cmd" . '?json=' . urlencode(json_encode($json)));
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$output = curl_exec($ch);
curl_close($ch);

print $output;
?>
