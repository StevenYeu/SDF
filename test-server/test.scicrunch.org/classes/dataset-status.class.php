<?php

class DatasetStatus extends DBObject {
    static protected $_table = "dataset_status";
    static protected $_table_fields = Array("id", "dataset_id", "status", "status_date");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iisi";

    private $id;
        public function _get_id(){ return $this->id; }
        public function _set_id($val){ if(is_null($this->id)) $this->id = $val; }
    private $dataset_id;
        public function _get_dataset_id(){ return $this->dataset_id; }
        public function _set_dataset_id($val){ if(is_null($this->dataset_id)) $this->dataset_id = $val; }
    private $status;
        public function _get_status(){ return $this->status; }
        public function _set_status($val){ if(is_null($this->status)) $this->status = $val; }
    private $status_date;
        public function _get_status_date(){ return $this->status_date; }
        public function _set_status_date($val){ if(is_null($this->status_date)) $this->status_date = $val; }

    static public function createNewObj($dataset_id, $status) {
    //    if(!$dataset_id || !$status) return NULL;

        $timestamp = time();

        $obj = new DatasetStatus(Array(
            "id" => NULL,
            "dataset_id" => $dataset_id,
            "status" => $status,
            "status_date" => $timestamp,
        ));
        DatasetStatus::insertObj($obj);

        return $obj;
    }
}

?>
