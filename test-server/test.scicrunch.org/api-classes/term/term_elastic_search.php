<?php
//require_once $_SERVER["DOCUMENT_ROOT"] . '/lib/elastic/vendor/autoload.php';

//function termElasticSearch($user, $api_key, $searchTerm, $size=200, $from=0, $type='all', $cid=-1)
function termElasticSearch($user, $api_key, $fields)
{
    $searchTerm = isset($fields['term']) && $fields['term'] != "null" ? $fields['term'] : "*";
    $from = isset($fields['from']) && $fields['from'] != "null" ? $fields['from'] : 0;
    $size = isset($fields['size']) && $fields['size'] != "null" ? $fields['size'] : 200;
    $type = isset($fields['type']) && $fields['type'] != "null" ? $fields['type'] : 'all';
    $cid = isset($fields['cid']) ? $fields['cid'] : -1;

    require_once $_SERVER["DOCUMENT_ROOT"] . '/lib/elastic/vendor/autoload.php';
    global $config;

    $client = Elasticsearch\ClientBuilder::create()->setHosts($config['elastichosts'])->build();

    $param['index'] = 'scicrunch';
    $param['type'] = 'term';

    $param['body']['size'] = $size;
    $param['body']['from'] = $from;

    $highlight = array(
        "label"=>new \stdClass(),
        "definition"=>new \stdClass(),
        "ilx"=>new \stdClass(),
        "type"=>new \stdClass(),
        "ontologies.url"=>new \stdClass(),
        "synonyms.literal"=>new \stdClass(),
        "existing_ids.curie"=>new \stdClass(),
        "existing_ids.iri"=>new \stdClass(),
        "comment"=>new \stdClass(),
        "superclass.ilx"=>new \stdClass(),
        "superclasses.label"=>new \stdClass(),
        "relationships.term1_label"=>new \stdClass(),
        "relationships.term2_label"=>new \stdClass(),
        "relationships.term1_ilx"=>new \stdClass(),
        "relationships.term2_ilx"=>new \stdClass(),
        "relationships.relationship_term_label"=>new \stdClass(),
        "relationships.relationship_term_ilx"=>new \stdClass(),
        "annotations.term_label"=>new \stdClass(),
        "annotations.term_ilx"=>new \stdClass(),
        "annotations.annotation_term_label"=>new \stdClass(),
        "annotations.annotation_term_ilx"=>new \stdClass(),
        "annotations.value"=>new \stdClass(),
    );

    $phrase_highlight = array(
        "label"=>new \stdClass(),
        "definition"=>new \stdClass(),
        "comment"=>new \stdClass(),
        "synonyms.literal"=>new \stdClass(),
        "superclasses.label"=>new \stdClass(),
        "relationships.term1_label"=>new \stdClass(),
        "relationships.term2_label"=>new \stdClass(),
        "relationships.relationship_term_label"=>new \stdClass(),
        "annotations.term_label"=>new \stdClass(),
        "annotations.annotation_term_label"=>new \stdClass(),
        "annotations.value"=>new \stdClass(),
    );

    if ($type != 'all') {
        $param['body']['filter']['bool']['must']["term"]["status"] = 0;
        $param['body']['filter']['bool']['must']["term"]["type"] = $type;
    } else {
        $param['body']['filter']['term']['status'] = "0";
    }

//      if ($cid != -1) {
//          require_once $_SERVER["DOCUMENT_ROOT"] . '/api-classes/term/get_community_terms.php';
//          $ancestors = getCommunityTerms($user, $api_key, $cid);
//          //print_r($ancestors);
// //         $param['body']['filter']['bool']['must']['bool']['should']['terms']['ancestors.ilx'] = $ancestors;
//      }

    if ($searchTerm == '*') {
        $param['body']['query']['match_all'] = new \stdClass();
        $param['body']['highlight']['fields'] = $highlight;
    }
    elseif (preg_match('/^(["\'])/m', $searchTerm) || preg_match('/^&#34;/', $searchTerm) || preg_match('/^&#39;/', $searchTerm)){
        $searchTerm = str_replace('&#34;', "", $searchTerm);
        $searchTerm = str_replace('&#39;', "", $searchTerm);

        $param['body']['query']['multi_match']['fields'] = array(
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
            "annotations.value",
        );
        $param['body']['highlight']['fields'] = $phrase_highlight;
        $param['body']['query'] ['multi_match']['query'] = $searchTerm;
        $param['body']['query'] ['multi_match']['type'] = "phrase";
    }
    else {
        $param['body']['query']['multi_match']['fields'] =
            array("ilx^3",
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
                "annotations.value",
            );
        $param['body']['highlight']['fields'] = $highlight;
        $param['body']['query'] ['multi_match']['query'] = $searchTerm;
    }
    $return = $client->search($param);
    //print_r($return);

    return $return;
}

?>
