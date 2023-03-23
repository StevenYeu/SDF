<?php

namespace search;

function searchSingleItemMentions($view, $rrid, $publicationYear, $place, $city, $region, $mode, $offset, $count) {
    $elastic_manager = \ElasticRRIDManager::managerByViewID($view);
    if(is_null($elastic_manager)) {
        return NULL;
    }

    $es_config = $GLOBALS["config"]["elastic-search"]["normal"];
    //$url = $es_config["base-url"] . "/rrid-mentions/_search";
    $url = $es_config["base-url"] . "/RIN_Mentions_pr/_search";

    $post_data = Array(
        "size" => $count,
        "from" => $offset,
        "query" => Array(
            "bool" => Array(
                "must" => Array(
                  // "query_string" => Array(
                  //     "fields" => Array("rridMentions.rrid", "resourceMentions.rrid"),
                  //     "query" => $rrids,
                  //     "default_operator" => "and"
                  // )
                )
            )
        ),
        "sort" => Array(
            Array(
                "dc.publicationYear" => Array(
                    "order" => "desc"
                )
            ),
            "_score"
        )
    );

    ## download co-mentions, search multiple rrids -- Vicky-2019-3-15
    if ($mode == "all") $fields = ["rridMentions.rrid.keyword", "resourceMentions.rrid.keyword", "filteredMentions.rrid.keyword"];
    else $fields = ["rridMentions.rrid.keyword"];
    $rrids = explode(",", $rrid);
    foreach ($rrids as $es_rrid) {
      if(!\helper\startsWith($es_rrid, "RRID")) {
          $es_rrid = "RRID:" . $es_rrid;
      }
      array_push($post_data["query"]["bool"]["must"], array(
          "multi_match" => Array(
              "fields" => $fields,
              "query" => $es_rrid
          )
      ));
    }

    /* add search filters */
    if($publicationYear) {
        array_push($post_data["query"]["bool"]["must"], array(
            "multi_match" => Array(
                "fields" => Array("dc.publicationYear"),
                "query" => $publicationYear
            )
        ));
    }
    if($place) {
        array_push($post_data["query"]["bool"]["must"], array(
            "multi_match" => Array(
                "fields" => Array("dc.creators.locations.name"),
                "query" => $place
            )
        ));
    }
    if($city) {
        array_push($post_data["query"]["bool"]["must"], array(
            "multi_match" => Array(
                "fields" => Array("dc.creators.locations.city"),
                "query" => $city
            )
        ));
    }
    if($region) {
        array_push($post_data["query"]["bool"]["must"], array(
            "multi_match" => Array(
                "fields" => Array("dc.creators.locations.region"),
                "query" => $region
            )
        ));
    }

    $post_header = Array(
        "Content-Type: application/json"
    );

    $results = \helper\sendPostRequest($url, json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);

    return json_decode($results, true);
}

function searchOrganizationMentions($view, $organization_mentions_rrids, $offset, $count) {
    $elastic_manager = \ElasticRRIDManager::managerByViewID($view);
    if(is_null($elastic_manager)) {
        return NULL;
    }

    $es_config = $GLOBALS["config"]["elastic-search"]["normal"];
    $url = $es_config["base-url"] . "/RIN_Mentions_pr/_search";

    $post_data = Array(
        "size" => $count,
        "from" => $offset,
        "query" => Array(
            "bool" => Array(
                "should" => Array(
                )
            )
        ),
        "sort" => Array(
            Array(
                "dc.publicationYear" => Array(
                    "order" => "desc"
                )
            ),
            "_score"
        )
    );

    $organization_mentions_rrids = explode(",", $organization_mentions_rrids);

    foreach ($organization_mentions_rrids as $rrid) {
        array_push($post_data["query"]["bool"]["should"], Array(
            "match_phrase" => Array(
                "resourceMentions.rrid.keyword" => Array(
                      "query" => $rrid
                  )
              )
        ));
        array_push($post_data["query"]["bool"]["should"], Array(
            "match_phrase" => Array(
                "rridMentions.rrid.keyword" => Array(
                      "query" => $rrid
                  )
              )
        ));
        array_push($post_data["query"]["bool"]["should"], Array(
            "match_phrase" => Array(
                "filteredMentions.rrid.keyword" => Array(
                      "query" => $rrid
                  )
              )
        ));
    }

    $post_header = Array(
        "Content-Type: application/json"
    );

    $results = \helper\sendPostRequest($url, json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);

    return json_decode($results, true);

}

function searchMentionPMIDs($view, $rrid, $offset, $count) {
    $elastic_manager = \ElasticRRIDManager::managerByViewID($view);
    if(is_null($elastic_manager)) {
        return NULL;
    }

    $es_config = $GLOBALS["config"]["elastic-search"]["normal"];
    $url = $es_config["base-url"] . "/RIN_Mentions_pr/_search";

    if(!\helper\startsWith($rrid, "RRID:")) {
        $rrid = "RRID:" . $rrid;
    }

    $post_data = Array(
        "size" => $count,
        "from" => $offset,
        "_source" => Array(
            "includes" => Array(
                "pmid",
                "dc.publicationYear"
            )
        ),
        "query" => Array(
            "bool" => Array(
                "must" => Array(
                    "multi_match" => Array(
                        "fields" => Array("rridMentions.rrid.keyword", "resourceMentions.rrid.keyword", "filteredMentions.rrid.keyword"),
                        "query" => $rrid,
                    )
                )
            )
        ),
        "sort" => Array(
            Array(
                "dc.publicationYear" => Array(
                    "order" => "desc"
                )
            ),
            "_score"
        )
    );

    $post_header = Array(
        "Content-Type: application/json"
    );

    $results = \helper\sendPostRequest($url, json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);

    return json_decode($results, true);

}

?>
