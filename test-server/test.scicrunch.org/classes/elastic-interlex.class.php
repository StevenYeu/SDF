<?php

class ElasticInterLexManager {
    private $_name;
    private $_plural_name;
    private $_viewid;
    private $_es_index;
    private $_es_type;

    private $_fields;
    private $_facets;
    private $_fields_map;
    private $_facets_map;
    // private $_special_fields;
    private $_snippet_func;

    /* special fields every interlex should have */
    private $_interlex_fields = Array(
        "title" => NULL,
        // "curie" => NULL,
        // "url" => NULL,
        // "description" => NULL,
        // "proper-citation" => NULL,
        // "type" => NULL,
        "uuid" => NULL,
    );

    private function __construct($name, $plural_name, $viewid, $es_index, $es_type, $fields, $facets, $interlex_fields, $snippet_func) {
        $this->_name = $name;
        $this->_plural_name = $plural_name;
        $this->_viewid = $viewid;
        $this->_es_index = $es_index;
        $this->_es_type = $es_type;

        $this->_fields = $fields ?: Array();
        $this->_facets = $facets ?: Array();

        $this->_fields_map = Array();
        foreach($this->_fields as $i => $f) { $this->_fields_map[$f->name] = $i; }
        $this->_facets_map = Array();
        foreach($this->_facets as $i => $f) { $this->_facets_map[$f->name] = $i; }

        foreach($interlex_fields as $interlex) {
            $this->_interlex_fields[$interlex->name] = $interlex;
        }
        // foreach($this->_interlex_fields as $key => $pf) {
        //     if(is_null($pf)) {
        //         throw new Exception("missing interlex field: " . $key);
        //     }
        // }

        // foreach($special_fields as $sf) {
        //     $this->_special_fields[$sf->name] = $sf;
        // }
        //
        $this->_snippet_func = $snippet_func;
    }

    public static function managerInterLex($viewid) {
        $id_view = function($raw_result) {
          return $raw_result["_id"];
        };

        $score_view = function($raw_result) {
          return $raw_result["_score"];
        };

        $snippet_func = function($result) {
            return $result->getField("description");
        };

        $description_view = function($raw_record) {
            return \helper\formattedDescription($raw_record["_source"]["definition"]);
        };

        $preferred_id = function($raw_record) {
            $existing_ids = $raw_record["_source"]["existing_ids"];
            foreach($existing_ids as $existing_id) {
                if($existing_id["preferred"] == "1") return $existing_id["curie"];
            }
            return "";
        };

        $synonyms_view = function($raw_record) {
            $synonyms = [];
            foreach($raw_record["_source"]["synonyms"] as $synonym) {
                if($synonym["literal"] != "") $synonyms[] = $synonym["literal"];
            }
            return join(", ", $synonyms);
        };

        $fields = Array(
            new ElasticInterLexField("Name", "label", Array("label"), "label.aggregate"),
            new ElasticInterLexField("Description", $description_view, Array("definition")),
            new ElasticInterLexField("Preferred ID", $preferred_id, Array("existing_ids.curie")),
            new ElasticInterLexField("Type", "type", Array("type"), "type.aggregate"),
            new ElasticInterLexField("ID", "ilx", Array("ilx")),
            new ElasticInterLexField("Score", $score_view, Array(), "_score"),
            new ElasticInterLexField("Ancestors", "ancestors.[].label", Array("ancestors.label"), "ancestors.label.aggregate"),
            new ElasticInterLexField("Synonyms", $synonyms_view, Array("synonyms.literal")),
            // new ElasticInterLexField("Relationships", "relationships.[].term1_label", Array("relationships.term1_label")),
        );

        $facets = Array(
            new ElasticInterLexField("Type", "type", Array("type.keyword"), "type.aggregate"),
            new ElasticInterLexField("Superclasses", "superclasses.[].label", Array(), "superclasses.label.aggregate"),
            new ElasticInterLexField("Ancestors", "ancestors.[].label", Array(), "ancestors.label.aggregate"),
            // new ElasticInterLexField("relationships term1", "relationships.[].term1_label", Array(), "relationships.term1_label.aggregate"),
            // new ElasticInterLexField("relationships term2", "relationships.[].term2_label", Array(), "relationships.term2_label.aggregate"),
            // new ElasticInterLexField("relationships", "relationships.[].relationship_term_label", Array(), "relationships.relationship_term_label.aggregate"),
        );

        $interlex_fields = Array(
            new ElasticInterLexField("title", "label", Array("label"), "label.aggregate"),
            new ElasticInterLexField("Relationships", "relationships", Array()),
        );

        // $special_fields = Array();

        $snippet_func = function($result) {
            return $result->getField("description");
        };

        $es_index = $GLOBALS["config"]["elastic-search"]["interlex"]["index"];
        return new ElasticInterLexManager("InterLex", "InterLex", $viewid, $es_index, "term", $fields, $facets, $interlex_fields, $snippet_func);
    }

    private static function _managerByViewID($viewid, $check) {
        switch($viewid) {
            case "interlex":
                if($check) return true;
                return self::managerInterLex($viewid);
            default:
                if($check) return false;
                return NULL;
        }
    }

    public static function managerByViewID($viewid) {
        return self::_managerByViewID($viewid, false);
    }

    public static function managerExists($viewid) {
        return self::_managerByViewID($viewid, true);
    }

    public function search($query, $per_page, $page, $options) {
        $es_config = $GLOBALS["config"]["elastic-search"]["interlex"];
        $url = $es_config["base-url"] . "/" . $this->_es_index . "/" . $this->_es_type . "/_search";
        // $url = "https://6a8bdb3e85c842b2aefd83d0cc313561.us-west-2.aws.found.io:9243/interlex_2019oct28/term/_search";
        $post_header = Array("Content-type: application/json");
        $post_data = Array(
            "size" => $per_page,
            "from" => ($page - 1) * $per_page,
        );

        $post_query = Array(
            "bool" => Array(
                "must" => Array(),
                "should" => Array(),
            ),
        );

        /* filters */
        if($options["filters"]) {
            foreach($options["filters"] as $filter) {
                if(count($filter) != 2) {
                    continue;
                }
                $filter_key = $filter[0];

                ## filter by date -- Vicky-2019-3-27
                if(in_array($filter_key, ["gte", "lte"])) {
                    if($filter_key == "gte") $filter_value = $filter[1]."T000000+0000";
                    else $filter_value = $filter[1]."T235959+0000";
                    $post_query["bool"]["filter"]["range"]["provenance.creationDate"][] = Array(
                        $filter_key => $filter_value
                    );
                } else {
                    ## filter by fields
                    if(strpos($filter[1], " ") !== false) $filter_value = $filter[1];
                    else {
                        $filter_value = str_replace(['"', "'"], "", $filter[1]);
                        $filter_value = '"'.$filter_value.'"';
                    }
                    $filter_field = $this->filterField($filter_key);
                    if(is_null($filter_field)) {
                        continue;
                    }
                    if(count($filter_field->es_filter_paths) >= 1) {  /* no need to or if only one filter for field */
                        $post_query["bool"]["must"][] = Array(
                            "query_string" => Array(
                                "fields" => $filter_field->es_filter_paths,
                                "query" => $filter_value,
                                "default_operator" => "and",
                                "lenient" => "true",
                            ),
                        );
                    }
                }
            }
        }

        /* facets */
        if($options["facets"]) {
            $es_facets = Array();
            foreach($options["facets"] as $facet) {
                if(count($facet) != 2) {
                    continue;
                }
                $facet_key = $facet[0];
                $facet_value = $facet[1];
                $facet_field = $this->facetField($facet_key);
                if(is_null($facet_field)) {
                    continue;
                }
                if(!is_null($facet_field->es_facet_path)) {
                    $es_facets[$facet_field->es_facet_path][] = $facet_value;
                }
            }
            foreach ($es_facets as $facet_field_path => $facet) {
                $post_query["bool"]["filter"][] = Array(
                    "terms" => Array($facet_field_path => $facet),
                );
            }
        }

        /* sorting */
        if($options["sort"]) {
            $sort_column = $options["sort"]["column"];
            $sort_direction = $options["sort"]["direction"];
            $sort_field = $this->sortField($sort_column);
            if(!is_null($sort_field) && !is_null($sort_field->es_facet_path) && ($sort_direction == "asc" || $sort_direction == "desc")) {
                $post_sort = Array(
                    Array($sort_field->es_facet_path => $sort_direction),
                    "_score",
                );
                $post_data["sort"] = $post_sort;
            }
        }

        /* query */
        if($query && $query != "*") {
            $post_query["bool"]["must"][] = Array(
              "query_string" => Array(
                  "fields" => ["*"],
                  "query" => $query,
                  "type" => "cross_fields",
                  "default_operator" => "and",
                  "lenient" => "true",
              ),
            );

            $should_query = str_replace(['"', "( ", " )"], ["", "(", ")"], $query);
            $interlex_name_field = $this->_interlex_fields["title"];
            $post_query["bool"]["should"][] = Array(
                "match" => Array(
                    $interlex_name_field->es_filter_paths[0] => Array(
                        "query" => '"'.$should_query.'"',
                        "boost" => 20,
                    ),
                )
            );
            $post_query["bool"]["should"][] = Array(
                "term" => Array(
                    $interlex_name_field->es_facet_path => Array(
                        "term" => $should_query,
                        "boost" => 2000,
                    ),
                ),
            );
        }

        if(!empty($post_query["bool"]["must"]) || !empty($post_query["bool"]["filter"])) {
            $post_data["query"] = $post_query;
        }

        /* aggregations */
        if(!empty($this->_facets)) {
            $aggs = Array();
            foreach($this->_facets as $facet) {
                if(!is_null($facet->es_facet_path)) {
                    $agg_path = $facet->es_facet_path;
                    $agg = Array(
                        "terms" => Array(
                          "field" => $agg_path,
                          "size" => 200
                        )
                    );
                    $aggs[$facet->name] = $agg;
                }
            }
            if(!empty($aggs)) {
                $post_data["aggregations"] = $aggs;
            }
        }

        if($_SESSION['user']->role == 2) $_SESSION['elastic_interlex_query'] = json_encode($post_data);
        else $_SESSION['elastic_interlex_query'] = "";
        // print $url;

        $result = \helper\sendPostRequest($url , json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);
        // print "<pre>";print_r(json_encode($result));print "</pre>";
        $result = json_decode($result, true);

        $elastic_result = new ElasticInterLexResults($this, $result);
        // print "<pre>";print_r($elastic_result);print "</pre>";
        return $elastic_result;
    }

    public function searchInterLex($interlex) {
        $es_config = $GLOBALS["config"]["elastic-search"]["interlex"];
        //$url = "https://5f86098ac2b28a982cebf64e82db4ea2.us-west-2.aws.found.io:9243/interlex/term/_search";
        $url = $es_config["base-url"] . "/" . $this->_es_index . "/" . $this->_es_type . "/_search";
        //print $url;
        $post_header = Array("Content-type: application/json");

        $post_data = Array(
            "query" => Array(
                "term" => Array(
                    "ilx" => $interlex,
                )
            )
        );
        $result = \helper\sendPostRequest($url, json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);
        $result = json_decode($result, true);

        $elastic_result = new ElasticInterLexResults($this, $result);
        //print "<pre>";print_r($elastic_result);print "</pre>";
        return $elastic_result;
    }

    public function searchRelationships($ilx) {
        $es_config = $GLOBALS["config"]["elastic-search"]["interlex"];
        // $url = "https://interlex.scicrunch.io/interlex/term/_search";
        $url = $es_config["base-url"] . "/" . $this->_es_index . "/" . $this->_es_type . "/_search";

        $post_header = Array("Content-type: application/json");

        $post_data = Array(
            "size" => 1000,
            "from" => 0,
            "query" => Array(
                "bool" => Array(
                     "must_not" => Array(
                        Array(
                            "term" => Array(
                                "type" => Array(
                                    "value" => "relationship"
                                )
                            )
                        ),
                        Array(
                            "term" => Array(
                                "ilx" => Array(
                                    "value" => "$ilx"
                                )
                            )
                        )
                     ),
                    "must" => Array(
                        Array(
                            "term" => Array(
                                "relationships.term1_ilx" => Array(
                                    "value" => $ilx
                                )
                            )
                        ),
                        Array(
                            "match" => Array(
                                "relationships.relationship_term_label" => Array(
                                    "query" => "includesTermSet includesTerm",
                                    "operator" => "or"
                                )
                            )
                        )
                    )
                )
            )
        );
        
        $result = \helper\sendPostRequest($url, json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);
        $result = json_decode($result, true);

        return $result;
    }

    public function searchRelationshipsGet($ilx) {
        $es_config = $GLOBALS["config"]["elastic-search"]["interlex"];
        $url = "https://d7a5e34a1de3435db617d90cabe61286.us-west-2.aws.found.io/Interlex_old/term/" . $ilx;

        $header = Array("Content-type: application/json");
        $data = Array();

        $result = \helper\sendGetRequest($url, json_encode($data), $header, $es_config["user"] . ":" . $es_config["password"]);
        $result = json_decode($result, true);

        return $result;
    }

    public function searchChildren($ilx) {
        $es_config = $GLOBALS["config"]["elastic-search"]["interlex"];
        //$url = "https://d7a5e34a1de3435db617d90cabe61286.us-west-2.aws.found.io/interlex/term/_search";
        $url = $es_config["base-url"] . "/" . $this->_es_index . "/" . $this->_es_type . "/_search";
        $es_ilx = explode("_", $ilx)[1];
        $post_header = Array("Content-type: application/json");

        $post_data = Array(
            "size" => "200",
            "_source" => Array(
                    "includes" => Array( "ilx" , "label", "definition")
            ),
            "query" => Array(
                "match" => Array(
                    "ancestors.ilx" => Array(
                          "query" => $es_ilx
                      )
                )
            )
        );

        $result = \helper\sendPostRequest($url, json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);
        $result = json_decode($result, true);

        $elastic_result = new ElasticInterLexResults($this, $result);

        return $elastic_result;
    }

    public function searchByChars($chars) {
        $es_config = $GLOBALS["config"]["elastic-search"]["interlex"];
        $url = $es_config["base-url"] . "/" . $this->_es_index . "/" . $this->_es_type . "/_search";
        $post_header = Array("Content-type: application/json");
        $es_chars = strtolower($chars);

        $post_data = Array(
            "size" => 10000,
            "from" => 0,
            "query" => Array(
                "bool" => Array(
                    "must" => Array(
                        Array(
                            "prefix" => Array(
                                "label.aggregate" => Array(
                                    "value" => $es_chars
                                )
                            )
                        ),
                        Array(
                            "term" => Array(
                                "type" => "term"
                            )
                        )
                    )
                )
            ),
            "sort" => Array(
                "label.aggregate" => Array(
                    "order" => "asc"
                )
            )
        );

        $result = \helper\sendPostRequest($url, json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);
        $result = json_decode($result, true);

        $elastic_result = new ElasticInterLexResults($this, $result);

        return $elastic_result;
    }

    public function getField($raw_result, $name) {
        $field = $this->_fields[$this->_fields_map[$name]];
        if(!$field) {
            return NULL;
        }
        return $field->esToView($raw_result);
    }

    public function getName($plural = false) {
        if($plural) {
            return $this->_plural_name;
        }
        return $this->_name;
    }

    public function getViewID() {
        return $this->_viewid;
    }

    // public function getSpecialField($raw_result, $name) {
    //     $field = $this->_special_fields[$name];
    //     if(!$field) {
    //         return NULL;
    //     }
    //     return $field->esToView($raw_result);
    // }

    private function getRawResult($index) {
        return $this->_raw_results["hits"]["hits"][$index];
    }

    public function sortField($name) {
        $field = $this->_fields[$this->_fields_map[$name]];
        return $field;
    }

    public function fields() {
        return $this->_fields;
    }

    public function filterField($name) {
        return $this->_fields[$this->_fields_map[$name]];
    }

    public function facetField($name) {
        if($name == "uuid") {
            return $this->_interlex_fields["uuid"];
        }
        return $this->_facets[$this->_facets_map[$name]];
    }

    public static function searchOptionsFromGet($get_vars) {
        $search_options = Array();

        if(!empty($get_vars["filter"])) {
            $search_options["filters"] = Array();
            foreach($get_vars["filter"] as $f) {
                $search_options["filters"][] = explode(":", $f, 2);
            }
        }

        if(!empty($get_vars["facet"])) {
            $search_options["facets"] = Array();
            foreach($get_vars["facet"] as $f) {
                $search_options["facets"][] = explode(":", $f, 2);
            }
        }

        if($get_vars["sort"] && $get_vars["column"]) {
            $search_options["sort"] = Array("direction" => $get_vars["sort"], "column" => $get_vars["column"]);
        }

        return $search_options;
    }

    public function snippet($record) {
        if(is_callable($this->_snippet_func)) {
            return call_user_func($this->_snippet_func, $record);
        }
        return "";
    }
}

class ElasticInterLexResults implements Iterator {
    private $_raw_results;
    private $_manager;
    private $_position;
    private $_records;
    private $_facets;

    public function __construct(ElasticInterLexManager $manager, $raw_results) {
        $this->_manager=  $manager;
        $this->_raw_results = $raw_results;
        $this->_position = 0;
        $this->_records = Array();
    }

    public function totalCount() {
        return $this->_raw_results["hits"]["total"];
    }

    public function hitCount() {
        return count($this->_raw_results["hits"]["hits"]);
    }

    public function getByIndex($index) {
        if(!$this->_records[$index]) {
            $this->_records[$index] = new ElasticInterLexRecord($this->_manager, $this->_raw_results["hits"]["hits"][$index]);
        }
        return $this->_records[$index];
    }

    public function facets() {
        if(is_null($this->_facets)) {
            $this->_facets = Array();
            foreach($this->_raw_results["aggregations"] as $agg_name => $agg_val) {
                $facet = Array();
                foreach($agg_val["buckets"] as $av) {
                    $facet[] = Array("value" => $av["key"], "count" => $av["doc_count"]);
                }
                $this->_facets[$agg_name] = $facet;
            }
        }
        return $this->_facets;
    }

    /* iterator functions */
    public function rewind() {
        $this->_iterator_position = 0;
    }

    public function current() {
        return $this->getByIndex($this->_iterator_position);
    }

    public function key() {
        return $this->_iterator_position;
    }

    public function next() {
        $this->_iterator_position += 1;
    }

    public function valid() {
        return isset($this->_raw_results["hits"]["hits"][$this->_iterator_position]);
    }
    /* /iterator functions */
}

class ElasticInterLexRecord {
    private $_manager;
    private $_raw_result;
    private $_cache_fields;
    private $_cache_special_fields;

    public function __construct(ElasticInterLexManager $manager, $result) {
        $this->_manager = $manager;
        $this->_raw_result = $result;
        $this->_cache_fields = Array();
        $this->_cache_special_fields = Array();
    }

    public function getField($name) {
        if(!isset($this->_cache_fields[$name])) {
            $this->_cache_fields[$name] = $this->_manager->getField($this->_raw_result, $name);
        }
        return $this->_cache_fields[$name];
    }

    // public function getSpecialField($name) {
        // if(!isset($this->_cache_special_fields[$name])) {
        //     $this->_cache_special_fields[$name] = $this->_manager->getSpecialField($this->_raw_result, $name);
        // }
        // return $this->_cache_special_fields[$name];
    // }

    public function fieldsToArray() {
        $fields = $this->_manager->fields();
        $data = Array();
        foreach($fields as $field) {
            $data[$field->name] = $this->getField($field->name);
        }
        return $data;
    }

    public function snippet() {
        return $this->_manager->snippet($this);
    }
}

class ElasticInterLexField {
    public $name;
    public $es_filter_paths;
    public $es_facet_path;
    public $es_to_view_function;
    private $_visibilities = Array(
        "table" => true,
        "snippet-filter" => false,
        "single-item" => true,
        "sort" => true,
    );

    public function __construct($name, $es_to_view_function, $es_filter_paths, $es_facet_path = NULL, $visibility_modifiers = NULL) {
        $this->name = $name;
        $this->es_filter_paths = $es_filter_paths;

        ## hide "sort" if no $es_facet_path
        if(is_null($es_facet_path)) {
          $this->_visibilities["sort"] = false;
        }

        if(!is_null($es_facet_path)) {
            $this->es_facet_path = $es_facet_path;
        } elseif(!empty($this->es_filter_paths)) {
            $this->es_facet_path = $this->es_filter_paths[0] . ".keyword";
        }
        $this->es_to_view_function = $es_to_view_function;

        foreach($visibility_modifiers as $key => $vm) {
            if(isset($this->_visibilities[$key])) {
                $this->_visibilities[$key] = $vm;
            }
        }
    }

    public function esToView($raw_result) {
        if(is_null($raw_result)) {
            return NULL;
        }
        if(is_callable($this->es_to_view_function)) {
            return call_user_func($this->es_to_view_function, $raw_result);
        }
        $result = \helper\derefArray($raw_result["_source"], $this->es_to_view_function);
        return $result;
    }

    public function visible($key) {
        if(isset($this->_visibilities[$key])) {
            return $this->_visibilities[$key];
        }
        return false;
    }
}

?>
