<?php

class RRIDFailureLog extends DBObject {
    static protected $_table = "rrid_failure_log";
    static protected $_table_fields = Array("id", "timestamp", "queried_id", "searched_id", "referer", "request_ip");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iissss";

    private $id;
        public function _get_id(){ return $this->id; }
        public function _set_id($val){ if(is_null($this->id)) $this->id = $val; }
    private $timestamp;
        public function _get_timestamp(){ return $this->timestamp; }
        public function _set_timestamp($val){ $this->timestamp = $val; }
    private $queried_id;
        public function _get_queried_id(){ return $this->queried_id; }
        public function _set_queried_id($val){ return $this->queried_id = $val; }
    private $searched_id;
        public function _get_searched_id(){ return $this->searched_id; }
        public function _set_searched_id($val){ $this->searched_id = $val; }
    private $referer;
        public function _get_referer(){ return $this->referer; }
        public function _set_referer($val){ $this->referer = $val; }
    private $request_ip;
        public function _get_request_ip(){ return $this->request_ip; }
        public function _set_request_ip($val){ $this->request_ip = $val; }

    static public function createNewObj($queried_id, $searched_id, $referer, $request_ip){
        $time = time();
        $log = new RRIDFailureLog(Array(
            "id" => NULL,
            "timestamp" => $time,
            "queried_id" => $queried_id,
            "searched_id" => $searched_id,
            "referer" => $referer,
            "request_ip" => $request_ip,
        ));
        RRIDFailureLog::insertObj($log);
        return $log;
    }

    static public function deleteObj(){

    }

    public function arrayForm(){
        return Array(
            "time" => $this->timestamp,
            "queried_id" => $this->queried_id,
            "searched_id" => $this->searched_id,
            "referer" => $this->referer
        );
    }
}

?>
