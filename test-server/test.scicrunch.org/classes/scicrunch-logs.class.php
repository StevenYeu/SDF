<?php

class ScicrunchLogs extends DBObject {
    static protected $_table = "scicrunch_logs";
    static protected $_table_fields = Array("id", "entity_id", "entity", "cid", "uid", "type", "log_date", "url");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iisiisis";

    private $id;
        public function _get_id(){ return $this->id; }
        public function _set_id($val){ if(is_null($this->id)) $this->id = $val; }
    private $entity_id;
        public function _get_entity_id(){ return $this->entity_id; }
        public function _set_entity_id($val){ if(is_null($this->entity_id)) $this->entity_id = $val; }
    private $entity;
        public function _get_entity(){ return $this->entity; }
        public function _set_entity($val){ if(is_null($this->entity)) $this->entity = $val; }
    private $cid;
        public function _get_cid(){ return $this->cid; }
        public function _set_cid($val){ if(is_null($this->cid)) $this->cid = $val; }
    private $uid;
        public function _get_uid(){ return $this->uid; }
        public function _set_uid($val){ if(is_null($this->uid)) $this->uid = $val; }
    private $type;
        public function _get_type(){ return $this->type; }
        public function _set_type($val){ if(is_null($this->type)) $this->type = $val; }
    private $log_date;
        public function _get_log_date(){ return $this->log_date; }
        public function _set_log_date($val){ if(is_null($this->log_date)) $this->log_date = $val; }
    private $url;
        public function _get_url(){ return $this->url; }
        public function _set_url($val){ if(is_null($this->url)) $this->url = $val; }

    static public function createNewObj($cid, $uid, $entity_id, $entity, $type, $url) {
        if(is_null($cid) || !$entity_id || !$entity || !$type || !$url) return NULL;

        $timestamp = time();

        $obj = new ScicrunchLogs(Array(
            "id" => NULL,
            "entity_id" => $entity_id,
            "entity" => $entity,
            "cid" => $cid,
            "uid" => $uid,
            "type" => $type,
            "log_date" => $timestamp,
            "url" => $url
        ));
        ScicrunchLogs::insertObj($obj);

        return $obj;
    }
}

?>
