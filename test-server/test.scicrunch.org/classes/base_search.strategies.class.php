<?php

interface StrategyURL{
    public function buildURL(&$vars);
}
interface StrategyXML{
    public function parseXML(&$xml, &$xml_raw, &$vars);
}

/******************************************************************************************************************************************************************************************************/
/******************************************************************************************************************************************************************************************************/

class StrategyURLSummary implements StrategyURL{
    public function buildURL(&$vars){
        // q - query
        $query = $vars["q"];
        $query_url = Connection::environment() . "/v1/federation/search.xml?q=" . $query;
        return $query_url;
    }
}

/******************************************************************************************************************************************************************************************************/

class StrategyXMLSummary implements StrategyXML{
    public function parseXML(&$xml, &$xml_raw, &$vars){
        $categories = $this->reshapeCategories($xml);
        $sources = $this->reshapeSources($xml);
        $sources = $this->collapseSources($sources);
        $total_count = $xml['result']['@attributes']['total'];
        $results = Array('categories' => $categories, 'sources' => $sources, 'totalCount' => $total_count);
        return $results;
    }

    private function reshapeCategories(&$data_xml){
        $categories = $data_xml['result']['categories']['category'];
        $reshape = Array();
        foreach($categories as $cat){
            $attributes = $cat['@attributes'];
            $new_cat = Array('count' => $cat['count']);
            foreach($attributes as $idx => $att) $new_cat[$idx] = $att;
            array_push($reshape, $new_cat);
        }
        return $reshape;
    }

    private function reshapeSources(&$data_xml){
        $sources = $data_xml['result']['results']['result'];
        $reshape = Array();
        if(isset($sources['@attributes']) && isset($sources['count']) && isset($sources['totalCount'])){	// this means that there is only one data source
            $new_cat = $this->reshapeSingleSource($sources);
            array_push($reshape, $new_cat);
        }else{																								// multiple data sources
            foreach($sources as $source){
                $new_cat = $this->reshapeSingleSource($source);
                array_push($reshape, $new_cat);
            }
        }
        return $reshape;
    }

    private function reshapeSingleSource(&$source){
        $new_cat = Array('count' => $source['count'], 'totalCount' => $source['totalCount']);
        $attributes = $source['@attributes'];
        foreach($attributes as $idx => $att) $new_cat[$idx] = $att;
        return $new_cat;
    }

    private function collapseSources(&$sources){
        $found_idx = Array();
        $collapsed_sources = Array();
        foreach($sources as $source){
            if(isset($found_idx[$source['nifId']])){	// append found source to categories of existing source
                $idx = $found_idx[$source['nifId']];
                array_push($collapsed_sources[$idx]['category'], $source['category']);
                array_push($collapsed_sources[$idx]['parentCategory'], $source['parentCategory']);
            }else{										// add new source
                 array_push($collapsed_sources, $source);
                 $idx = count($collapsed_sources) - 1;
                 $collapsed_sources[$idx]['category'] = Array($collapsed_sources[$idx]['category']);
                 $collapsed_sources[$idx]['parentCategory'] = Array($collapsed_sources[$idx]['parentCategory']);
                 $found_idx[$source['nifId']] = $idx;
            }
        }
        return $collapsed_sources;
    }
}

/******************************************************************************************************************************************************************************************************/
/******************************************************************************************************************************************************************************************************/

class StrategyURLSingleSource implements StrategyURL{
    public function buildURL(&$vars){
        // source - array of nif ids (only first one is used though)
        // q - query
        // _type - (data or facets)
        $base_url = Connection::environment() . '/v1/federation/%s/%s.xml?q=%s&offset=%d&count=%d&exportType=all';
        $default_results_per_page = 20;
        $count = isset($vars['results_per_page']) && is_numeric($vars['results_per_page']) ? (int) $vars['results_per_page'] : $default_results_per_page;	// default 20
        $offset = isset($vars['page_number']) && is_numeric($vars['page_number']) ? (int) $vars['page_number'] * $count : 0;
        $query_url = sprintf($base_url, $vars['_type'], $vars['source'][0], $vars['q'], $offset, $count);
        if($vars['_type'] == "data") $query_url .= "&exportType=all";
        if(isset($vars['facets'])) $this->addFiltersFacets($query_url, $vars['facets'], "facet");
        if(isset($vars['filters'])) $this->addFiltersFacets($query_url, $vars['filters'], "filter");
        return $query_url;
    }

    private function addFiltersFacets(&$query_url, &$f, $type){
        // each facet much have format: Array("name1" => "value1", ...)
        foreach($f as $name => $val){
            $query_url .= "&" . $type . "=" . $name . ":" . $val;
        }
    }
}

/******************************************************************************************************************************************************************************************************/

class StrategyXMLSingleSource implements StrategyXML{
    public function parseXML(&$xml, &$xml_raw, &$vars){
        $count = $xml["result"]['@attributes']['resultCount'];
        $results = $xml["result"]['results']['row'];
        $results = $this->reshapeResults($results);
        $return_array = Array("count" => $count, "results" => $results);
        return $return_array;
    }

    private function reshapeResults(&$results){
        $new_results = Array();
        if(count($results) == 1){
            array_push($new_results, $this->reshapeSingleResult($results));
        }else{
            foreach($results as $res){
                $data = $this->reshapeSingleResult($res);
                array_push($new_results, $data);
            }
        }
        return $new_results;
    }

    private function reshapeSingleResult(&$res){
        $data = Array();
        foreach($res['data'] as $datum){
            $data[$datum['name']] = $datum['value'];
        }
        return $data;
    }
}

/******************************************************************************************************************************************************************************************************/

class StrategyXMLSingleSourceFacets implements StrategyXML{
    public function parseXML(&$xml, &$xml_raw, &$vars){
        $return_data = Array();
        foreach($xml_raw->facets as $facet){
            $category_name = (string) $facet->attributes()->category;
            $return_data[$category_name] = Array();
            foreach($facet->facets as $single_facet){
                $count = (int) $single_facet->attributes()->count;
                $name = (string) $single_facet;
                array_push($return_data[$category_name], Array("name" => $name, "count" => $count));
            }
        }
        return $return_data;
    }
}

/******************************************************************************************************************************************************************************************************/
/******************************************************************************************************************************************************************************************************/

class StrategyURLLiterature implements StrategyURL{
    public function buildURL(&$vars){
        $base_url = Connection::environment() . '/v1/literature/search.xml?q=%s&offset=%d&count=%d';
        $default_results_per_page = 20;
        $count = isset($vars['results_per_page']) && is_numeric($vars['results_per_page']) ? (int) $vars['results_per_page'] : $default_results_per_page;	// default 20
        $offset = isset($vars['page_number']) && is_numeric($vars['page_number']) ? (int) $vars['page_number'] * $count : 0;
        $query_url = sprintf($base_url, $vars['q'], $offset, $count);
        return $query_url;
    }
}

/******************************************************************************************************************************************************************************************************/

class StrategyXMLLiterature implements StrategyXML{
    public function parseXML(&$xml, &$xml_raw, &$vars){
        $results = Array();
        $results['count'] = $xml['result']['@attributes']['resultCount'];
        $results['publications'] = Array();
        if($results['count'] > 1){
            foreach($xml['result']['publications']['publication'] as $pub){
                array_push($results['publications'], $pub);
            }
        }elseif($results['count'] == 1){
            array_push($results['publications'], $xml['result']['publications']['publication']);
        }
        return $results;
    }
}

?>
