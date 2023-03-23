<?php
error_reporting(E_ERROR);
$docroot = "../../..";
include_once $docroot . '/classes/classes.php';
include_once $docroot . '/config.php';
include_once $docroot . '/classes/connection.class.php';
include_once $docroot . '/classes/term.class.php';
require $docroot . '/lib/elastic/vendor/autoload.php';

use Elasticsearch;
use Elasticsearch\ClientBuilder;

$client = Elasticsearch\ClientBuilder::create()->setHosts($config['elastichosts'])->build();
$param['index'] = 'scicrunch';
$param['type'] = 'term';

$dbObj = new DbObj();
$t1 = new Term($dbObj);

$rows = $t1->getTermList();
//print_r($rows);

$count = 0;
foreach ($rows as $row){
    $count++;

//    if ($count > 11){
//       exit;
//    }

    $t = new Term($dbObj);
    $t->getById($row['id']);

    $t->getOntologies();
    $t->getExistingIds();
    $t->getSynonyms();
    $t->getSuperclasses();
    $t->getRelationships();
    $t->getAnnotations();

    $term = array();
    foreach($t as $key => $val) {
        if(!is_array($val)  && in_array($key, Term::$elasticsearch_fields)) {
            $term[$key] = utf8_encode(stripslashes($val));
        }
    }

    $ontologies = array();
    foreach ($t->ontologies as $ont) {
        $notologies2 = array();
        foreach ($ont as $key => $val){
            if (in_array($key, TermOntology::$elasticsearch_fields)){
                $ontologies2[$key] = utf8_encode(stripslashes($val));
            }
        }
        $ontologies[] = $ontologies2;
    }
    $term["ontologies"] = $ontologies;

    $synonyms = array();
    foreach ($t->synonyms as $syn) {
        $syn2 = array();
        foreach ($syn as $key => $val){
            if (in_array($key, TermSynonym::$elasticsearch_fields)){
                $syn2[$key] = utf8_encode(stripslashes($val));
            }
        }
        $synonyms[] = $syn2;
    }
    $term["synonyms"] = $synonyms;

    $existing_ids = array();
    foreach ($t->existing_ids as $eid) {
        $eid2 = array();
        foreach ($eid as $key => $val){
            if (in_array($key, TermExistingId::$elasticsearch_fields)){
                $eid2[$key] = utf8_encode(stripslashes($val));
            }
        }
        $existing_ids[] = $eid2;
    }
    $term["existing_ids"] = $existing_ids;

    $superclasses = array();
    foreach ($t->superclasses as $sup) {
        $sup2 = array();
        foreach ($sup as $key => $val){
            if (in_array($key, TermSuperclass::$elasticsearch_fields)){
                $sup2[$key] = utf8_encode(stripslashes($val));
            }
        }
        $superclasses[] = $sup2;
    }
    $term["superclasses"] = $superclasses;

    $relationships = array();
    foreach ($t->relationships as $rel) {
        $rel2 = array();
        foreach ($rel as $key => $val){
            if (in_array($key, TermRelationship::$elasticsearch_fields)){
                $rel2[$key] = utf8_encode(stripslashes($val));
            }
        }
        $relationships[] = $rel2;
    }
    $term["relationships"] = $relationships;

    $annotations = array();
    foreach ($t->annotations as $ann) {
        $ann2 = array();
        foreach ($ann as $key => $val){
            if (in_array($key, TermAnnotation::$elasticsearch_fields)){
                $ann2[$key] = utf8_encode(stripslashes($val));
            }
        }
        $annotations[] = $ann2;
    }
    $term["annotations"] = $annotations;

    $param['id'] = $term['ilx'];
    $param['body'] = $term;
//print_r($param);
//continue;

    $response = array();
    try {
        $response = $client->index($param);
    } catch (Exception $e) {
        print_r($e);
    }
    print_r($response);
}





?>
