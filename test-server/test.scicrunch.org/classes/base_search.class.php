<?php

include $GLOBALS["DOCUMENT_ROOT"] . '/classes/base_search.strategies.class.php';

class BaseSearch extends Connection {

    public $id;
    public $paginatePages = 0;
    public $allSources;
    public $vars;

    private $build_url_method;
    private $reshape_method;
    private $method;

    public function __construct($vars){
        $this->create($vars);
    }

    public function create($vars) {
        if(!isset($vars['q']) || $vars['q'] == "") $vars['q'] = "*";
        else $vars['q'] = rawurlencode(html_entity_decode($vars['q']));

        $this->vars = $vars;
    }

    public function doSearch($method){
        $this->setStrategy($method);
        $search_url = $this->build_url_method->buildURL($this->vars);
        $xml = $this->getXML($search_url);
        $results = $this->reshape_method->parseXML($xml['data_xml'], $xml['raw_xml'], $this->vars);
        return $results;
    }

    private function setStrategy($method){
        $this->method = $method;
        switch($method){
            case "summary":
                $this->build_url_method = new StrategyURLSummary();
                $this->reshape_method = new StrategyXMLSummary();
                break;
            case "single_source":
                $this->build_url_method = new StrategyURLSingleSource();
                $this->reshape_method = new StrategyXMLSingleSource();
                $this->vars['_type'] = 'data';
                break;
            case "single_source_facets":
                $this->build_url_method = new StrategyURLSingleSource();
                $this->reshape_method = new StrategyXMLSingleSourceFacets();
                $this->vars['_type'] = 'facets';
                break;
            case "literature":
                $this->build_url_method = new StrategyURLLiterature();
                $this->reshape_method = new StrategyXMLLiterature();
                break;
            default:
                throw new Exception("Bad search method given");
        }
    }

    private function getXML($search_url){
        $xml_all = Connection::multi(Array($search_url));
        $xml_all = $xml_all[0];
        $xml_all = simplexml_load_string($xml_all);
        $data_xml = BaseSearch::xml2array($xml_all);
        return Array("data_xml" => $data_xml, "raw_xml" => $xml_all);
    }

    private function checkLocalStore($url) {
        return NULL;    // disable
        $this->connect();
        $return = $this->select('search_data', array('xml'), 's', array($url), 'where url=?');
        $this->close();

        if (count($return) > 0) { 
            return $return[0]['xml'];
        } else {
            return NULL;
        }    
    }

    private function insertIntoLocalStore($url, &$xml) {
        $this->connect();
        $this->insert('search_data', 'iss', array(null, $url, $xml));
        $this->close();
    }

    static public function xml2array($xmlObject, $out = array()){
            foreach ( (array) $xmlObject as $index => $node )
                $out[$index] = ( is_object ( $node ) ||  is_array ( $node ) ) ? BaseSearch::xml2array ( $node ) : $node;
    
            return $out;
    } 
}
?>
