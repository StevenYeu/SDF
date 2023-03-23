<?php
// ini_set('display_errors', 1);
// ini_set('display_startup_errors', 1);
// error_reporting(E_ALL);

require_once $_SERVER["DOCUMENT_ROOT"] . '/lib/elastic/vendor/autoload.php';

function termElasticTest($user, $api_key, $fields){
    if(!\APIPermissionActions\getUser($api_key, $user)) return "not allowed";

    global $config;
    $host = $config['elastichosts'][0] . "/scicrunch/term/_search?pretty";

//    print_r($fields);
    $searchTerm = isset($fields['term']) ? $fields['term'] : "";
    $from = isset($fields['from']) ? $fields['from'] : 0;
    $size = isset($fields['size']) ? $fields['size'] : 200;
    $type = isset($fields['type']) ? $fields['type'] : 'all';
    $cid = isset($fields['cid']) ? $fields['cid'] : -1;
    $phrase = preg_match('/^(["\'])/m', $searchTerm) || preg_match('/^&#34;/', $searchTerm) || preg_match('/^&#39;/', $searchTerm) ? true : false;
    $return = array();

    $highlight = '{
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
    }';

    $phrase_highlight = '{
        "fields":{
            "label": {},
            "definition": {},
            "comment": {},
            "synonyms.literal": {},
            "superclasses.label": {},
            "relationships.term1_label": {},
            "relationships.term2_label": {},
            "relationships.relationship_term_label": {},
            "annotations.term_label": {},
            "annotations.annotation_term_label": {},
            "annotations.value": {}
        }
    }';

    $match_fields =  '
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
    ';

    $phrase_match_fields = '
        "label^4",
        "definition^2",
        "comment^2",
        "synonyms.literal^3",
        "superclasses.label",
        "relationships.term1_label",
        "relationships.term12_label",
        "relationships.relationship_term_label",
        "annotations.term_label",
        "annotations.annotation_term_label",
        "annotations.value"
    ';

    if ($phrase == true) {
        $searchTerm = str_replace('&#34;', "", $searchTerm);
        $searchTerm = str_replace('&#39;', "", $searchTerm);
        $searchTerm = str_replace('\'', "", $searchTerm);
        $searchTerm = str_replace('"', "", $searchTerm);

        $highlight = $phrase_highlight;
        $match_fields = $phrase_match_fields;
    }

    $ancestor_filter = "";
    if ($cid != -1) {
        require_once $_SERVER["DOCUMENT_ROOT"] . '/api-classes/term/get_community_terms.php';
        $ancestors = getCommunityTerms($user, $api_key, $cid);
        $ancestor_ilxes = implode('","', $ancestors);
        $ancestor_ilxes = '"' . $ancestor_ilxes . '"';
        $ancestor_filter = "\"terms\":{\"ancestors.ilx\":[$ancestor_ilxes]}";
    }

    $type_filter = '';
    if ($type != 'all') {
        $type_filter = "\"term\":{\"type\":\"$type\"}";
    }

    if ($searchTerm == '') {
        $query = "{\"bool\":{\"must\":[{\"match_all\":{}},{{$type_filter}},{{$ancestor_filter}}]}}";
    }
    else {
        $multi_match = "{\"query\": \"$searchTerm\",\"fields\":[$match_fields]}";
        if ($phrase == true) {
            $multi_match = "{\"query\":\"$searchTerm\",\"type\":\"phrase\",\"fields\":[$match_fields]}";
        }
        $query = "{ \"bool\":{ \"must\":[{\"multi_match\": $multi_match},{{$type_filter}},{{$ancestor_filter}}]}}";
    }

    $url="curl -XGET \"$host\" -d   '
    {
        \"from\" : $from,
        \"size\" : $size,
        \"query\": $query,
        \"highlight\": $highlight
    }
    '";

    $return = runQuery($url);
    return $return;
}

function runQuery($url){

    ob_start();
    passthru($url);
    $var = ob_get_contents();
    ob_end_clean();

    $arr = json_decode($var);
    return $arr;
}
//Elastica:
// require_once $_SERVER["DOCUMENT_ROOT"] . '/lib/elastica/vendor/autoload.php';
// function termElasticTest($user, $api_key, $fields){
//     if(!\APIPermissionActions\getUser($api_key, $user)) return "not allowed";

//     global $config;
//     $return = array();

//     // Create the search object and inject the client
//     $search = new \Elastica\Search(new \Elastica\Client(
//         array(
//             'host' => 'localhost',
//             'port' => 9200
//         )
//     ));
//     $qb = new \Elastica\QueryBuilder(new \Elastica\QueryBuilder\Version\Version240());

//     $query = new \Elastica\Query([
//         'query' => [
//             'term' => ['_all' => 'neuron'],
//         ],
//     ]);

//     // Configure and execute the search
//     $resultSet = $search->addIndex('scicrunch')
//     ->addType('term')
//     ->search($query);

//     $results = $resultSet->getResults();
//     $totalResults = $resultSet->getTotalHits();
//     print $totalResults . "\n";

//     foreach ($results as $result) {
//         echo $result->getData()['label'] . "\n";
//     }

//     return $return;
// }

?>