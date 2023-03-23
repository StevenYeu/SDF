<?php

function addTermMapping($user, $api_key, $source, $value, $matched_value, $is_ambiguous, $is_whole, $relation, $view_name, $column_name, $concept_id) {
    require_once __DIR__ . "/../elasticsearch_wrapper.php";

    if(!\APIPermissionActions\checkAction("add-term-mapping", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);
    $dbobject = new DbObj();

    $method = "user-contributed";
    $curation_status = "submitted";

    /* get the matching concept from elastic search */
    $existing_id = NULL;
    $iri = NULL;
    $concept = NULL;

    $type_search_array = Array(
        "query" => Array(
            "match_phrase" => Array(
                "existing_ids.curie" => $concept_id
            )
        )
    );
    $type_search_post = json_encode($type_search_array);
    $type_results = typeSearch($user, $api_key, "scicrunch", "term", "_search", "POST", NULL, $type_search_post, "ilx");
    $missing_curie_return = APIReturnData::quick400("could not find concept_id");
    if(!$type_results->success) return $missing_curie_return;
    $curie_results = json_decode($type_results->data["body"], true);
    if($curie_results["hits"]["total"] < 1) return $missing_curie_return;
    $curie_result = $curie_results["hits"]["hits"][0]["_source"];
    foreach($curie_result["existing_ids"] as $ei) {
        if($ei["curie"] == $concept_id) {
            $iri = $ei["iri"];
        }
        if(\helper\startsWith(strtolower($ei["curie"]), "ilx")) {
            $existing_id = $ei["curie"];
        }
    }
    if(is_null($iri) || is_null($existing_id)) return $missing_curie_return;
    $concept = $curie_result["label"];

    /* get the term */
    $term = new Term($dbobject);
    $term->getByIlx(str_replace(":", "_", $existing_id));
    if(!$term->id) {
        $term->getByIlx(str_replace("ILX:", "tmp_", $existing_id));
    }
    if(!$term->id) return APIReturnData::quick400("invalid ilx from concept_id");

    /* validate the matched_value */
    if(!$matched_value) {
        return APIReturnData::quick400("invalid matched_value");
    }

    /* validate the value */
    if($value && strpos($value, $matched_value) === false) {
        return APIReturnData::quick400("matched_valued must be contained in value");
    }

    /* validate is whole */
    if($value && $value != $matched_value) {
        $is_whole = false;
    }

    /* validate relation */
    if(!$relation) {
        $relation = "exact";
    } elseif(!in_array($relation, TermMapping::$allowed_relations)) {
        return APIReturnData::quick400("invalid relation");
    }

    /* validate view_name */
    if(!$view_name) {
        return APIReturnData::quick400("view name is required");
    }

    $term_mapping = TermMappingDBO::createNewObj(
        $cuser,
        $term,
        NULL,
        $source,
        $value,
        $matched_value,
        $is_ambiguous,
        $is_whole,
        NULL,
        $relation,
        $method,
        $curation_status,
        NULL,
        $view_name,
        $column_name,
        $concept,
        $concept_id,
        $iri,
        $existing_id
    );
    if(is_null($term_mapping)) return APIReturnData::quick400("could not create term mapping");

    return APIReturnData::build($term_mapping, true);
}

?>
