<?php

class CommunityDatasetTemplate extends DBObject {
    static protected $_table = "community_dataset_templates";
    static protected $_table_fields = Array("id", "cid", "dataset_fields_template_id", "timestamp");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iiii";

    private $id;
        public function _get_id() { return $this->id; }
        public function _set_id($val) { if(is_null($this->id)) $this->id = $val; }
    private $cid;
        public function _get_cid() { return $this->cid; }
        public function _set_cid($val) { if(is_null($this->cid)) $this->cid = $val; }
    private $dataset_fields_template_id;
        public function _get_dataset_fields_template_id() { return $this->dataset_fields_template_id; }
        public function _set_dataset_fields_template_id($val) { if(is_null($this->dataset_fields_template_id)) $this->dataset_fields_template_id = $val; }
    private $timestamp;
        public function _get_timestamp() { return $this->timestamp; }
        public function _set_timestamp($val) { if(is_null($this->timestamp)) $this->timestamp = $val; }

    private $_dataset_field_template;

    static public function createNewObj(Community $community, DatasetFieldTemplate $dataset_field_template) {
        if(!$dataset_field_template->id) return NULL;
        $timestamp = time();
        $obj = new CommunityDatasetTemplate(Array(
            "id" => NULL,
            "cid" => $community->id,
            "dataset_fields_template_id" => $dataset_field_template->id,
            "timestamp" => $timestamp,
        ));
        self::insertObj($obj);

        return $obj;
    }

    static public function deleteObj($obj) {
        $cxn = new Connection();
        $cxn->connect();
        $cxn->delete(self::$_table, "i", Array($obj->id), "where id=?");
        $cxn->close();
    }

    public function arrayForm() {
        return Array(
            "id" => $this->id,
            "dataset_field_template" => $this->datasetFieldTemplate()->arrayForm(),
            "timestamp" => $this->timestamp,
        );
    }

    public function datasetTemplate() {
        if(is_null($this->_dataset_field_template)) {
            $this->refreshDatasetFieldTemplate();
        }
        return $this->_dataset_field_template;
    }

    public  function refreshDatasetFieldTemplate() {
        $this->_dataset_field_template = DatasetFieldTemplate::loadBy(Array("id"), Array($this->dataset_fields_template_id));
    }
}

?>
