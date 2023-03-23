<?php

class CommunityDataset extends DBObject {
    static protected $_table = "community_datasets";
    static protected $_table_fields = Array("id", "datasetid", "cid", "timestamp", "curated");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iiiis";

    const CURATED_STATUS_PENDING = "pending";
    const CURATED_STATUS_REJECTED = "rejected";
    const CURATED_STATUS_APPROVED = "approved";
    static public $CURATED_STATUSES = Array(self::CURATED_STATUS_PENDING, self::CURATED_STATUS_REJECTED, self::CURATED_STATUS_APPROVED);

    private $id;
        public function _get_id() { return $this->id; }
        public function _set_id($val) { if(is_null($this->id)) $this->id = $val; }
    private $datasetid;
        public function _get_datasetid() { return $this->datasetid; }
        public function _set_datasetid($val) { if(is_null($this->datasetid)) $this->datasetid = $val; }
    private $cid;
        public function _get_cid() { return $this->cid; }
        public function _set_cid($val) { if(is_null($this->cid)) $this->cid = $val; }
    private $timestamp;
        public function _get_timestamp() { return $this->timestamp; }
        public function _set_timestamp($val) { if(is_null($this->timestamp)) $this->timestamp = $val; }
    private $curated;
        public function _get_curated() { return $this->curated; }
        public function _set_curated($val) { if(in_array($val, self::$CURATED_STATUSES)) $this->curated = $val; }

    static public function createNewObj(Dataset $dataset, Community $community) {
        if(!$dataset->id || is_null($community->id)) return NULL;

        $timestamp = time();
        $curated = self::CURATED_STATUS_APPROVED;

        $obj = new CommunityDataset(Array(
            "id" => NULL,
            "datasetid" => $dataset->id,
            "cid" => $community->id,
            "timestamp" => $timestamp,
            "curated" => $curated,
        ));
        CommunityDataset::insertObj($obj);

        return $obj;
    }

    static public function deleteObj($obj) {
        $cxn = new Connection();
        $cxn->connect();
        $cxn->delete(self::$_table, "i", Array($obj->id), "where id=?");
        $cxn->close();
    }

    public function arrayForm() {
        $comm = new Community();
        $comm->getByID($this->cid);

        return Array(
            "id" => $this->id,
            "datasetid" => $this->datasetid,
            "cid" => $this->cid,
            "portalName" => $comm->portalName,
            "timestamp" => $this->timestamp,
            "curated" => $this->curated,
        );
    }
}

?>
