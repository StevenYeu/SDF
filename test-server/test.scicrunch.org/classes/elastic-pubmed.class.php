<?php

class ElasticPMIDManager {
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

    /* special fields every rrid should have */
    private $_pmid_fields = Array(
        "title" => NULL,
        // "curie" => NULL,
        // "url" => NULL,
        // "description" => NULL,
        // "proper-citation" => NULL,
        // "type" => NULL,
        "uuid" => NULL,
    );

    private function __construct($name, $plural_name, $viewid, $es_index, $es_type, $fields, $facets, $pmid_fields, $snippet_func) {
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

        foreach($pmid_fields as $pf) {
            $this->_pmid_fields[$pf->name] = $pf;
        }
        // foreach($this->_pmid_fields as $key => $pf) {
        //     if(is_null($pf)) {
        //         throw new Exception("missing pmid field: " . $key);
        //     }
        // }

        // foreach($special_fields as $sf) {
        //     $this->_special_fields[$sf->name] = $sf;
        // }
        //
        $this->_snippet_func = $snippet_func;
    }

    public static function managerLiterature($viewid) {
        $id_view = function($raw_result) {
          return $raw_result["_id"];
        };

        $snippet_func = function($result) {
            return $result->getField("description");
        };

        $description_view = function($raw_record) {
            return \helper\formattedDescription($raw_record["_source"]["dc"]["description"]);
        };

        $fields = Array(
            new ElasticPMIDField("title", "dc.title", Array()),
            new ElasticPMIDField("pmid", "dc.identifier", Array()),
            new ElasticPMIDField("id", $id_view, Array()),
            new ElasticPMIDField("description", $description_view, Array()),
            new ElasticPMIDField("type", function() { return "Literature"; }, Array()),
            new ElasticPMIDField("Journal", "dc.publishers.[].name", Array()),
            new ElasticPMIDField("Publication Year", "dc.publicationYear", Array("dc.publicationYear"), "dc.publicationYear.keyword"),
            new ElasticPMIDField("mesh-terms", "article.mesh.[].term", Array()),
            new ElasticPMIDField("grants", "funding.grant", Array()),
            new ElasticPMIDField("Author", "dc.creators", Array()),
        );

        $facets = Array(
            new ElasticPMIDField("Publication Year", "dc.publicationYear", Array(), "dc.publicationYear.keyword"),
            new ElasticPMIDField("Journal", "dc.publishers.[].name", Array(), "dc.publishers.name.keyword"),
            new ElasticPMIDField("Author", "dc.creators.[].name", Array(), "dc.creators.name.keyword"),
        );

        $pmid_fields = Array(
            new ElasticPMIDField("title", "dc.title", Array("dc.title"), "dc.title.aggregate"),
        );

        // $special_fields = Array();

        $snippet_func = function($result) {
            return $result->getField("description");
        };

        return new ElasticPMIDManager("Literature", "Literature", $viewid, "pubmed", "literature", $fields, $facets, $pmid_fields, $snippet_func);
    }

    public static function managerPubMedMentions($viewid) {
        $id_view = function($raw_result) {
          return $raw_result["_id"];
        };

        $description_view = function($raw_record) {
            return \helper\formattedDescription($raw_record["_source"]["dc"]["description"]);
        };

        $fields = Array(
            new ElasticPMIDField("pmid", "pmid", Array()),
            new ElasticPMIDField("description", $description_view, Array()),
            new ElasticPMIDField("resourceMentions", "resourceMentions", Array()),
            new ElasticPMIDField("rridMentions", "rridMentions", Array()),
        );

        return new ElasticPMIDManager("PubMedMentions", "PubMedMentions", $viewid, "", "", $fields, Array(), Array(), "");
    }

    private static function _managerByViewID($viewid, $check) {
        switch($viewid) {
            case "pubmed":
                if($check) return true;
                return self::managerLiterature($viewid);
            case "pubmed-mentions":
                if($check) return true;
                return self::managerPubMedMentions($viewid);
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

    public function searchLiterature($query, $per_page, $page, $options) {
        $es_config = $GLOBALS["config"]["elastic-search"]["pubmed"];
        $url = $es_config["base-url"] . "/" . $this->_es_index . "/" . $this->_es_type . "/_search";
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
            $pmid_name_field = $this->_pmid_fields["title"];
            $post_query["bool"]["should"][] = Array(
                "match" => Array(
                    $pmid_name_field->es_filter_paths[0] => Array(
                        "query" => '"'.$should_query.'"',
                        "boost" => 20,
                    ),
                )
            );
            $post_query["bool"]["should"][] = Array(
                "term" => Array(
                    $pmid_name_field->es_facet_path => Array(
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
        // print "<pre>";print_r(json_encode($post_data));print "</pre>";
        if($_SESSION['user']->role == 2) $_SESSION['elastic_pubmed_query'] = json_encode($post_data);
        else $_SESSION['elastic_pubmed_query'] = "";
        // print $url;

        $result = \helper\sendPostRequest($url , json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);
        $result = json_decode($result, true);

        $elastic_result = new ElasticPMIDResults($this, $result);
        return $elastic_result;
    }

    public function searchPMID($pmid) {
        $es_config = $GLOBALS["config"]["elastic-search"]["pubmed"];
        //$url = "https://5f86098ac2b28a982cebf64e82db4ea2.us-west-2.aws.found.io:9243/pubmed/literature/_search";
        $url = $es_config["base-url"] . "/" . $this->_es_index . "/" . $this->_es_type . "/_search";
        //print $url;
        $post_header = Array("Content-type: application/json");

        $post_data = Array(
            "query" => Array(
                "term" => Array(
                    "dc.identifier" => $pmid,
                )
            )
        );
        $result = \helper\sendPostRequest($url, json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);
        $result = json_decode($result, true);

        $elastic_result = new ElasticPMIDResults($this, $result);
        //print "<pre>";print_r($elastic_result);print "</pre>";
        return $elastic_result;
    }

    public function searchPMIDMentions($pmid) {
        $es_config = $GLOBALS["config"]["elastic-search"]["pubmed"];
        // $url = "https://c77a72824235489c9b51f5f0562222df.us-west-2.aws.found.io:9243/rrid-mentions-2019jul15/_search";
        $url = $es_config["base-url"] . "/RIN_Mentions_pr/_search";
        $post_header = Array("Content-type: application/json");

        $post_data = Array(
            "query" => Array(
                "terms" => Array(
                    "_id" => [$pmid],
                )
            )
        );
        // print "<pre>";print_r(json_encode($post_data));print "</pre>";
        $result = \helper\sendPostRequest($url, json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);
        $result = json_decode($result, true);

        $elastic_result = new ElasticPMIDResults($this, $result);
        //print "<pre>";print_r($elastic_result);print "</pre>";
        return $elastic_result;
    }

    public function searchRelatedPublications($doc_id) {
        $es_config = $GLOBALS["config"]["elastic-search"]["pubmed"];
        //$url = "https://5f86098ac2b28a982cebf64e82db4ea2.us-west-2.aws.found.io:9243/pubmed/literature/_search";
        $url = $es_config["base-url"] . "/" . $this->_es_index . "/" . $this->_es_type . "/_search";
        // print $url;
        $post_header = Array("Content-type: application/json");
        $per_page = 5;
        $post_data = Array(
            "size" => $per_page,
            "query" => Array(
                "more_like_this" => Array(
                    "fields" => ["dc.title", "dc.description", "dc.article.mesh.[].term"],
                    "like" => [
                      Array(
                        "_index" => "pubmed",
                        "_id" => $doc_id
                        )
                    ]
                )
            )
        );
        // print "<pre>";print_r(json_encode($post_data));print "</pre>";
        $result = \helper\sendPostRequest($url, json_encode($post_data), $post_header, $es_config["user"] . ":" . $es_config["password"], true, $es_config["port"]);
        $result = json_decode($result, true);

        $elastic_result = new ElasticPMIDResults($this, $result);
        //print "<pre>";print_r($elastic_result);print "</pre>";
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
            return $this->_rrid_fields["uuid"];
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

class ElasticPMIDResults implements Iterator {
    private $_raw_results;
    private $_manager;
    private $_position;
    private $_records;
    private $_facets;

    public function __construct(ElasticPMIDManager $manager, $raw_results) {
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
            $this->_records[$index] = new ElasticPMIDRecord($this->_manager, $this->_raw_results["hits"]["hits"][$index]);
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

class ElasticPMIDRecord {
    private $_manager;
    private $_raw_result;
    private $_cache_fields;
    private $_cache_special_fields;

    public function __construct(ElasticPMIDManager $manager, $result) {
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

class ElasticPMIDField {
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
