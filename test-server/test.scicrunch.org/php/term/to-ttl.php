<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";
require_once $_SERVER['DOCUMENT_ROOT'] . "/api-classes/term/term_by_ilx.php";
include_once $_SERVER['DOCUMENT_ROOT'] . '/lib/hardf/vendor/autoload.php';
use pietercolpaert\hardf\TriGWriter;

session_start();
$ilx = $_GET["ilx"];
//echo $ilx;

$api_key = null;
$user = $_SESSION['user'];

$term = getTermByIlx($user, $api_key, $ilx, 1, 1);
$term = DbObj::termForExport($term);
//print_r($term);

/*
 * $prefixes = array('ILX', 'ilx', 'owl', 'skos', 'OBOANN');
    id_ =  pref(j['ilx'])
    type_ = {'term':'owl:Class','relationship':'owl:ObjectProperty','annotation':'owl:AnnotationProperty'}[j['type']]
    out.append( (id_, rdflib.RDF.type, type_) )
    out.append( (id_, rdflib.RDFS.label, j['label']) )
    out.append( (id_, 'skos:definition', j['definition']) )
    for syndict in j['synonyms']:
        out.append( (id_, 'OBOANN:synonym', syndict['literal']) )
    for superdict in j['superclasses']:  # should we be returning the preferred id here not the ilx? or maybe that is a different json output?
        out.append( (id_, rdflib.RDFS.subClassOf, pref(superdict['ilx'])) )
    for eid in j['existing_ids']:
        out.append( (id_, 'ilx:someOtherId', eid['iri']) )  # predicate TODO
 */

$writer = new TriGWriter([
    "prefixes" => [
        "ILX" => "http://uri.interlex.org/base/ilx_",
        "ilx" => "http://uri.interlex.org/base/ilx_",
        "OBOANN" => "http://ontology.neuinfo.org/NIF/Backend/OBO_annotation_properties.owl#",
        "skos" =>"http://www.w3.org/2004/02/skos/core#",
        "owl" => "http://www.w3.org/2002/07/owl#",
        "rdf" => "http://www.w3.org/1999/02/22-rdf-syntax-ns#",
        "rdfs"=> "http://www.w3.org/2000/01/rdf-schema#"
    ]
]);

$type = "owl:Class";
if ($term['type'] == 'relationship') {
    $type = "owl:ObjectProperty";
}
if ($term['type'] == 'annotation') {
    $type = "owl:AnnotationProperty";
}

$subject = "http://uri.interlex.org/base/" . $term['ilx'];

//term.type
$writer->addTriple($subject,"rdf:type",$type);
//term.label
$writer->addTriple($subject,"rdfs:label",$term['label']);
//term.definition
$writer->addTriple($subject,"skos:definition",$term['definition']);

//term.synonyms
foreach ($term['synonyms'] as $synonym){
    $writer->addTriple($subject, 'OBOANN:synonym', $synonym['literal']);
}

//term.superclasses
foreach ($term['superclasses'] as $superclass){
    $linearray = explode('_', $superclass['ilx']);
    $curie = strtoupper($linearray[0]) . ':' . $linearray[1];
    $writer->addTriple($subject, 'rdfs:subClassOf', $curie);
}

//term.existing_ids
foreach ($term['existing_ids'] as $eid){
    $writer->addTriple($subject, 'ilx:someOtherId', $eid['iri']);
}

echo "<pre>";
echo htmlspecialchars($writer->end());
echo "</pre>";

?>
