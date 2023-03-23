<?php

class SearchFederationFailureLog extends DBObject {
    static protected $_table = "search_federation_failure_log";
    static protected $_table_fields = Array("id", "timestamp", "query", "cid", "category", "subcategory", "source", "query_uri", "referer", "request_ip", "status_code", "result_count");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iisissssssii";

    private $id;
        public function _get_id(){ return $this->id; }
        public function _set_id($val){ if(is_null($this->id)) $this->id = $val; }
    private $timestamp;
        public function _get_timestamp(){ return $this->timestamp; }
        public function _set_timestamp($val){ if(is_null($this->timestamp)) $this->timestamp = $val; }
    private $query;
        public function _get_query(){ return $this->query; }
        public function _set_query($val){ if(is_null($this->query)) $this->query = $val; }
    private $cid;
        public function _get_cid(){ return $this->cid; }
        public function _set_cid($val){ if(is_null($this->cid)) $this->cid = $val; }
    private $category;
        public function _get_category(){ return $this->category; }
        public function _set_category($val){ if(is_null($this->category)) $this->category = $val; }
    private $subcategory;
        public function _get_subcategory(){ return $this->subcategory; }
        public function _set_subcategory($val){ if(is_null($this->subcategory)) $this->subcategory = $val; }
    private $source;
        public function _get_source(){ return $this->source; }
        public function _set_source($val){ if(is_null($this->source)) $this->source = $val; }
    private $query_uri;
        public function _get_query_uri(){ return $this->query_uri; }
        public function _set_query_uri($val){ if(is_null($this->query_uri)) $this->query_uri = $val; }
    private $referer;
        public function _get_referer(){ return $this->referer; }
        public function _set_referer($val){ if(is_null($this->referer)) $this->referer = $val; }
    private $request_ip;
        public function _get_request_ip(){ return $this->request_ip; }
        public function _set_request_ip($val){ if(is_null($this->request_ip)) $this->request_ip = $val; }
    private $status_code;
        public function _get_status_code(){ return $this->status_code; }
        public function _set_status_code($val){ $this->status_code = $val; }
    private $result_count;
        public function _get_result_count(){ return $this->result_count; }
        public function _set_result_count($val){ $this->result_count = $val; }

    static public function createNewObj($query, $query_uri, $referer, $request_ip, $cid=NULL, $category=NULL, $subcategory=NULL, $source=NULL, $status_code = NULL, $result_count = NULL){
        $time = time();
        $log = new SearchFederationFailureLog(Array(
            "id" => NULL,
            "timestamp" => $time,
            "query" => $query,
            "cid" => $cid,
            "category" => $category,
            "subcategory" => $subcategory,
            "source" => $source,
            "query_uri" => $query_uri,
            "referer" => $referer,
            "request_ip" => $request_ip,
            "status_code" => $status_code,
            "result_count" => $result_count,
        ));
        SearchFederationFailureLog::insertObj($log);
        return $log;
    }

    public function arrayForm(){
        return Array(
            "time" => $this->timestamp,
            "query" => $this->query,
            "cid" => $this->cid,
            "category" => $this->category,
            "subcategory" => $this->subcategory,
            "source" => $this->source,
            "query_uri" => $this->query_uri,
            "referer" => $this->referer,
            "status_code" => $this->status_code,
            "result_count" => $this->result_count,
        );
    }
}

?>
