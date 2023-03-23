<?php

require_once $GLOBALS["DOCUMENT_ROOT"] . '/classes/schemas/schemas.class.php';

class Search extends Connection {

    public $id;
    public $uid;
    public $community;
    public $category;
    public $subcategory;
    public $source;
    public $query;
    public $display;
    public $filter;
    public $facet;
    public $sort;
    public $preference;
    public $column;
    public $exclude;
    public $include;
    public $parent;
    public $child;
    public $page;
    public $fullscreen;
    public $paginatePages = 0;
    public $allSources;
    public $stripped;
    public $per_page;

    public $vars;

    static public $archivedViews = Array(
/*    
	    "nlx_144143-1",
        "nlx_144143-8",
        "nlx_144143-9",
        "nlx_144143-10",
        "nlx_144143-6",
        "nlx_144143-7",
        "nlx_143912-1",
        "nif-0000-00386-1",
        "nif-0000-08189-1",
        "nlx_143565-2",
        "nlx_98194-1",
        "nlx_37081-1",
        "nlx_23971-1",
        "nif-0000-10626-1",
        "nif-0000-10988-1",
        "nif-0000-37443-1",
        "nlx_152893-1",
        "nif-0000-00001-1",
        "nif-0000-02955-2",
        "nlx_151660-2",
        "nlx_151835-2",
        "nlx_151835-3",
        "nlx_152525-4",
        "nlx_154720-1",
        "nlx_31015-3",
        "omics_00269-1",
        "nlx_152525-14",
        "nif-0000-00016-1",
//         "nlx_152633-1",
        "nif-0000-00339-1",
        "nif-0000-00007-2",
        "nif-0000-00007-1",
        "nlx_144511-2",
        "nlx_144511-1",
        "nif-0000-03531-2",
        "nif-0000-03531-3",
        "nif-0000-03531-4",
        "nif-0000-03208-7",
        "nif-0000-03208-4",
        "nif-0000-03208-5",
        "nif-0000-03208-2",
        "nif-0000-03208-6",
	"nif-0000-03208-3", */
    );

    private static $view_id_fields = Array(
        "nlx_144509-1" => Array("field" => "canonical_id", "rrid-prefix" => false),
        "nif-0000-07730-1" => Array("field" => "id", "rrid-prefix" => false),
        "SCR_013869-1" => Array("field" => "proper_citation", "rrid-prefix" => false, "filters" => Array("vendor" => "Vendor")),
        "nlx_154697-1" => Array("field" => "proper_citation", "rrid-prefix" => true),
        "nif-0000-11872-1" => Array("field" => "proper_citation", "rrid-prefix" => true),
        "nlx_143929-1" => Array("field" => "proper_citation", "rrid-prefix" => true),
    );

    const MAX_PAGE = 1000;

    public function create($vars, $reformat=false) {
        //if($reformat) $vars = $this->reformatVars($vars);
        $this->vars = $vars;
        $this->uid = isset($vars['uid']) ? $vars['uid'] : NULL;
        $this->community = isset($vars['community']) ? $vars['community'] : NULL;
        $this->category = isset($vars['category']) ? $vars['category'] : NULL;
        $this->subcategory = isset($vars['subcategory']) ? $vars['subcategory'] : NULL;
        $this->source = isset($vars['nif']) ? $vars['nif'] : NULL;
        $this->query = isset($vars['q']) ? $vars['q'] : NULL;
        $this->display = isset($vars['l']) ? $vars['l'] : NULL;
        $this->filter = isset($vars['filter']) ? $vars['filter'] : NULL;
        $this->facet = isset($vars['facet']) ? $vars['facet'] : NULL;
        $this->page = isset($vars['page']) ? $vars['page'] : NULL;
        $this->parent = isset($vars['parent']) ? $vars['parent'] : NULL;
        $this->child = isset($vars['child']) ? $vars['child'] : NULL;
        $this->fullscreen = isset($vars['fullscreen']) ? $vars['fullscreen'] : NULL;
        $this->sort = isset($vars['sort']) ? $vars['sort'] : NULL;
        $this->column = isset($vars['column']) ? $vars['column'] : NULL;
        $this->exclude = isset($vars['exclude']) ? $vars['exclude'] : NULL;
        $this->include = isset($vars['include']) ? $vars['include'] : NULL;
        $this->preference = isset($vars['preference']) ? $vars['preference'] : NULL;
        $this->stripped = isset($vars['stripped']) ? $vars['stripped'] : NULL;
        $this->per_page = isset($vars["per_page"]) ? (int) $vars["per_page"] : 20;

        if($this->per_page !== 10 && $this->per_page !== 20 && $this->per_page !== 50 && $this->per_page !== 100) $this->per_page = 20;
        if($this->page > self::MAX_PAGE) $this->page = self::MAX_PAGE;
    }

    /* function to set category searches to any search, unless it is a data, literature or view search */
    private function reformatVars($vars) {
        if(isset($vars["nif"])) return $vars;
        if(isset($vars["subcategory"])) unset($vars["subcategory"]);
        if($vars["category"] === "data" || $vars["category"] === "Any" || $vars["category"] === "literature") return $vars;
        $vars["category"] = "Any";
        return $vars;
    }

    public function currentFacets($vars) {
        if($vars["category"] == "discovery") {
            include '/process-elastic-search.php';
            $this->facet = $vars["facet"];
            $this->filter = $vars["filter"];
            $indices = convertIndices();
        }

        if ($this->facet || $this->filter || $this->sort || ($this->parent && !$this->source)) {
            $html = '<h3>Current Facets and Filters</h3>';
            $html .= '<ul class="list-unstyled">';
            if(is_array($this->filter) || is_object($this->filter)){
                foreach ($this->filter as $filter) {
                    $newVars = $vars;
                    $newVars['filter'] = array_diff($vars['filter'], array($filter));
                    $filter_text = self::filterText($filter);
                    ## modified last updated date format -- Vicky-2019-4-2
                    $tmp = explode(":", $filter_text);
                    if(in_array($tmp[0], ["gte", "lte"])) {
                        $date = DateTime::createFromFormat('Ymd', $tmp[1]);
                        $tmp[1] = $date->format('m/d/Y');
                        if($tmp[0] == "gte") $tmp[0] = "Records added after";
                        else $tmp[0] = "Records added before";
                        $filter_text = join(" ", $tmp);
                    }
                    $html .= '<li>' . $filter_text . ' (filter)<a href="' . $this->generateURL($newVars) . '"><i class="fa red-x fa-times-circle"></i></a></li>';
                }
            }

            if(is_array($this->facet) || is_object($this->facet)){
                foreach ($this->facet as $filter) {
                    $newVars = $vars;
                    $newVars['facet'] = array_diff($vars['facet'], array($filter));
                    if(\helper\endsWith($filter, ":")) {
                        $filter_text = $filter . "<i>&mdash;no value&mdash;</i>";
                    } else {
                        $filter_text = $filter;
                    }

                    $facet = explode(":", $filter_text);
                    if($facet[0] == "Data Sources") {
                        foreach ($indices as $key => $value) {
                            if(\helper\startsWith($facet[1], $key)) $filter_text = $facet[0].":".$value;
                        }
                    }

                    if ($filter_text == "Mentions:available") $filter_text = "Mentions:yes";
                    if ($filter_text == "Validation:true") $filter_text = "Validation:information available";
                    if ($filter_text == "Issues:warning") $filter_text = "Issues:issues found";
                    $html .= '<li>' . $filter_text . ' (facet)<a href="' . $this->generateURL($newVars) . '"><i class="fa red-x fa-times-circle"></i></a></li>';
                }
            }

            if ($this->column && $this->sort) {
                $newVars = $vars;
                $newVars['column'] = null;
                $newVars['sort'] = null;
                $html .= '<li>' . $this->column . ' : ';
                if ($this->sort == 'asc')
                    $html .= 'Ascending';
                else
                    $html .= 'Descending';
                $html .= '<a href="' . $this->generateURL($newVars) . '"><i class="fa red-x fa-times-circle"></i></a></li>';
            }

            $html .= '</ul><hr style="margin-top:10px;margin-bottom:15px;"/>';
        } else {
            $html = '';
        }

        return $html;
    }

    static public function filterText($filter) {
        $split = explode(":", $filter);
        if(count($split) !== 2) return $filter;
        if(\helper\startsWith($filter, "v_lastmodified_epoch:")) {;
            $prefix = str_replace("v_lastmodified_epoch", "last modified", $split[0]);
            if(strlen($split[1]) === 0) return $filter;
            if($split[1][0] === "<") $op = "before";
            else $op = "after";
            $time = substr($split[1], 1);
            $converted_time = date("m/d/Y", (int) $time);
            $new_filter = $prefix . ":" . $op . " " . $converted_time;
            return $new_filter;
        } else if(\helper\startsWith($filter, "v_status:")) {
            $prefix = "status:";
            switch($split[1]) {
                case "N":
                    $new_filter = $prefix . "new";
                    break;
                case "C":
                    $new_filter = $prefix . "updated";
                    break;
                default:
                    $new_filter = $prefix . "unmodified";
                    break;
            }
            return $new_filter;
        }
        return $filter;
    }

    public function currentSpecialFacets($vars, $base) {
        if ($this->facet || $this->filter || $this->sort || $this->parent) {
            $html = '<h3>Current Facets</h3>';
            $html .= '<ul class="list-unstyled">';
            foreach ($this->filter as $filter) {
                $newVars = $vars;
                $newVars['filter'] = array_diff($vars['filter'], array($filter));
                $html .= '<li>' . $filter . '<a href="' . $this->generateSpecialURL($newVars, $base) . '"><i class="fa red-x fa-times-circle"></i></a></li>';
            }

            foreach ($this->facet as $filter) {
                $newVars = $vars;
                $newVars['facet'] = array_diff($vars['facet'], array($filter));
                $html .= '<li>' . $filter . '<a href="' . $this->generateSpecialURL($newVars, $base) . '"><i class="fa red-x fa-times-circle"></i></a></li>';
            }


            if ($this->column && $this->sort) {
                $newVars = $vars;
                $newVars['column'] = null;
                $newVars['sort'] = null;
                $html .= '<li>' . $this->column . ' : ';
                if ($this->sort == 'asc')
                    $html .= 'Ascending';
                else
                    $html .= 'Descending';
                $html .= '<a href="' . $this->generateSpecialURL($newVars, $base) . '"><i class="fa red-x fa-times-circle"></i></a></li>';
            }

            $html .= '</ul><hr style="margin-top:10px;margin-bottom:15px;"/>';
        } else {
            $html = '';
        }

        return $html;
    }

    public function getTableParams() {
        if (!$this->source || $this->vars["all-fields"])
            $params = '&exportType=all';
        else
            $params = '';

        if ($this->facet) {
            foreach ($this->facet as $facet) {
                $params .= '&facet=' . rawurlencode(str_replace(Array("&lt;", "&gt;"), Array("<", ">"), $facet));
            }
        }
        if ($this->filter) {
            foreach ($this->filter as $filter) {
                $params .= '&filter=' . rawurlencode(str_replace(Array("&lt;", "&gt;"), Array("<", ">"), $filter));
            }
        }
        if ($this->exclude) {
            if ($this->exclude == 'synonyms')
                $params .= '&expandSynonyms=false&includeInferred=false';
        }
        if ($this->include) {
            if ($this->include == 'acronyms')
                $params .= '&expandAcronyms=true';
        }
        if ($this->column && $this->sort) {
            $params .= '&sortField=' . rawurlencode($this->column);
            if ($this->sort == 'asc')
                $params .= '&sortAsc=true';
            else
                $params .= '&sortAsc=false';
        }
        $params .= '&q=' . rawurlencode($this->query);

        return $params;
    }

    public function doSearch($logging=false) {   // Manu changed from true to false
        switch ($this->category) {
            case "data":
                $results = $this->dataDirect();
                break;
            case "literature":
                $results = $this->literatureSearch();
                break;
	    default:
		error_log("doSearch *************************************** ",0); // Manu
                $results = $this->resourceDirect();
                break;
        }
        $count = isset($results["total"]) ? $results["total"] : $results["count"];
        if($logging && isset($_SERVER["HTTP_REFERER"])){
            SearchFederationFailureLog::createNewObj($this->query, $_SERVER["REQUEST_URI"], $_SERVER["HTTP_REFERER"], \helper\getIP($_SERVER), $this->community->id, $this->category, $this->subcategory, $this->source, $results["return_status_code"], $count);
        }
        return $results;
    }

    /** Data Page Searches */

    public function dataDirect() {
        if ($this->fullscreen && $this->source) {
            return $this->dataFullTable();
        } elseif ($this->source) {
            return $this->dataTableSearch();
        } else {
            return $this->dataPageSearch();
        }
    }

    public function dataFullTable() {
        $params = $this->getTableParams();
        $vars['urls'][] = Connection::environment() . '/v1/federation/facets/' . $this->source . '.xml?' . $params . '&minCount=1';
        $vars['urls'][] = Connection::environment() . '/v1/federation/data/' . $this->source . '.xml?count=50&offset=' . (($this->page - 1) * 50) . $params;
        return $this->doTableSearch($vars, 50);
    }

    public function dataTableSearch() {
        $params = $this->getTableParams();
        if ($this->page > 1)
            $move = '&offset=' . (($this->page - 1) * $this->per_page) . '&count=' . $this->per_page;
        else
            $move = '&count=' . $this->per_page;
        $vars['urls'][] = Connection::environment() . '/v1/federation/facets/' . $this->source . '.xml?orMultiFacets=true' . $params . '&minCount=1';
        $vars['urls'][] = Connection::environment() . '/v1/federation/data/' . $this->source . '.xml?orMultiFacets=true' . $params . $move;
        /* temporary csv fix */
        //$vars['csv'] = PUBLICENVIRONMENT . '/v1/federation/data/' . $this->source . '.csv?orMultiFacets=true&highlight=true' . $params . "&count=1000";
        $vars['csv'] = "/php/data-federation-csv.php?orMultiFacets=true" . $params . "&count=1000&nifid=" . $this->source;
        /* /temporary csv fix */

        return $this->doTableSearch($vars, $this->per_page);
    }

    public function checkLocalStore($url) {
        $this->connect();
        $return = $this->select('search_data', array('xml'), 's', array($url), 'where url=?');
        $this->close();

        if (count($return) > 0) {
            return $return[0]['xml'];
        } else {
            return false;
        }
    }

    public function insertIntoLocalStore($url, $xml) {
        return;
        $this->connect();
        $this->insert('search_data', 'iss', array(null, $url, $xml));
        $this->close();
    }

    public function clearLocalStore() {
        $this->connect();
        $this->delete('search_data', null, array(), '');
        $this->close();
    }

    public function dataPageSearch() {
        $url = Connection::environment() . '/v1/federation/search.xml?q=' . rawurlencode($this->query);
        $finalArray = Array("count" => 0);
        if ($this->exclude) {
            if ($this->exclude == 'synonyms')
                $url .= '&expandSynonyms=false';
            elseif ($this->exclude == 'acronyms')
                $url .= '&expandAcronyms=true';
        }
        if(!!$this->parent && !!$this->child){
            $url .= '&facet=' . $this->parent . ':' . $this->child;
        }

        //$url = rawurlencode($url);
        $string = $this->checkLocalStore($url);
        if ($string) {
            $xml = simplexml_load_string($string);
        } else {
            $data_results = Connection::multi(Array($url));
            $finalArray["return_status_code"] = self::lowestConnectionStatus($finalArray);
            $xml = simplexml_load_string($data_results[0]);
            if ($xml && (int)$xml->result['total'] > 0)
                $this->insertIntoLocalStore($url, $xml->asXML());
        }


        if ($xml) {
            $allowed_nifids = array_keys($this->allSources);
            $finalArray['expansion'] = $this->getQueryInfo($xml);
            $prefAlp = array();
            $prefCov = array();
            $elseAlp = array();
            $elseCov = array();
            foreach ($xml->result->results->result as $result) {
                $string_nifid = (string)$result['nifId'];
                if(!in_array($string_nifid, $allowed_nifids)) continue;
                $parent = (string)$result['parentCategory'];
                $child = (string)$result['category'];
                $array_name = (!$finalArray['sources'][$string_nifid]) ? "sources" : "hidden-sources";

                if (!$this->parent || ($this->parent == $parent && $this->child == $child)) {
                    $single_source_vals = array(
                        'child' => $child,
                        'parent' => $parent,
                        'name' => (string)$result['db'] . ': ' . $result['indexable'],
                        'count' => (string)$result->count,
                        'cover' => sprintf("%.2f%%", ((int)$result->count / (int)$result->totalCount) * 100)
                    );
                    if($array_name == "sources") $finalArray[$array_name][$string_nifid] = $single_source_vals;
                    else $finalArray[$array_name][$string_nifid][] = $single_source_vals;
                    if (!$this->preference && $this->community->views[$string_nifid]) {
                        $prefAlp[(string)$result['db'] . ': ' . $result['indexable']] = $string_nifid;
                        $prefCov[$string_nifid] = (int)$result->count / (int)$result->totalCount;
                    } else {
                        $elseAlp[(string)$result['db'] . ': ' . $result['indexable']] = $string_nifid;
                        $elseCov[$string_nifid] = (int)$result->count / (int)$result->totalCount;
                    }
                    if($array_name == "sources") $finalArray["count"] += (int)$result->count;
                }else{
                    if(!isset($skip_cat[$parent])) $skip_cat[$parent] = Array();
                    $skip_cat[$parent][$child] = 1;
                }
            }
            foreach ($xml->result->categories->category as $category) {
                $parent = (string)$category['parent'];
                $child = (string)$category['category'];
                if(!isset($skip_cat[$parent][$child])){
                    $finalArray['categories'][$parent][$child] = (int)$category->count;
                }
            }

            if (count($prefAlp) > 0) {
                $finalArray['alphabetical'] = $prefAlp + $elseAlp;
                $finalArray['cover'] = $prefCov + $elseCov;
            } else {
                $finalArray['alphabetical'] = $elseAlp;
                $finalArray['cover'] = $elseCov;
            }
            ksort($finalArray["alphabetical"], SORT_STRING | SORT_FLAG_CASE);
        }
        return $finalArray;
    }

    /** Resource Page Searches */

    public function resourceDirect() {
        error_log("resourceDirect Start " . $this->community->name, 0); // Steven	        
        if ($this->source) {
            if($this->fullscreen) $count = 50;
            else $count = $this->per_page;
            $return0 = $this->resourceTableSearch($this->source, $count);
            if($return0) $final = $this->doTableSearch($return0, $count);
            else $final=NULL;
        } elseif ($this->category == 'Any') {
            error_log("resourceDirect Any ", 0); // Steven	        
            $return0 = $this->allCategorySearch();
            error_log("resourceDirect Return ${return0}", 0); // Steven	        
            $final = $this->doCategorySearch($return0);
	} else {
	        error_log("resourceDirect ************************************************", 0); // Manu	
            $return0 = $this->resourceCategoryURLS();
            $final = $this->doCategorySearch($return0);
        }
        return $final;
    }

    public function resourceTableSearch($source, $count=20) {
        $params = $this->getTableParams();
        $end = -1;
        if ($this->page > 1)
            $move = '&offset=' . (($this->page - 1) * $count) . '&count=' . $count;
        else
            $move = '&count=' . $count;

        $found_url = $this->buildFoundURL($source, $params);
        if(is_null($found_url)) return NULL;    // searched a source not in our community, return nothing
        $vars['urls'][0] = str_replace('/data/', '/facets/', $found_url) . $params . '&minCount=1';
        $vars['urls'][1] = $found_url . $params . $move;
        $found_url_params = explode("?", $found_url)[1];
        if(!$found_url_params) $found_url_params = "?";
        if(!\helper\startsWith($found_url_params, "?")) $found_url_params = "?" . $found_url_params;
        $vars['csv'] = "/php/data-federation-csv.php" . $found_url_params . $params . "&count=1000&orMultiFacets=true&nifid=" . $source;
        return $vars;
    }

    public function doTableSearch($vars, $amount) {
        //print_r($vars);

        $final_array = Array();
        $finalArray['export'] = $vars['csv'];
        $this->buildFacetFromURL($vars['urls'][0], $finalArray);
        $this->buildDataFromURL($vars['urls'][1], $finalArray, $amount);
        if(!isset($finalArray["table"])) $finalArray["facets"] = Array();
        return $finalArray;
    }

    public function allCategorySearch() {
        $params = $this->getTableParams();
        $count = 0;
        $urlHolder = array();
        $subHolder = array();
        $nifHolder = array();
        $max = 0;
        //print_r($this->community->urlTree);
        $urls = 0;
        foreach ($this->community->urlTree as $category => $arr) {
            if (count($arr['urls']) > 0) {
                foreach ($arr['urls'] as $url) {
                    $urls++;
                }
            }
            if (count($arr['subcategories']) > 0) {
                foreach ($arr['subcategories'] as $sub => $array) {
                    foreach ($array['urls'] as $i => $url) {
                        $urls++;
                    }
                }
            }
        }
        $theCount = ceil(20 / $urls);
        foreach ($this->community->urlTree as $category => $arr) {
            if (count($arr['urls']) > 0) {
                $num = '&offset=' . ($theCount * ($this->page - 1)).'&count=' . $theCount;

                foreach ($arr['urls'] as $url) {
                    $urlHolder[$count][] = $url . $params . $num;
                    $countHolder[$count][] = $theCount;
                }
                $subHolder[$count] = array_fill(0, count($urlHolder[$count]), $category . '|');
                $nifHolder[$count] = $arr['nif'];
                $count++;
                $max = count($urlHolder[0]);
            }
            if (count($arr['subcategories']) > 0) {
                foreach ($arr['subcategories'] as $sub => $array) {
                    $num = '&offset=' . ($theCount * ($this->page - 1)).'&count=' . $theCount;
                    foreach ($array['urls'] as $i => $url) {
                        $urlHolder[$count][] = $url . $params . $num;
                        $subHolder[$count][] = $category . '|' . $sub;
                        //echo $array['nif'][$i]."<br/>";
                        $nifHolder[$count][] = $array['nif'][$i];
                        $countHolder[$count][] = $theCount;
                    }
                    if (count($urlHolder[$count]) > $max)
                        $max = count($urlHolder[$count]);
                    $count++;
                }
            }
        }

        //print_r($nifHolder);
        //echo "\n";
        //print_r($urlHolder);
        foreach ($urlHolder as $i => $urlArr) {
            foreach ($urlArr as $j => $url4) {
                if ($urlHolder[$i][$j]) {
                    $vars['urls'][] = $urlHolder[$i][$j];
                    $vars['subcategories'][] = $subHolder[$i][$j];
                    $vars['nif'][] = $nifHolder[$i][$j];
                    $vars['counts'][] = $countHolder[$i][$j];
                }
            }
        }
        //print_r($vars);
        error_log("Do AllCategorySearch",0); // Steven
        return $vars;
    }

    public function resourceCategoryURLS() {
        $params = $this->getTableParams();
        if ($this->subcategory) {
            $num = ceil(20 / count($this->community->urlTree[$this->category]['subcategories'][$this->subcategory]['urls']));
            $params .= '&offset=' . ($num * ($this->page - 1)) . '&count=' . $num;
            foreach ($this->community->urlTree[$this->category]['subcategories'][$this->subcategory]['urls'] as $url) {
                $vars['urls'][] = $url . $params;
                $vars['counts'][] = $num;
            }
            $vars['nif'] = $this->community->urlTree[$this->category]['subcategories'][$this->subcategory]['nif'];
            foreach ($vars['urls'] as $url) {
                $vars['subcategories'][] = $this->subcategory;
            }
        } else {
            $count = 0;
            $urlHolder = array();
            $subHolder = array();
            $nifHolder = array();
            $max = 0;

            $divide = count($this->community->urlTree[$this->category]['subcategories']);

            if (count($this->community->urlTree[$this->category]['urls']) > 0) {

                $divide += 1;
                $upper = ceil(20 / $divide);
                $lower = ceil($upper / count($this->community->urlTree[$this->category]['urls']));
                $num = '&count=' . $lower . '&offset=' . (($this->page - 1) * $lower);

                foreach ($this->community->urlTree[$this->category]['urls'] as $url) {
                    $urlHolder[0][] = $url . $params . $num;
                    $countHolder[0][] = $lower;
                }
                $subHolder[0] = array_fill(0, count($urlHolder[0]), 'CURRENT');
                $nifHolder[0] = $this->community->urlTree[$this->category]['nif'];
                $count++;
                $max = count($urlHolder[0]);
            } else {
                $upper = ceil(20 / $divide);
            }
            if (count($this->community->urlTree[$this->category]['subcategories']) > 0) {
                foreach ($this->community->urlTree[$this->category]['subcategories'] as $sub => $array) {
                    $lower = ceil($upper / count($array['urls']));
                    $num = '&count=' . $lower . '&offset=' . (($this->page - 1) * $lower);
                    foreach ($array['urls'] as $i => $url) {
                        $urlHolder[$count][] = $url . $params . $num;
                        $subHolder[$count][] = $sub;
                        $nifHolder[$count][] = $array['nif'][$i];
                        $countHolder[$count][] = $lower;
                    }
                    if (count($urlHolder[$count]) > $max)
                        $max = count($urlHolder[$count]);
                    $count++;
                }
            }
            for ($j = 0; $j < $max; $j++) {
                for ($i = 0; $i < count($urlHolder); $i++) {
                    if ($urlHolder[$i][$j]) {
                        $vars['urls'][] = $urlHolder[$i][$j];
                        $vars['subcategories'][] = $subHolder[$i][$j];
                        $vars['nif'][] = $nifHolder[$i][$j];
                        $vars['counts'][] = $countHolder[$i][$j];
                    }
                }
            }
        }
        return $vars;
    }

    public function getQueryInfo($xml) {
        $finalArray['hasExpo'] = false;
        //print_r($xml);
        foreach ($xml->query->clauses->clauses as $clause) {
            $expo = array();
            foreach ($clause->expansion->expansion as $expansion) {
                $expo[] = (string)$expansion;
            }
            $finalArray['query'][] = array(
                'id' => (string)$clause['id'],
                'label' => (string)$clause->query,
                'expansion' => $expo
            );
            if (count($expo) > 0) {
                $finalArray['hasExpo'] = true;
            }
        }
        return $finalArray;
    }

    public function doCategorySearch($vars) {
        error_log("Do CategorySearch",0); // Steven
        $orderArray = array();
        $count = 0;
        foreach ($vars['urls'] as $i => $url) {
            $string = $this->checkLocalStore($url);
            if ($string) {
                $theFiles[$i] = $string;
            } else {
                $orderArray[$count] = $i;
                $urls[$count] = $url;
                $count++;
            }
        }

        if (count($urls) > 0) {
            $files = Connection::multi($urls);
            $finalArray["return_status_code"] = self::lowestConnectionStatus($finalArray);

            foreach ($files as $i => $file) {
                $this->insertIntoLocalStore($urls[$i], $file);
                $theFiles[$orderArray[$i]] = $file;
            }
        }

        $first = true;
        $snippets = array();

        $max = 0;
        $total = 0;
        $maxPages = 0;
        foreach ($theFiles as $i => $file) {
            if(!isset($this->allSources[$vars['nif'][$i]])) continue;
            //echo $vars['urls'][$i];
            $xml = $this->xmlSingleSourceSearch($file, $vars["nif"][$i]);
            if ($xml) {
                if ($first) {
                    $finalArray['expansion'] = $this->getQueryInfo($xml);
                    $first = false;
                }
                //echo $vars['urls'][$i].' : '.(int)$xml->result['resultCount'];
                $total += (int)$xml->result['resultCount'];

                if ($this->subcategory) {
                    $tree[$this->category][$this->subcategory][$this->allSources[$vars['nif'][$i]]->getTitle()] = (int)$xml->result['resultCount'];
                    $translate[$this->allSources[$vars['nif'][$i]]->getTitle()] = $vars['nif'][$i];
                } else {
                    $spliter = explode('|', $vars['subcategories'][$i]);
                    if ($this->category == 'Any') {
                        if (count($spliter) > 1 && $spliter[1] != '') {
                            $tree[$this->category][$spliter[0]][$spliter[1]][$this->allSources[$vars['nif'][$i]]->getTitle()] = (int)$xml->result['resultCount'];
                            $translate[$this->allSources[$vars['nif'][$i]]->getTitle()] = $vars['nif'][$i];
                        } else {
                            $tree[$this->category][$spliter[0]]['Not in a Subcategory'][$this->allSources[$vars['nif'][$i]]->getTitle()] = (int)$xml->result['resultCount'];
                            $translate[$this->allSources[$vars['nif'][$i]]->getTitle()] = $vars['nif'][$i];
                        }
                    } else {
                        if ($vars['subcategories'][$i] == '') {
                            $tree[$this->category]['Not in a Subcategory'][$this->allSources[$vars['nif'][$i]]->getTitle()] = (int)$xml->result['resultCount'];
                            $translate[$this->allSources[$vars['nif'][$i]]->getTitle()] = $vars['nif'][$i];
                        } else
                            $tree[$this->category][$vars['subcategories'][$i]][$this->allSources[$vars['nif'][$i]]->getTitle()] = (int)$xml->result['resultCount'];
                        $translate[$this->allSources[$vars['nif'][$i]]->getTitle()] = $vars['nif'][$i];
                    }
                }

                if ($finalArray['info']['counts']['nif'][$vars['nif'][$i]]){
                    $finalArray['info']['counts']['nif'][$vars['nif'][$i]] += (int)$xml->result['resultCount'];
                }else{
                    $finalArray['info']['counts']['nif'][$vars['nif'][$i]] = (int)$xml->result['resultCount'];
                }
                //print_r($finalArray);

                //echo $vars['counts'][$i]);
                if (ceil((int)$xml->result['resultCount'] / $vars['counts'][$i]) > $maxPages) {
                    $maxPages = ceil((int)$xml->result['resultCount'] / $vars['counts'][$i]);
                }

                if ((int)$xml->result['resultCount'] > 0){
                    if(isset($finalArray['info']['nifDirect'][$vars['nif'][$i]])){
                        array_push($finalArray['info']['nifDirect'][$vars['nif'][$i]], $vars['subcategories'][$i]);
                    }else{
                        $finalArray['info']['nifDirect'][$vars['nif'][$i]] = Array($vars['subcategories'][$i]);
                    }
                }

                if ($finalArray['info']['counts']['subs'][$vars['subcategories'][$i]])
                    $finalArray['info']['counts']['subs'][$vars['subcategories'][$i]] += (int)$xml->result['resultCount'];
                else
                    $finalArray['info']['counts']['subs'][$vars['subcategories'][$i]] = (int)$xml->result['resultCount'];

                $snippet = new Snippet();
                //echo "<br/>".$vars['view'][$i]."<br/>";
                if (!isset($snippets[$vars['nif'][$i]])) {
                    $snippet = new Snippet();
                    $snippet->getSnippetByView($this->community->id, $vars['nif'][$i]);
                    if ($snippet->raw) {
                        $snippet->splitParts();
                    } else {
                        $snippet->raw = '<xml><title></title><description></description><citation></citation><url></url></xml>';
                        $snippet->splitParts();
                    }
                    $snippets[$vars['nif'][$i]] = $snippet;
                } else
                    $snippet = $snippets[$vars['nif'][$i]];

                //print_r($snippets);

                foreach ($xml->result->results->row as $row) {
                    $snippet->resetter();
                    foreach ($row->data as $data) {
                        $snippet->replace((string)$data->name, (string)$data->value);
                        if ((string)$data->name == 'v_uuid')
                            $uuid = (string)$data->value;
                    }
                    $snippet->splitParts();
                    $results[$i][] = array(
                        'snippet' => $snippet->snippet,
                        'nif' => $vars['nif'][$i],
                        'subcategory' => $vars['subcategories'][$i],
                        'uuid' => $uuid
                    );
                }
                if (count($results[$i]) > $max)
                    $max = count($results[$i]);
            }
        }

        foreach ($tree as $level1 => $array1) {
            $lev1 = array();
            foreach ($array1 as $level2 => $array2) {
                if (!is_array($array2)) {
                    if ($level1 == 'Any') { // /Any/Category
                        $newVars = $this->vars;
                        $newVars['subcategory'] = false;
                        $newVars['nif'] = false;
                        $newVars['category'] = $level2;
                    } else { // /Category/Subcategory
                        $newVars = $this->vars;
                        if($level2 != 'Not in a Subcategory')
                            $newVars['subcategory'] = $level2;
                        else
                            $newVars['subcategory'] = false;
                        $newVars['nif'] = false;
                        $newVars['category'] = $level1;
                    }
                    $lev1[] = array('name' => $level2, 'size' => $array2, 'url' => $this->generateURL($newVars), "nif" => $newVars["nif"]);
                } else {
                    $lev2 = array();
                    foreach ($array2 as $level3 => $array3) {
                        if (!is_array($array3)) {
                            if ($level1 == 'Any') { // /Any/Category/Subcategory
                                $newVars = $this->vars;
                                if($level3 != 'Not in a Subcategory')
                                    $newVars['subcategory'] = $level3;
                                else
                                    $newVars['subcategory'] = false;
                                $newVars['nif'] = false;
                                $newVars['category'] = $level2;
                            } else { // /Category/Subcategory/Source
                                $newVars = $this->vars;
                                if($level2 != 'Not in a Subcategory')
                                    $newVars['subcategory'] = $level2;
                                else
                                    $newVars['subcategory'] = false;
                                $newVars['nif'] = $translate[$level3];
                                $newVars['category'] = $level1;
                            }
                            $lev2[] = array('name' => $level3, 'size' => $array3, 'url' => $this->generateURL($newVars), "nif" => $newVars["nif"]);
                        } else {
                            $lev3 = array();
                            foreach ($array3 as $level4 => $array4) {
                                $newVars = $this->vars;
                                if ($level3 == 'Not in a Subcategory')
                                    $newVars['subcategory'] = false;
                                else
                                    $newVars['subcategory'] = $level3;
                                $newVars['nif'] = $translate[$level4];
                                $newVars['category'] = $level2;
                                $lev3[] = array('name' => $level4, 'size' => $array4, 'url' => $this->generateURL($newVars), "nif" => $newVars["nif"]);
                            }
                            $newVars = $this->vars;
                            if ($level1 == 'Any') {
                                if ($level3 == 'Not in a Subcategory')
                                    $newVars['subcategory'] = false;
                                else
                                    $newVars['subcategory'] = $level3;
                                $newVars['nif'] = false;
                                $newVars['category'] = $level2;
                            } else {
                                if ($level2 == 'Not in a Subcategory')
                                    $newVars['subcategory'] = false;
                                else
                                    $newVars['subcategory'] = $level2;
                                $newVars['nif'] = $translate[$level3];
                                $newVars['category'] = $level2;
                            }
                            $lev2[] = array('name' => $level3, 'children' => $lev3, 'url' => $this->generateURL($newVars));
                        }
                    }
                    if ($level1 == 'Any') {
                        $newVars['subcategory'] = false;
                        $newVars['nif'] = false;
                        $newVars['category'] = $level2;
                    } else {
                        if ($level2 == 'Not in a Subcategory')
                            $newVars['subcategory'] = false;
                        else
                            $newVars['subcategory'] = $level2;
                        $newVars['nif'] = false;
                        $newVars['category'] = $level1;
                    }
                    $lev1[] = array('name' => $level2, 'children' => $lev2, 'url' => $this->generateURL($newVars));
                }
            }
            $lev0 = array('name' => $level1, 'children' => $lev1);
        }
        $finalArray['info']['tree'] = $lev0;

        //echo $max;
        $this->paginatePages = $maxPages;

        if (count($results) > 1) {
            for ($j = 0; $j < $max; $j++) {
                foreach ($results as $i => $dont) {
                    if ($results[$i][$j]) {
                        $finalArray['results'][] = $results[$i][$j];
                    }
                }
            }
        } else {
            $finalArray['results'] = end($results);
        }
        $finalArray['total'] = $total;

        // build facets if only one source had results
        $single = false;
        $source = false;
        // check if exactly one source has results
        foreach($finalArray['info']['counts']['nif'] as $nif => $n){
            if($n > 0){
                if(!$source){
                    $source = $nif;
                    $single = true;
                }else{
                    $single = false;
                    break;
                }
            }
        }
        // also make sure only one category has results
        if($single) {
            $sub_count = 0;
            foreach($finalArray["info"]["counts"]["subs"] as $sub => $count) {
                if($count > 0) {
                    $sub_count += 1;
                    if($sub_count > 1) $single = false;
                }
            }
        }
        // if only one source and category has results, get the facets
        if($single){
            $table_urls = $this->resourceTableSearch($source);
            if($table_urls) $this->buildFacetFromURL($table_urls['urls'][0], $finalArray);
        }
        $finalArray["info"]["single-source"] = $single;

        return $finalArray;
    }

    public function literatureSearch() {
        if(isset($this->vars["litref_pmids"])) {
            $offset = ($this->page - 1) * $this->per_page;
            $pmids = array_slice($this->vars["litref_pmids"], $offset, $this->per_page);

            $url = Connection::environment() . "/v1/literature/pmid.xml?";
            foreach($pmids as $i => $pmid) {
                if($i !== 0) $url .= "&";
                $url .= "pmid=" . $pmid;
            }
        } else {
            //$url = Connection::environment() . '/v1/literature/search.xml?q=' . urlencode($this->query) . '&highlight=true&facetCount=1&count=20&offset=' . (($this->page - 1) * 20);
            $url = Connection::environment() . '/v1/literature/search.xml?q=' . urlencode($this->query) . '&facetCount=1&count=20&offset=' . (($this->page - 1) * 20);
            foreach ($this->facet as $facet) {
                $splits = explode(':', $facet);
                switch ($splits[0]) {
                    case 'Author':
                        $url .= '&authorFilter=' . join(':', array_slice($splits, 1));
                        break;
                    case 'Journal':
                        $url .= '&journalFilter=' . join(':', array_slice($splits, 1));
                        break;
                    case 'Year':
                        $url .= '&yearFilter=' . join(':', array_slice($splits, 1));
                        break;
                    case 'Section':
                        $url .= '&section=' . join(':', array_slice($splits, 1));
                        break;
                    case 'Search':
                        $url .= '&searchFullText=true';
                        break;
                    case 'Require':
                        $url .= '&requireFullText=true';
                        break;
                }
            }
            if($this->sort == "date") {
                $url .= "&sort=date";
            }
            if ($this->exclude && $this->exclude == 'synonyms') {
                $url .= '&expandSynonyms=false';
            }
            if($this->include && $this->exclude == 'acronyms') {
                $url .= '&expandAcronyms=true';
            }
        }
        return $this->doLitSearch($url);
    }

    public function doLitSearch($url) {
        $url = str_replace(" ", "%20", $url);
        $string = $this->checkLocalStore($url);
        if ($string) {
            $xml = simplexml_load_string($string);
        } else {
            $response = Connection::multi(Array($url));
            $finalArray["return_status_code"] = self::lowestConnectionStatus($finalArray);
            $xml = simplexml_load_string($response[0]);
            if($xml) $this->insertIntoLocalStore($url, $xml->asXML());
        }

        if ($xml) {
            if(isset($this->vars["litref_pmids"])) {
                $publications = $xml;
                $finalArray["total"] = count($this->vars["litref_pmids"]);
                $this->paginatePages = (int) ceil($finalArray["total"] / 10);
            } else {
                $finalArray['expansion'] = $this->getQueryInfo($xml);
                $finalArray['total'] = (int)$xml->result['resultCount'];
                $this->paginatePages = (int) ceil($xml->result['resultCount'] / 20);
                foreach ($xml->result->facets as $type) {
                    if ((string)$type['category'] == 'author_facet')
                        $label = 'Author';
                    elseif ((string)$type['category'] == 'year')
                        $label = 'Year';
                    elseif ((string)$type['category'] == 'journalShort_facet')
                        $label = 'Journal';
                    elseif ((string)$type['category'] == 'grantAgency_facet')
                        $label = 'Grant';

                    foreach ($type->facets as $facet) {
                        if($label=='Year')
                            $finalArray['json'][] = array('year'=>(int)$facet,'num'=>(int)$facet['count']);
                        $finalArray['facets'][$label][] = array('text' => (string)$facet, 'count' => (int)$facet['count']);
                    }
                }
                $finalArray['facets']['Section'] = array('Title', 'Abstract', 'Introduction', 'Methods', 'Results', 'Supplement', 'Appendix', 'Contributions', 'Background', 'Commentary', 'Funding', 'Limitations', 'Caption');
                $publications = $xml->result->publications;
            }
            foreach ($publications->publication as $pub) {
                $paper = array();
                $paper['pmid'] = (string)$pub['pmid'];
                $paper['firstAuthor'] = (string)$pub->authors->author;
                foreach ($pub->authors->author as $author) {
                    $paper['authors'][] = (string)$author;
                }
                $paper['journal'] = (string)$pub->journal;
                $paper['journalShort'] = (string)$pub->journalShort;
                $paper['date'] = array(
                    'day' => (string)$pub->day,
                    'month' => (string)$pub->month,
                    'year' => (string)$pub->year
                );
                foreach ($pub->meshHeadings->meshHeading as $mesh) {
                    $paper['mesh'][] = (string)$mesh;
                }
                $paper['title'] = (string)$pub->title;
                $paper['abstract'] = (string)$pub->abstract;
                $paper['schema'] = SchemaGeneratorPublicationXML::generate($pub);
                $finalArray['papers'][] = $paper;
            }
        }
        $finalArray['export'] = str_replace(Connection::environment(), PUBLICENVIRONMENT, str_replace('.xml', '.ris', $url));
        return $finalArray;
    }

    public function getParams() {
        $params = '';
        if ($this->facet) {
            foreach ($this->facet as $facet) {
                $params .= '&facet[]=' . $facet;
            }
        }
        if ($this->filter) {
            foreach ($this->filter as $filter) {
                $params .= '&filter[]=' . $filter;
            }
        }
        if ($this->parent && $this->child) {
            $params .= '&parent=' . $this->parent . '&child=' . $this->child;
        }
        if ($this->exclude) {
            $params .= '&exclude=' . $this->exclude;
        }
        if ($this->include) {
            $params .= '&include=' . $this->include;
        }
        if (!isset($this->page) || $this->page != 1) {
            $params .= '&page=' . $this->page;
        }
        if ($this->fullscreen == 'true') {
            $params .= '&fullscreen=true';
        }
        if ($this->sort) {
            $params .= '&sort=' . $this->sort;
        }
        if ($this->preference) {
            $params .= '&preference=' . $this->preference;
        }
        if (isset($this->column) && isset($this->sort)) {
            $params .= '&column=' . $this->column . '&sort=' . $this->sort;
        }
        return $params;
    }

    public function generateURL($vars) {
        // print_r($vars);
        $params = '';
        if ($vars['facet']) {
            foreach ($vars['facet'] as $facet) {
                $params .= '&facet[]=' . rawurlencode(str_replace(Array("<",">",'"'), Array("&lt;", "&gt;", "%22"), $facet));
            }
        }
        if ($vars['filter']) {
            foreach ($vars['filter'] as $filter) {
                $params .= '&filter[]=' . str_replace(Array("<",">",'"'), Array("&lt;", "&gt;", "%22"), $filter);
            }
        }
        if ($vars['parent'] && $vars['child']) {
            $params .= '&parent=' . $vars['parent'] . '&child=' . $vars['child'];
        }
        if ($vars['exclude']) {
            $params .= '&exclude=' . $vars['exclude'];
        }
        if ($vars['include']) {
            $params .= '&include=' . $vars['include'];
        }
        if ($vars['per_page']) {
            $params .= "&per_page=" . $vars['per_page'];
        }
        if($vars["category-filter"]) {
            $params .= "&category-filter=" . $vars["category-filter"];
        }

        // "About" pages need more than just the community name in the URL
        if ($vars['type'] == 'about' && $vars['id'] != "data" && $vars['category'] != "data") {
            $url = '/' . $vars['portalName'] . "/" . $vars["type"] . "/" . $vars["title"] . "/" . $vars["id"];
        } else if ($vars['type'] == "interlex") {
            $url = Community::fullURLStatic($this->community) . "/interlex";
        } else {
            $url = Community::fullURLStatic($this->community);
        }

        if(isset($vars['stripped'])&&$vars['stripped']=='true')
            $url .= '/stripped';
        if ($vars['category']) {
            $url .= '/' . $vars['category'];
        }
        if ($vars['subcategory']) {
            $url .= '/' . $vars['subcategory'];
        }
        if ($vars['nif']) {
            $url .= '/source/' . $vars['nif'];
        }
        if (!isset($vars['page']) || $vars['page'] != 1) {
            if ($vars['type'] == "interlex") $params .= '&page=' . $vars['page'];
            else $url .= '/page/' . $vars['page'];
        }
        if ($vars['view']) {
            if($vars['rrid']) {
                if(strpos($vars['rrid'], 'protocols.io') !== false) $rrid = $vars['rrid'];
                else $rrid = preg_replace("/^rrid:/i", "", $vars["rrid"]);
                $url .= '/record/' . $vars['view'] . '/' . $rrid;
            } elseif ($vars['uuid']) {
                $url .= '/record/' . $vars['view'] . '/' . $vars['uuid'];
            } else {
                $url .= "/" . $vars['view'];
            }
        }
        if ($vars['fullscreen'] == 'true') {
            $params .= '&fullscreen=true';
        }
        if ($vars['sort']) {
            $params .= '&sort=' . $vars['sort'];
        }
        if ($vars['preference']) {
            $params .= '&preference=' . $vars['preference'];
        }
        if (isset($vars['column']) && isset($vars['sort'])) {
            $params .= '&column=' . $vars['column'] . '&sort=' . $vars['sort'];
        }
        if (isset($vars["litref"])) {
            $params .= "&litref=" . $vars["litref"];
        }
        if (isset($vars["referer"])) {
            $params .= "&referer=" . urlencode($vars["referer"]);
        }
        if ($vars["snippet-view"] && isset($vars["nif"])) {
            $params .= "&snippet-view=true";
        }
        if ($vars["sources"] && isset($vars["sources"])) {
            $params .= "&sources=" . $vars["sources"];
        }
        if (isset($vars["results-types"]) && $vars["results-types"] != "") {
            $params .= "&types=" . $vars["results-types"];
        }

        // 'About' pages aren't search related, although they do have filters ...
        if ($vars['type'] == 'about' && $vars['id'] != "data" && $vars['category'] == "") {
            $url .= '?l=' . rawurlencode($vars['l']) . str_replace(Array("+", "<", ">"), Array("%2B", "%3c", "%3e"), $params);
        } else {
            if($vars['view'] && $vars['rrid']) {
                $url .= '/resolver';
            } else if($vars['title'] == "table"){
                $url .= '/table/search';
            } else {
                $url .= '/search';
            }
            $url .= '?q=' . rawurlencode($vars['q']) . '&l=' . rawurlencode($vars['l']) . str_replace(Array("+", "<", ">"), Array("%2B", "%3c", "%3e"), $params);
        }
        if (isset($vars['db']) && $vars['db'] != "") {
            $url .= '&db=' . $vars['db'];
        }
        $url = str_replace("%25", "%", $url);   // fixed url -- Vicky-2019-6-28
        return $url;
    }

    public function generateURLFromDiff($diff, $community=NULL) {
        $newVars = $this->vars;
        foreach($diff as $dk => $dv) $newVars[$dk] = $dv;
        return $this->generateURL($newVars, $community);
    }

    public function generateSpecialURL($vars, $base) {
        //print_r($vars);
        $params = '';
        if ($vars['facet']) {
            foreach ($vars['facet'] as $facet) {
                $params .= '&facet[]=' . $facet;
            }
        }
        if ($vars['filter']) {
            foreach ($vars['filter'] as $filter) {
                $params .= '&filter[]=' . $filter;
            }
        }
        if ($vars['parent'] && $vars['child']) {
            $params .= '&parent=' . $vars['parent'] . '&child=' . $vars['child'];
        }
        if ($vars['exclude']) {
            $params .= '&exclude=' . $vars['exclude'];
        }
        if ($vars['include']) {
            $params .= '&include=' . $vars['include'];
        }
        $url = $base;

        if ($vars['subcategory']) {
            $url .= '/' . $vars['subcategory'];
        }
        if ($vars['nif']) {
            $url .= '/source/' . $vars['nif'];
        }
        if (!isset($vars['page']) || $vars['page'] != 1) {
            $params .= '&page=' . $vars['page'];
        }
        if ($vars['sort']) {
            $params .= '&sort=' . $vars['sort'];
        }
        if ($vars['preference']) {
            $params .= '&preference=' . $vars['preference'];
        }
        if (isset($vars['column']) && isset($vars['sort'])) {
            $params .= '&column=' . $vars['column'] . '&sort=' . $vars['sort'];
        }
        $url .= '?q=' . $vars['q'] . '&l=' . $vars['l'] . $params;
        return $url;
    }

    public function paginate($vars) {
        $html = '<div class="text-left">';
        $html .= '<ul class="pagination">';
        $newVars = $vars;
        $newVars['page'] = $this->page - 1;

        if ($this->page > 1)
            $html .= '<li><a href="' . $this->generateURL($newVars) . '">«</a></li>';
        else
            $html .= '<li><a href="javascript:void(0)">«</a></li>';

        if ($this->page - 1 > 0) {
            $start = $this->page - 1;
        } else
            $start = 1;
        if ($this->page + 1 < $this->paginatePages) {
            $end = $this->page + 2;
        } else
            $end = $this->paginatePages;

        if ($start > 1) {
            $html .= '<li><a href="javascript:void(0)">..</a></li>';
        }

        for ($i = $start; $i < $end; $i++) {
            $newVars = $vars;
            $newVars['page'] = $i;

            if ($i == $this->page) {
                $html .= '<li class="active"><a href="javascript:void(0)">' . $i . '</a></li>';
            } else {
                $html .= '<li><a href="' . $this->generateURL($newVars) . '">' . $i . '</a></li>';
            }
        }

        if ($end < $this->paginatePages) {
            $html .= '<li><a href="javascript:void(0)">..</a></li>';
        }

        $newVars = $vars;
        $newVars['page'] = $this->page + 2;
        if ($this->page < $this->paginatePages)
            $html .= '<li><a href="' . $this->generateURL($newVars) . '">»</a></li>';
        else
            $html .= '<li><a href="javascript:void(0)">»</a></li>';


        $html .= '</ul></div>';

        return $html;
    }

    public function paginateLong($vars, $class=NULL, $count=NULL, $per_page=NULL) {
        if($vars["type"] == "interlex") $this->page = $vars["page"];
        if($count !== NULL && $per_page) {
            $paginated_pages = (int) ceil($count / $per_page);
        } else {
            $paginated_pages = $this->paginatePages;
        }
        if($paginated_pages == 0) {
            return "";
        }
        if(!is_null($class)) $class_string = 'class="' . $class . '"';
        $html = '<div class="text-left">';
        $html .= '<ul class="pagination">';
        $newVars = $vars;
        $newVars['page'] = $this->page - 1;
        $max_page = $paginated_pages < self::MAX_PAGE ? $paginated_pages : self::MAX_PAGE;

        if ($this->page > 1)
            $html .= '<li><a ' . $class_string . ' href="' . $this->generateURL($newVars) . '">«</a></li>';
        else
            $html .= '<li><a href="javascript:void(0)">«</a></li>';

        if ($this->page - 3 > 0) {
            $start = $this->page - 3;
        } else
            $start = 1;
        if ($this->page + 3 < $max_page) {
            $end = $this->page + 3;
        } else
            $end = $max_page;

        if ($start > 2) {
            $newVars = $vars;
            $newVars['page'] = 1;
            $html .= '<li><a ' . $class_string . ' href="' . $this->generateURL($newVars) . '">1</a></li>';
            $newVars['page'] = 2;
            $html .= '<li><a ' . $class_string . ' href="' . $this->generateURL($newVars) . '">2</a></li>';
            $html .= '<li><a href="javascript:void(0)">..</a></li>';
        }

        for ($i = $start; $i <= $end; $i++) {
            $newVars = $vars;
            $newVars['page'] = $i;

            if ($i == $this->page) {
                $html .= '<li class="active"><a ' . $class_string . ' href="javascript:void(0)">' . number_format($i) . '</a></li>';
            } else {
                $html .= '<li><a ' . $class_string . ' href="' . $this->generateURL($newVars) . '">' . number_format($i) . '</a></li>';
            }
        }

        $newVars = $vars;
        $newVars['page'] = $this->page + 1;
        if ($this->page < $max_page)
            $html .= '<li><a ' . $class_string . ' href="' . $this->generateURL($newVars) . '">»</a></li>';
        else
            $html .= '<li><a href="javascript:void(0)">»</a></li>';


        $html .= '</ul></div>';

        return $html;
    }

    public function getNoSubscriptionResultText($vars, $modified_time) {
        $new_filter = Array();
        if(isset($vars["filter"])) {
            foreach($vars["filter"] as $filter) {
                if($filter !== "v_lastmodified_epoch:>" . (string) $modified_time && $filter !== "v_status:N") $new_filter[] = $filter;
            }
            $vars["filter"] = $new_filter;
        }
        $html = "";
        $html .= '<p style="text-transform: none;padding:10px;font-size:18px;">There doesn\'t appear to be recent updates to your saved search: <b>' . $vars['q'] . '</b>. Would you like to search again with all results?</p>';
        $html .= '<p>';
        $html .= ' <a class="btn-u btn-u-sea" href="' . $this->generateURL($vars) . '">Search Again</a>';
        $html .= '</p>';
        return $html;
    }

    public function getResultText($type, $args, $expansion, $vars) {
        $html = '';
        $display_query = isset($vars["l"]) ? $vars["l"] : $vars["q"];
        $display_query = \helper\sanitizeHTMLString($display_query);
        if(!$display_query) $display_query = "*";
        if ($type == 'resource') {
            if ($args[1] == 0) {
                if($vars["community"]->portalName == "resources") {
                    $skip_extra = true;
                } else {
                    $skip_extra = false;
                }
                $html .= '<div class="alert alert-warning fade in text-center">
                            <h4 style="text-transform: none;font-size:24px">Please Search Again!</h4>';
                if(!is_null($args[3])) {
                    $html .= $this->getNoSubscriptionResultText($vars, $args[4]);
                } else {
                    $html .= '<p style="text-transform: none;padding:10px;font-size:18px;">We could not find any records in this particular category for your search: <b>' . $display_query . '</b>.';
                    if(!$skip_extra) {
                        $html .= ' We recommend you try in another category or try in ' . Community::getSearchNameMoreResources($this->community) . ' or ' . Community::getSearchNameLiterature($this->community) . '.';
                    }
                    $html .= '</p><p>';

                    $newVars = $vars;
                    $newVars['subcategory'] = false;
                    $newVars['nif'] = false;

                    if ($vars['category'] != 'Any') {
                        $newVars['category'] = 'Any';
                        $html .= '<a class="btn-u" href="' . $this->generateURL($newVars) . '">Try All Categories</a> ';
                    }
                    if(!$skip_extra) {
                        $newVars['category'] = 'data';
                        $html .= '<a class="btn-u btn-u-red" href="' . $this->generateURL($newVars) . '">Try ' . Community::getSearchNameMoreResources($this->community) . '</a>';

                        $newVars['category'] = 'literature';
                        $html .= ' <a class="btn-u btn-u-sea" href="' . $this->generateURL($newVars) . '">Try ' . Community::getSearchNameLiterature($this->community) . '</a>';
                    }

                    $html .= ' <a class="btn-u btn-u-purple" href="/'.$this->community->portalName.'/about/resource">Add A Resource</a>';
                    $html .= '</p>';
                }
                $html .= '</div>';
                return $html;
            } else {
                $sources_text = "";
                if(!$args[2]) {
                    $sources_text = ' from <a href="#sources-list">' . number_format($args[2]) . ' sources</a>';
                }
                $html .= 'on page ' . $this->page . ' showing ' . number_format($args[0]) . ' out of ' . number_format($args[1]) . ' results' . $sources_text;
            }
        } elseif ($type == 'data') {
            if ($args[0] == 0) {
                $html .= '<div class="alert alert-warning fade in text-center">
                            <h4 style="text-transform: none;font-size:24px">Please Search Again!</h4>';
                if(!is_null($args[2])) {
                    $html .= $this->getNoSubscriptionResultText($vars, $args[3]);
                } else {
                    $html .= '<p style="text-transform: none;padding:10px;font-size:18px;">We could not find any data for your search: <b>' . $display_query . '</b>. Would you like to search the literature?</p>';
                    $html .= '<p>';

                    $newVars = $vars;
                    $newVars['subcategory'] = false;
                    $newVars['nif'] = false;
                    $newVars['category'] = 'literature';

                    $html .= ' <a class="btn-u btn-u-sea" href="' . $this->generateURL($newVars) . '">Try ' . Community::getSearchNameLiterature($this->community) . '</a>';
                    $html .= '</p>';
                }
                $html .= "</div>";
                return $html;
            } else
                $html .= 'showing <span class="data-number" data="' . number_format($args[0]) . '">' . number_format($args[0]) . '</span> results across <span data="' . number_format($args[1]) . '" class="source-number">' . number_format($args[1]) . '</span> data source(s)';
        } elseif ($type == 'literature') {
            if ($args[0] == 0) {

                $html .= '<div class="alert alert-warning fade in text-center">
                            <h4 style="text-transform: none;font-size:24px">Please Search Again!</h4>';
                $html .= '<p style="text-transform: none;padding:10px;font-size:18px;">We could not find any papers for your search: <b>' . $display_query . '</b>. We recommend you try
                              your search across the ' . Community::getSearchNameCommResources($this->community) . ' or ' . Community::getSearchNameMoreResources($this->community) . '.</p>';
                $html .= '<p>';

                $newVars = $vars;
                $newVars['subcategory'] = false;
                $newVars['nif'] = false;

                $newVars['category'] = 'Any';
                $html .= '<a class="btn-u btn-u-red" href="' . $this->generateURL($newVars) . '">Try ' . Community::getSearchNameCommResources($this->community) . '</a>';

                $newVars['category'] = 'data';
                $html .= ' <a class="btn-u btn-u-sea" href="' . $this->generateURL($newVars) . '">Try ' . Community::getSearchNameMoreResources($this->community) . '</a>';
                $html .= '</p>
                        </div>';
                return $html;
            } else {
                $html .= $this->getExpansionResultText($expansion, $vars)."<br>";
                $html .= "<h4>";
                if ($args[0] < $this->page * $this->per_page)
                    $html .= 'showing ' . number_format(($this->page - 1) * $this->per_page + 1) . ' - ' . number_format($args[0]) . ' papers out of ' . number_format($args[0]) . ' papers';
                else
                    $html .= 'showing ' . number_format(($this->page - 1) * $this->per_page + 1) . ' - ' . number_format($this->page * $this->per_page) . ' papers out of ' . number_format($args[0]) . ' papers';
                $html .= "</h4>";
                //$html .= $this->getExpansionResultText($expansion, $vars);
            }
        } elseif ($type == 'table') {
          ## remove no search results table view -- Vicky-2018-11-21
            /*if ($args[0] == 0) {
                $html .= '<div class="alert alert-warning fade in text-center"> <h4 style="text-transform: none;font-size:24px">Please Search Again!</h4>';
                if(!is_null($args[1])) {
                    $html .= $this->getNoSubscriptionResultText($vars, $args[2]);
                } else {
                    $strings = Array();
                    if($this->community->id == 0) {
                        $strings["resources"] = "Resources";
                        $strings["data"] = "Data";
                        $strings["lit"] = "Literature";
                    } else {
                        $strings["resources"] = Community::getSearchNameCommResources($this->community);
                        $strings["data"] = Community::getSearchNameMoreResources($this->community);
                        $strings["lit"] = Community::getSearchNameLiterature($this->community);
                    }
                    $html .= '<p style="text-transform: none;padding:10px;font-size:18px;">We could not find any records in this particular source for your search: <b>' . $display_query . '</b>. We recommend you try your search across all ' . Community::getSearchNameCommResources($this->community) . ', or ' . Community::getSearchNameMoreResources($this->community) . ', or ' . Community::getSearchNameLiterature($this->community) . '.</p>';
                    $html .= '<p>';

                    $newVars = $vars;
                    $newVars['subcategory'] = false;
                    $newVars['nif'] = $this->community->id == 0 ? 'nlx_144509-1' : false;

                    $newVars['category'] = 'Any';
                    $html .= '<a class="btn-u" href="' . $this->generateURL($newVars) . '">Try ' . $strings["resources"] . '</a> ';

                    $newVars['nif'] = false;
                    $newVars['category'] = 'data';
                    $html .= '<a class="btn-u btn-u-red" href="' . $this->generateURL($newVars) . '">Try ' . $strings["data"] . '</a>';

                    $newVars['category'] = 'literature';
                    $html .= ' <a class="btn-u btn-u-sea" href="' . $this->generateURL($newVars) . '">Try ' . $strings["lit"] . '</a>';

                    $html .= ' <a class="btn-u btn-u-purple" href="/'.$this->community->portalName.'/about/resource">Or Add A Resource</a>';

                    $html .= '</p>';
                }
                $html .= '</div>';
                return $html;
            } else*/
            if ($args[0] != 0)
                $html .= number_format($args[0]) . ' Results';
        }
        return $html;
    }

    public function getExpansionResultText($expansion, $vars) {
        $html = "";
        if ($expansion['hasExpo'] || $this->exclude) {
            $expansion_array = Array();
            foreach ($expansion['query'] as $array) {
                if (count($array['expansion']) > 0) {
                    $single_expansion_html = "Your term <strong>" . $array["label"] . "</strong> also searched for ";
                    $single_expansion_html .= implode(", ", array_map(function($a) { return "<strong>" . $a . "</strong>"; }, $array["expansion"]));
                    $expansion_array[] = $single_expansion_html;
                }
            }
            $expansion_html = '<div class="truncate-medium">';

            if(!empty($expansion_array)) {
                $expansion_html .= implode(". ", $expansion_array);
            }
            $expansion_html .= "</div>";
            if($vars["category"] != "data") {
                $expansion_html .= "<div>";
                if ($this->exclude && $this->exclude == 'synonyms') {
                    $newVars = $vars;
                    $newVars['exclude'] = null;
                    $expansion_html .= '<a href="' . $this->generateURL($newVars) . '" target="_self"><i class="fa fa-square-o"></i> Synonyms</a> | ';
                } else {
                    $newVars = $vars;
                    $newVars['exclude'] = 'synonyms';
                    $expansion_html .= '<a href="' . $this->generateURL($newVars) . '" target="_self"><i class="fa fa-check-square-o"></i> Synonyms</a> | ';
                }
                if ($this->include && $this->include == 'acronyms') {
                    $newVars = $vars;
                    $newVars['include'] = null;
                    $expansion_html .= '<a href="' . $this->generateURL($newVars) . '" target="_self"><i class="fa fa-check-square-o"></i> Acronyms</a> ';
                } else {
                    $newVars = $vars;
                    $newVars['include'] = 'acronyms';
                    $expansion_html .= '<a href="' . $this->generateURL($newVars) . '" target="_self"><i class="fa fa-square-o"></i> Acronyms</a> ';
                }
                $expansion_html .= "</div>";
            }
            $html .= $expansion_html;
        }
        return $html;
    }

    public function buildFacetFromURL($url, &$finalArray){
        // load the xml from the url string into an xml object
        $xml = $this->checkLocalStore($url);
        if(!$xml){
            $xml_all = Connection::multi(Array($url));
            $xml = $xml_all[0];
            $this->insertIntoLocalStore($url, $xml);
        }
        $facetXML = simplexml_load_string($xml);

        // parse the xml
        if ($facetXML) {
            foreach ($facetXML->facets as $facets) {
                foreach ($facets->facets as $facet) {
                    $finalArray['facets'][(string)$facets['category']][] = array('value' => (string)$facet, 'count' => (int)$facet['count']);
                    $tree[(string)$facets['category']][] = array('name' => (string)$facet, 'size' => (int)$facet['count']);
                }
            }
            foreach($tree as $category=>$array){
                $level2 = array();
                foreach($array as $arr){
                    $newVars = $this->vars;
                    $newVars['facet'][] = rawurlencode($category).':'.rawurlencode($arr['name']);
                    $level2[] = array('name'=>rawurlencode($arr['name']),'size'=>$arr['size'],'url'=>$this->generateURL($newVars));
                }
                $level1[] = array('name'=>rawurlencode($category),'children'=>$level2);
            }
            $finalArray['graph'] = array('name'=>'Facets','children'=>$level1);
        }
    }

    public function buildDataFromURL($url, &$finalArray, $amount){
        // load the xml from the url string into an xml object
        $xml = $this->checkLocalStore($url);
        if(!$xml){
            $xml_all = Connection::multi(Array($url));
            $finalArray["return_status_code"] = self::lowestConnectionStatus($finalArray);
            $xml = $xml_all[0];
            $this->insertIntoLocalStore($url, $xml);
        }
        $dataXML = $this->xmlSingleSourceSearch($xml, $this->source);

        // parse the xml
        if ($dataXML) {
            $finalArray['expansion'] = $this->getQueryInfo($dataXML);
            $finalArray['total'] = (int)$dataXML->result['resultCount'];
            $count = 0;
            foreach ($dataXML->result->results->row as $row) {
                $numCol = 0;
                foreach ($row->data as $data) {
                    $finalArray['table'][$count][(string)$data->name] = (string)$data->value;
                    $numCol++;
                }
                $count++;
            }
            if (SchemaGeneratorRegistryXML::isRegistry($dataXML)) {
                $finalArray['schemas'] = SchemaGeneratorRegistryXML::generate($dataXML);
            } else {
                $schemasExtended = SchemaGeneratorSources::genreatePropertyValueTable($finalArray['table'], $this->community->portalName, $this->source);
                $finalArray['schemas'] = $schemasExtended['schemas'];
                $finalArray['schemas_extras'] = $schemasExtended['schemas_extras'];
            }
            $this->paginatePages = ceil((int)$dataXML->result['resultCount'] / $amount);
        }
    }

    public function buildFoundURL($source, $params){
        $base_url = Connection::environment() . "/v1/federation/data/" . $source . ".xml?orMultiFacets=true";
        $facets_filters = $params;
        $url_tree = $this->community->urlTree;
        $found = false;
        $param_types = $this->getParamsTypes($params);

        if ($this->subcategory) {   // if there's a subcategory
            foreach ($url_tree[$this->category]['subcategories'][$this->subcategory]['nif'] as $i => $nif) {
                if ($nif == $source) {
                    $found = true;
                    $facets_filters .= $this->checkFilterFacets($url_tree[$this->category]['subcategories'][$this->subcategory]['objects'][$i]->filter, $param_types);
                    $facets_filters .= $this->checkFilterFacets($url_tree[$this->category]['subcategories'][$this->subcategory]['objects'][$i]->facet, $param_types);
                }
            }
        }
        if(!$found) {   // if there is no subcategory,  search category
            $category = $url_tree[$this->category];
            foreach ($category['nif'] as $i => $nif){
                if ($nif == $source) {
                    $found = true;
                    $facets_filters .= $this->checkFilterFacets($category['objects'][$i]->filter, $params_types);
                    $facets_filters .= $this->checkFilterFacets($category['objects'][$i]->facet, $param_types);
                }
            }
            foreach($category['subcategories'] as $i => $subcat){   // and that categories subcategories
                foreach($subcat['nif'] as $j => $subnif){
                    if($subnif == $source){
                        $found = true;
                        $facets_filters .= $this->checkFilterFacets($subcat['objects'][$j]->filter, $param_types);
                        $facets_filters .= $this->checkFilterFacets($subcat['objects'][$j]->facet, $param_types);
                    }
                }
            }
        }
        if(!$found){    // this condition will only be met in the resource view (not table view) when query is found in a single subcategory source
            foreach($url_tree[$this->category]['subcategories'] as $subname => $subvals){
                foreach($subvals['nif'] as $i => $nif){
                    if($nif == $source){
                        $found = true;
                        $facets_filters .= $this->checkFilterFacets($subvals['objects'][$i]->filter, $param_types);
                        $facets_filters .= $this->checkFilterFacets($subvals['objects'][$i]->facet, $param_types);
                    }
                }
            }
        }
        if(!$found){    // catch all, searches every category and subcategory if all else fails
            foreach($url_tree as $i => $category){
                foreach($category['nif'] as $j => $nif){
                    if($nif == $source){
                        $found = true;
                        $facets_filters .= $this->checkFilterFacets($category['objects'][$j]->filter, $param_types);
                        $facets_filters .= $this->checkFilterFacets($category['objects'][$j]->facet, $param_types);
                    }
                }
                if($category['subcategories']){
                    foreach($category['subcategories'] as $j => $subcategory){
                        foreach($subcategory['nif'] as $k => $nif2){
                            if($nif2 == $source){
                                $found = true;
                                $facets_filters .= $this->checkFilterFacets($subcategory['objects'][$k]->filter, $param_types);
                                $facets_filters .= $this->checkFilterFacets($subcategory['objects'][$k]->facet, $param_types);
                            }
                        }
                    }
                }
            }
        }
        if(!$found) return NULL;
        $full_url = $base_url . $facets_filters;
        return $full_url;
    }

    private function getParamsTypes($params){
        // returns a list of params that are already in the request url, therefore cannot be used again
        $params = explode("&", $params);
        $return_params = Array();
        foreach($params as $param){
            $param_split = preg_split("/(:|%3A)/", $param);
            if(!in_array($param_split[0], $return_params) && $param_split[0] != ""){
                array_push($return_params, $param_split[0]);
            }
        }
        return $return_params;
    }

    private function checkFilterFacets($ff, $param_types){
        $ff_split = preg_split("/(:|%3A)/", $ff);
        $ff_type = $ff_split[0];
        if($ff_type[0] == "&") $ff_type = substr($ff_type, 1);  // remove ampersand
        if(in_array($ff_type, $param_types)) return "";
        return $ff;
    }

    private function xmlSingleSourceSearch($file, $nifid) {
        $xml = simplexml_load_string($file);
        if(!$xml) return NULL;
        $found = false;
        if(!isset($xml->result->attributes()->resultCount) || (int) $xml->result->attributes()->resultCount == 0) return $xml;
        foreach($this->filter as $filter) {
            if(\helper\startsWith($filter, "v_lastmodified_epoch:>")) {
                $found = true;
                break;
            }
        }
        if(!$found) return $xml;
        $data_rows = $xml->result->results->row;
        foreach($data_rows->data as $dr) {
            if(strpos(file_get_contents(__DIR__ . "/../vars/missing-epochs.txt"), $nifid) === false) return $xml;
        }
        return NULL;
    }

    private static function lowestConnectionStatus($finalArray) {
        if(!isset($finalArray["return_status_code"])) return Connection::$lastHttpStatusCodeMin;
        return min(Connection::$lastHttpStatusCodeMin, $finalArray["return_status_code"]);
    }

    public static function newRecentSearch($vars, $cid) {
        if(!$_SESSION) return;
        if(!isset($_SESSION["recent-searches"])) {
            $_SESSION["recent-searches"] = Array();
        }
        $vars_hash = self::recentSearchHash($vars, $cid);
        unset($vars["community"]);
        $_SESSION["recent-searches"][$vars_hash] = Array("timestamp" => time(), "vars" => $vars, "cid" => $cid);
        if(count($_SESSION["recent-searches"]) > 5) {
            $earliest_time = MAXINT;
            $earliest_hash = NULL;
            foreach($_SESSION["recent-searches"] as $hash => $val) {
                if($val["timestamp"] < $earliest_time) {
                    $earliest_time = $val["timestamp"];
                    $earliest_hash = $hash;
                }
            }
            if($earliest_hash) {
                unset($_SESSION["recent-searches"][$earliest_hash]);
            }
        }
    }

    public static function recentSearchHash($vars, $cid) {
        $cat = "";
        $cat .= $cid;
        $cat .= $vars["category"];
        $cat .= $vars["subcategory"];
        $cat .= $vars["nif"];
        $cat .= $vars["q"];
        $hash = crc32($cat);
        return $hash;
    }

    public static function getViewFilters($view) {
        if(isset(self::$view_id_fields[$view])) {
            return self::$view_id_fields[$view]["filters"];
        }
        return NULL;
    }
}

?>
