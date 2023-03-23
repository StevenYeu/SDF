<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

$app->get($AP."/term/exists", function(Request $request) use($app){
    require_once __DIR__."/term/term_exists.php";

    $uid = aR($request->get("uid"), "i");
    $label = aR($request->get("label"), "s");

    $results = termExists($app["config.user"], $app["config.api_key"], $label, $uid);
    return $app->json($results, 200);
});

$app->get($AP."/term/mappings/{term_id}", function(Request $request, $term_id) use($app) {
    require_once __DIR__."/term/term_mappings.php";
    $term_id = aR($term_id, "i");

    $size = aR($request->get("size"), "i");
    $from = aR($request->get("from"), "i");
    $curation_status = aR($request->get("curation_status"), "s");

    $results = getMappings($app["config.user"], $app["config.api_key"], $term_id, $from, $size, $curation_status);
    return $app->json($results, 200);
});

$app->get($AP."/term/with-mappings/{tmid}", function(Request $request, $tmid) use($app) {
    require_once __DIR__."/term/term_and_mappings.php";
    $tmid = aR($tmid, "i");

    $results = getTermAndMappings($app["config.user"], $app["config.api_key"], $tmid);
    return $app->json($results, 200);
});

$app->get($AP."/term/mappings-order/{tmid}", function(Request $request, $tmid) use($app) {
    require_once __DIR__."/term/term_and_mappings.php";
    $tmid = aR($tmid, "i");

    $results = getTermMappingsOrder($app["config.user"], $app["config.api_key"], $tmid);
    return $app->json($results, 200);
});

$app->post($AP."/term/mappings", function(Request $request) use($app) {
    require_once __DIR__."/term/term_mappings_add.php";

    $source = $request->request->get("source");
    $value = $request->request->get("value");
    $matched_value = $request->request->get("matched_value");
    $is_ambiguous = $request->request->get("is_ambiguous") ? true : false;
    $is_whole = $request->request->get("is_whole");
    if(is_null($is_whole)) {
        $is_whole = true;
    } else {
        $is_whole = $is_whole ? true : false;
    }
    $relation = $request->request->get("relation");
    $view_name = $request->request->get("source_level_1");
    $column_name = $request->request->get("source_level_2");
    $concept_id = $request->request->get("concept_id");

    $mapping = addTermMapping(
        $app["config.user"],
        $app["config.api_key"],
        $source,
        $value,
        $matched_value,
        $is_ambiguous,
        $is_whole,
        $relation,
        $view_name,
        $column_name,
        $concept_id
    );
    return appReturn($app, $mapping, true);
});


$app->get($AP."/term/mapping/logs/{tmid}", function(Request $request, $tmid) use($app) {
        require_once __DIR__."/term/term_mapping_logs.php";
        $tmid = aR($tmid, "i");

        $results = getMappingLogs($app["config.user"], $app["config.api_key"], $tmid);
        return $app->json($results, 200);
});


$app->get($AP."/term/view/{term_id}", function(Request $request, $term_id) use($app) {
    require_once __DIR__."/term/term_by_id.php";
    $term_id = aR($term_id, "i");

    $results = getTermById($app["config.user"], $app["config.api_key"], $term_id);
    return $app->json($results, 200);
});
$app->get($AP."/term/ilx/{ilx}", function(Request $request, $ilx) use($app) {
    require_once __DIR__."/term/term_by_ilx.php";
    $ilx = aR($ilx, "s");

    $results = getTermByIlx($app["config.user"], $app["config.api_key"], $ilx);
    return $app->json($results, 200);
});
$app->get($AP."/term/curie/{curie}", function(Request $request, $curie) use($app) {
    require_once __DIR__."/term/term_by_curie.php";
    $curie = aR($curie, "s");

    $results = getTermByCurie($app["config.user"], $app["config.api_key"], $curie);
    return $app->json($results, 200);
});
$app->get($AP."/term/lookup/{term_label}", function(Request $request, $term_label) use($app) {
    require_once __DIR__."/term/term_by_label.php";
    $term_label = aR($term_label, "s");

    $results = termLookup($app["config.user"], $app["config.api_key"], $term_label);
    return $app->json($results, 200);
});

$app->get($AP."/term/curie-by-prefix/{prefix}", function(Request $request, $prefix) use($app) {
    require_once __DIR__."/term/term_curie_by_prefix.php";
    $prefix = aR($prefix, "s");
    $results = getTermCurieByPrefix($app["config.user"], $app["config.api_key"], $prefix);
    return $app->json($results, 200);
});


$app->get($AP."/term/history/{term_id}", function(Request $request, $term_id) use($app) {
   require_once __DIR__."/term/term_history.php";
   $term_id = aR($term_id, "s");
   $results = getTermHistory($app["config.user"], $app["config.api_key"], $term_id);
   return $app->json($results, 200);
});


$app->get($AP."/term/match/{term}", function(Request $request, $term) use($app) {
    require_once __DIR__."/term/term_match.php";
    $term = aR($term, "s");
    $results = termMatch($app["config.user"], $app["config.api_key"], $term);
    return $app->json($results, 200);
});


$app->get($AP."/term/list/{type}", function(Request $request, $type) use($app){
    require_once __DIR__."/term/term_list.php";

    $type = aR($type, "s");

    $results = getTermList($app["config.user"], $app["config.api_key"], $type);
    return $app->json($results, 200);
});


$app->get($AP."/term/list", function(Request $request) use($app){
    require_once __DIR__."/term/term_list.php";

    $results = getTermList($app["config.user"], $app["config.api_key"], "all");
    return $app->json($results, 200);
});


$app->get($AP."/term/annotation/list", function(Request $request) use($app){
    require_once __DIR__."/term/annotation_term_list.php";

    $results = getAnnotationTermList($app["config.user"], $app["config.api_key"]);
    return $app->json($results, 200);
});


$app->get($AP."/curies/catalog", function(Request $request) use($app){
    require_once __DIR__."/term/curie_catalog.php";

    $results = getCurieCatalog($app["config.user"], $app["config.api_key"]);
    return $app->json($results, 200);
});


$app->get($AP."/term/ontologies", function(Request $request) use($app){
    require_once __DIR__."/term/term_ontologies.php";

    $results = getTermOntologies($app["config.user"], $app["config.api_key"]);
    return $app->json($results, 200);
});

$app->get($AP."/term/type-counts", function(Request $request) use($app){
    require_once __DIR__."/term/term_type_counts.php";

    $results = getTermTypeCounts($app["config.user"], $app["config.api_key"]);
    return $app->json($results, 200);
});

$app->get($AP."/term/curie-counts", function(Request $request) use($app){
    require_once __DIR__."/term/term_curie_counts.php";

    $type = $request->get("type");
    $results = getTermCurieCounts($app["config.user"], $app["config.api_key"], $type);
    return $app->json($results, 200);
});

$app->get($AP."/term/affiliates", function(Request $request) use($app){
    require_once __DIR__."/term/term_affiliates.php";

    $results = getTermAffiliates($app["config.user"], $app["config.api_key"]);
    return $app->json($results, 200);
});

/* TODO: not used? remove?
 * used to be used for term matches when user creating a term so not duplicating. using term elastic search now
*/
$app->get($AP."/term/search/{term}", function(Request $request, $term) use($app) {
    require_once __DIR__."/term/term_search.php";
    $term = aR($term, "s");
    $results = termSearch($app["config.user"], $app["config.api_key"], $term);
    return $app->json($results, 200);
});

$app->get($AP."/term/parents/{term_id}", function(Request $request, $term_id) use($app) {
    require_once __DIR__."/term/term_parents.php";
    $term_id = aR($term_id, "i");
    //$term_label = aR($request->query->get('term'), "s");

    $results = getTermParents($app["config.user"], $app["config.api_key"], $term_id);
    return $app->json($results, 200);
});


$app->get($AP."/term/children/{ilx}", function(Request $request, $ilx) use($app) {
    require_once __DIR__."/term/term_children.php";
    $ilx = aR($ilx, "s");

    $results = getTermChildren($app["config.user"], $app["config.api_key"], $ilx);
    return $app->json($results, 200);
});

$app->get($AP."/term/collection/{ilx}", function(Request $request, $ilx) use($app) {
    require_once __DIR__."/term/term_collection.php";
    $ilx = aR($ilx, "s");

    $results = getTermCollection($app["config.user"], $app["config.api_key"], $ilx);
    return $app->json($results, 200);
});

$app->get($AP."/term/mapping-search/{origValue}", function(Request $request, $origValue) use($app) {
    require_once __DIR__."/term/term_and_mappings.php";
    $origValue = aR($origValue, "s");
    $matchedValue = aR($request->get("matchedValue"), "s");

    $results = getTermMappingMatches($app["config.user"], $app["config.api_key"], $matchedValue, $origValue);
    return $app->json($results, 200);
});

$app->get($AP."/term/elastic-search/{keywords}", function(Request $request, $keywords) use($app) {
    require_once __DIR__."/term/term_elastic_search2.php";
    $keywords = aR($keywords, "s");

    $results = getTermESResults($app["config.user"], $app["config.api_key"], $keywords);
    return $app->json($results, 200);
});

$app->get($AP."/term/version", function(Request $request) use($app) {
    require_once __DIR__."/term/term_version.php";
    $tid = aR($request->query->get('tid', "i"));
    $version = aR($request->query->get('version', "i"));

    $results = getTermVersion($app["config.user"], $app["config.api_key"], $tid, $version);
    return $app->json($results, 200);
});


$app->get($AP."/term/elastic/search", function(Request $request) use($app) {
    require_once __DIR__."/term/term_elasticsearch.php";

    $fields = Array();
    $fields['from'] = $request->get("from") ? aR($request->get("from"), "i") : 0;
    $fields['size'] = $request->get("size") ? aR($request->get("size"), "i") : 100;
    $fields['term'] = $request->get("term") ? aR($request->get("term"), "s") : '';
    $fields['type'] = $request->get("type") ? aR($request->get("type"), "s") : 'all';
    $fields['cid'] = $request->get("cid") ? aR($request->get("cid"), "i"): -1;
    $fields['query'] = $request->get("query");

    $results = termElasticSearch($app["config.user"], $app["config.api_key"], $fields);
    return $app->json($results, 200);
});

$app->get($AP."/term/subtree/{ilx_id}", function(Request $request, $ilx_id) use($app) {
    require_once __DIR__."/term/term_subtree.php"; // uses bfs
    $ilx_id = aR($ilx_id, "s");
    $edges = $request->get("edges") ? aR($request->get("edges"), "s") : 'all';
    $types = $request->get("types") ? aR($request->get("types"), "s") : 'relationship';
    $traverse_superclasses = $request->get("traverse_superclasses") ? aR($request->get("traverse_superclasses"), "s") : 'false';
    $results = getTermSubtree($app["config.user"], $app["config.api_key"], $ilx_id, $edges, $types, $traverse_superclasses);
    return $app->json($results, 200);
});

$app->get($AP."/term/get-relationship/{id}", function(Request $request, $id) use($app) {
    require_once __DIR__."/term/term_get_relationship.php";
    $id = aR($id, "i");
    $results = getTermRelationship($app["config.user"], $app["config.api_key"], $id);
    return $app->json($results, 200);
});

$app->get($AP."/term/get-relationships/{tid}", function(Request $request, $tid) use($app) {
    require_once __DIR__."/term/term_get_relationships.php";
    $tid = aR($tid, "i");
    $results = getTermRelationships($app["config.user"], $app["config.api_key"], $tid);
    return $app->json($results, 200);
});


$app->get($AP."/term/get-annotation/{id}", function(Request $request, $id) use($app) {
    require_once __DIR__."/term/term_get_annotation.php";
    $id = aR($id, "i");
    $results = getTermAnnotation($app["config.user"], $app["config.api_key"], $id);
    return $app->json($results, 200);
});

$app->get($AP."/term/get-annotations/{tid}", function(Request $request, $tid) use($app) {
    require_once __DIR__."/term/term_get_annotations.php";
    $tid = aR($tid, "i");
    $results = getTermAnnotations($app["config.user"], $app["config.api_key"], $tid);
    return $app->json($results, 200);
});

$app->get($AP."/term/get-community", function(Request $request) use($app) {
    require_once __DIR__."/term/term_get_community.php";
    $tid = aR($request->query->get('tid', "i"));
    $cid = aR($request->query->get('cid', "i"));
    $results = getTermCommunity($app["config.user"], $app["config.api_key"], $tid, $cid);
    return $app->json($results, 200);
});

$app->get($AP."/term/get-community-terms/{cid}", function(Request $request, $cid) use($app) {
    require_once __DIR__."/term/get_community_terms.php";
    $cid = aR($cid, "i");
    $results = getCommunityTerms($app["config.user"], $app["config.api_key"], $cid);
    return $app->json($results, 200);
});

/**
 *  SWG\Post( path="/term/add-simplified", summary="term add function without having to hit ilx/add with a serperate API hit.",
 *      SWG\Parameter( name="label", description="label of new term", in="form", required=true, type="string" ),
 *      SWG\Parameter( name="definition", description="definition of the new term", in="form", required=false, type="string" ),
 *      SWG\Parameter( name="type", description="type of the new term", in="form", required=false, type="string" ),
 *  )
 **/
$app->post($AP."/term/add", function(Request $request) use($app) {
    require_once __DIR__."/term/add_term.php";

    $cid = aR($request->request->get("cid"), "i");
    $raw_fields = $request->request->all();

    $fields = Array();
    foreach($raw_fields as $key => $val) {
        if(is_array($val)) {
            $temp = array();
            foreach ($val as $v) {
                $temp2 = array();
                foreach ($v as $k=>$v2) {
                    $temp2[$k] = $v2;
                }
                $temp[] = $temp2;
            }
            $fields[$key] = $temp;
        } else {
            $fields[$key] = $val;
        }
    }
    if (!array_key_exists('force', $fields)) {
        $fields['force'] = false;
    }
    // Create ILX ID in ilx_identifiers table if label doesn't already exist for the API key and then adds to terms table
    return appReturn($app, addTerm($app["config.user"], $app["config.api_key"], $cid, $fields));
});

/**
 *  SWG\Post( path="/term/edit/{term_id}", summary="term edit function",
 *      SWG\Parameter( name="term_id", description="id of the term being edited", in="path", required=true, type="integer" ),
 *      SWG\Parameter( name="label", description="new label", in="form", required=false, type="string" ),
 *      SWG\Parameter( name="definition", description="new definition", in="form", required=false, type="string" ),
 *  )
 **/
$app->post($AP."/term/edit/{term_id}", function(Request $request, $term_id) use($app){
    require_once __DIR__."/term/edit_term.php";
    require_once __DIR__."/term/term_by_ilx.php";

    $cid = aR($request->request->get("cid"), "i");
    $term_id = aR($term_id, "s");

    $raw_fields = $request->request->all();

    $fields = Array();
    foreach($raw_fields as $key => $val) {
        if(is_array($val)) {
            $temp = array();
            foreach ($val as $v) {
                $temp2 = array();
                foreach ($v as $k=>$v2) {
                    $temp2[$k] = $v2;
                }
                $temp[] = $temp2;
            }
            $fields[$key] = $temp;
        } else {
            $fields[$key] = $val;
        }
    }

    $results = editTerm($app["config.user"], $app["config.api_key"], $term_id, $cid, $fields);
    if($results === "not allowed") return $app->json("action is not allowed", 403);
    elseif(gettype($results) === "string") return $app->json($results, 400);
    else return $app->json($results, 200);
});


$app->post($AP."/curie/catalog/add", function(Request $request) use($app){
    require_once __DIR__."/term/add_curie_catalog.php";

    $raw_fields = $request->request->all();

    $fields = Array();
    foreach($raw_fields as $key => $val) {
        $fields[$key] = $val;
    }

    $results = addToCurieCatalog($app["config.user"], $app["config.api_key"], $fields);
    return $app->json($results, 200);
});


$app->post($AP."/term/ontology/add", function(Request $request) use($app){
    require_once __DIR__."/term/add_term_ontology.php";

    $url = $request->get("url");
    $results = addToTermOntology($app["config.user"], $app["config.api_key"], $url);
    return $app->json($results, 200);
});


$app->post($AP."/term/add-relationship", function(Request $request) use($app) {
    require_once __DIR__."/term/add_term_relationship.php";
    $raw_fields = $request->request->all();

    $fields = Array();
    foreach($raw_fields as $key => $val) {
       $fields[$key] = $val;
    }

    $results = addTermRelationship($app["config.user"], $app["config.api_key"], $fields);
    if($results === "not allowed")
        return $app->json("action is not allowed", 403);
    elseif($results === "Relationship already exists. You can edit it instead.")
        return $app->json($results, 200);
    elseif(gettype($results) === "string")
        return $app->json($results, 400);
    else return $app->json($results, 201);
});


$app->post($AP."/term/add-annotation", function(Request $request) use($app) {
    require_once __DIR__."/term/add_term_annotation.php";
    $raw_fields = $request->request->all();

    $fields = Array();
    foreach($raw_fields as $key => $val) {
        $fields[$key] = $val;
    }

    $results = addTermAnnotation($app["config.user"], $app["config.api_key"], $fields);
    if($results === "not allowed")
        return $app->json("action is not allowed", 403);
    elseif($results === "Annotation already exists. You can edit it instead.")
        return $app->json($results, 200);
    elseif(gettype($results) === "string")
        return $app->json($results, 400);
    else return $app->json($results, 201);
});


$app->post($AP."/term/edit-relationship/{id}", function(Request $request, $id) use($app) {
    require_once __DIR__."/term/edit_term_relationship.php";
    $raw_fields = $request->request->all();
    $id = aR($id, "i");

    $fields = Array();
    foreach($raw_fields as $key => $val) {
        $fields[$key] = $val;
    }

    $results = editTermRelationship($app["config.user"], $app["config.api_key"], $id, $fields);
    if($results === "not allowed") return $app->json("action is not allowed", 403);
    elseif(gettype($results) === "string") return $app->json($results, 400);
    else return $app->json($results, 201);
});


$app->post($AP."/term/edit-annotation/{id}", function(Request $request, $id) use($app) {
    require_once __DIR__."/term/edit_term_annotation.php";
    $raw_fields = $request->request->all();
    $id = aR($id, "i");

    $fields = Array();
    foreach($raw_fields as $key => $val) {
        $fields[$key] = $val;
    }

    $results = editTermAnnotation($app["config.user"], $app["config.api_key"], $id, $fields);
    if($results === "not allowed") return $app->json("action is not allowed", 403);
    elseif(gettype($results) === "string") return $app->json($results, 400);
    else return $app->json($results, 201);
});


$app->post($AP."/term/vote", function(Request $request) use($app) {
    require_once __DIR__."/term/term_vote.php";
    $raw_fields = $request->request->all();

    $fields = Array();
    foreach($raw_fields as $key => $val) {
        $fields[$key] = $val;
    }

    $results = addTermVote($app["config.user"], $app["config.api_key"], $fields);
    if($results === "not allowed") return $app->json("action is not allowed", 403);
    elseif(gettype($results) === "string") return $app->json($results, 400);
    else return $app->json($results, 201);
});


$app->post($AP."/term/elastic/upsert/{tid}", function(Request $request, $tid) use($app) {
    require_once __DIR__."/term/term_elasticsearch.php";

    $tid = aR($tid, "i");

    $results = termElasticUpsert($app["config.user"], $app["config.api_key"], $tid);
    if($results === "not allowed") return $app->json("action is not allowed", 403);
    else return $app->json($results, 200);
});


$app->post($AP."/term/elastic/delete/{ilx}", function(Request $request, $ilx) use($app) {
    require_once __DIR__."/term/term_elasticsearch.php";

    $ilx = aR($ilx, "s");

    $results = termElasticDelete($app["config.user"], $app["config.api_key"], $ilx);
    if($results === "not allowed") return $app->json("action is not allowed", 403);
    else return $app->json($results, 200);
});


$app->post($AP."/term/mapping/curate/{tmid}", function(Request $request, $tmid) use($app) {
    require_once __DIR__."/term/term_mapping_curate.php";
    include_once '/assets/plugins/purifier/HTMLPurifier.auto.php';

    $tmid = aR($tmid, "i");

    $raw_fields = $request->request->all();

    $fields = Array();
    $purifier_config = HTMLPurifier_Config::createDefault();
    $purifier = new HTMLPurifier($purifier_config);
    foreach($raw_fields as $key => $val) {
        $val = $purifier->purify($val);
        $fields[$key] = $val;
    }

    $results = termMappingCurate($app["config.user"], $app["config.api_key"], $tmid, $fields);
    if($results === "not allowed") return $app->json("action is not allowed", 403);
    else return $app->json($results, 200);
});

$app->post($AP."/term/add-community/{tid}", function(Request $request, $tid) use($app) {
    require_once __DIR__."/term/add_term_community.php";
    $tid = aR($tid, "i");

    $raw_fields = $request->request->all();

    $fields = Array();
    $fields['tid'] = $tid;
    foreach($raw_fields as $key => $val) {
        $fields[$key] = $val;
    }

    $results = addTermCommunity($app["config.user"], $app["config.api_key"], $tid, $fields);
    if($results === "not allowed") return $app->json("action is not allowed", 403);
    else return $app->json($results, 200);
});

$app->post($AP."/term/curate-community/{id}", function(Request $request, $id) use($app) {
    require_once __DIR__."/term/term_community_curate.php";
    $id = aR($id, "i");

    $raw_fields = $request->request->all();

    $fields = Array();
    foreach($raw_fields as $key => $val) {
        $fields[$key] = $val;
    }

    $results = curateTermCommunity($app["config.user"], $app["config.api_key"], $id, $fields);
    if($results === "not allowed") return $app->json("action is not allowed", 403);
    else return $app->json($results, 200);
});

$app->get($AP."/term/elastic/test", function(Request $request) use($app) {
    require_once __DIR__."/term/term_elastic_test.php";

//     $raw_fields = $request->request->all();
//     print_r($raw_fields);
//     foreach($raw_fields as $key => $val) {
//         $k = aR($key, "s");
//         $v = aR($val, "s");
//         $fields[$k] = $v;
//     }
    $fields = Array();
    $fields['from'] = aR($request->get("from"), "i");
    $fields['size'] = aR($request->get("size"), "i");
    $fields['term'] = aR($request->get("term"), "s");
    $fields['type'] = aR($request->get("type"), "s");
    $fields['cid'] = aR($request->get("cid"), "i");

    $results = termElasticTest($app["config.user"], $app["config.api_key"], $fields);
    if($results === "not allowed") return $app->json("action is not allowed", 403);
    else return $app->json($results, 200);
});

$app->get($AP."/term/search_by_annotation", function(Request $request) use($app) {
    require_once __DIR__."/term/term_by_annotations.php";
    $term = aR($request->query->get("term"), "s");
    $annotation_ids = $request->query->get("annotation_ids");
    $annotation_labels = $request->query->get("annotation_labels");
    $type = aR($request->query->get("type"), "s");
    $count = aR($request->query->get("count"), "i");
    $offset = aR($request->query->get("offset"), "i");
    $results = termByAnnotation($app["config.user"], $app["config.api_key"], $term, $annotation_ids, $annotation_labels, $type, $count, $offset);
    return $app->json($results, 200);
});

$app->get($AP."/term/search_by_annotation/values", function(Request $request) use($app) {
    require_once __DIR__."/term/term_by_annotations_values.php";
    $term = aR($request->query->get("term"), "s");
    $annotation_ids = $request->query->get("annotation_ids");
    $annotation_labels = $request->query->get("annotation_labels");
    $type = aR($request->query->get("type"), "s");
    $annotation_request_id = $request->query->get("annotation_request_id");
    $results = termByAnnotationValues($app["config.user"], $app["config.api_key"], $term, $annotation_request_id, $annotation_ids, $annotation_labels, $type);
    return $app->json($results, 200);
});

$app->post($AP."/term/elastic/batch-upsert", function(Request $request) use($app) {
    require_once __DIR__ . "/term/elastic_batch_upsert.php";

    $results = elasticBatchUpsert($app["config.user"], $app["config.api_key"]);
    return appReturn($results);
});

$app->get($AP."/term/most_used_cdes_datasets", function(Request $request) use($app) {
    require_once __DIR__."/term/most_used_cdes_datasets.php";
    $count = $request->query->get("count");
    return appReturn($app, termByMostUsedCDEDatasets($app["config.user"], $app["config.api_key"], $count), false, true);
});

?>
