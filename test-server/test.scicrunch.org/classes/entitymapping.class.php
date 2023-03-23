<?php

class EntityMapping extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "entity_mapping";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                => self::fieldDef("id", "i", true),
            "source"            => self::fieldDef("source", "s", true),
            "table_name"        => self::fieldDef("table_name", "s", true),
            "col"               => self::fieldDef("col", "s", true),
            "value"             => self::fieldDef("value", "s", true),
            "identifier"        => self::fieldDef("identifier", "s", false),
            "external_id"       => self::fieldDef("external_id", "s", false),
            "relation"          => self::fieldDef("relation", "s", false),
            "match_substring"   => self::fieldDef("match_substring", "i", false, Array("allowed_values" => Array(0, 1))),
            "curation_status"   => self::fieldDef("curation_status", "s", false, Array("allowed_values" => self::$allowed_curation_status)),
            "uid"               => self::fieldDef("uid", "i",  true),
            "uid_updater"       => self::fieldDef("uid_updater", "i", false),
            "timestamp"         => self::fieldDef("timestamp", "i", true),
            "status"            => self::fieldDef("status", "s", false),
        );
    }
    protected function _set_source($val) {
        if(!EntityMapping::isSourceID($val)) return false;
        return $val;
    }
    protected function _display_timestamp($val) {
        return self::displayTime($val);
    }

    protected static $_history_table = "entity_mapping_history";

    const CURATION_STATUS_CURATED = "curated";
    const CURATION_STATUS_PENDING = "pending";
    const CURATION_STATUS_REJECTED = "rejected";
    public static $allowed_curation_status = Array(self::CURATION_STATUS_CURATED, self::CURATION_STATUS_PENDING, self::CURATION_STATUS_REJECTED);

    public static function createNewObj($source, $table_name, $col, $value, $identifier, $relation, $uid, $external_id = NULL, $match_substring = 0, $status = NULL, $curation_status = "pending"){
        if(is_null($relation)) $relation = "OWL:equivalentClass";
        if(!in_array($curation_status, self::$allowed_curation_status)) $curation_status = self::CURATION_STATUS_PENDING;
        if(is_null($match_substring)) $match_substring = 0;
        if($curation_status === self::CURATION_STATUS_CURATED){
            if(!is_null(self::getExistingCurated($source, $table_name, $col, $value, $curation_status))) throw new Exception("curated mapping already exists for this value");
        }
        if(self::checkExisting($source, $table_name, $col, $value, $identifier)) throw new Exception("entity mapping already exists");

        $time = time();
        $obj = self::insertObj(Array(
            "id" => NULL,
            "source" => $source,
            "table_name" => $table_name,
            "col" => $col,
            "value" => $value,
            "identifier" => $identifier,
            "external_id" => $external_id,
            "relation" => $relation,
            "match_substring" => $match_substring,
            "curation_status" => $curation_status,
            "uid" => $uid,
            "uid_updater" => $uid,
            "timestamp" => $time,
            "status" => $status
        ));

        return $obj;
    }

    public static function deleteObj(){

    }

    public function updateDB(){
        if($this->curation_status === self::CURATION_STATUS_CURATED){
            $existing = self::getExistingCurated($this->source, $this->table_name, $this->col, $this->value, $this->curation_status);
            if(!is_null($existing) && $existing->id !== $this->id) throw new Exception("curated mapping already exists for this value");
        }
        if($this->checkExistingNotSelf()) throw new Exception("entity mapping already exists");

        $this->createHistoryRecord();
        parent::updateDB();
    }

    private function createHistoryRecord(){
        $cxn = new Connection();
        $cxn->connect();
        $db_entity = $cxn->select(self::$_table_name, Array("source", "table_name", "col", "value", "identifier", "external_id", "relation", "match_substring", "curation_status", "uid_updater", "timestamp", "status"), "i", Array($this->id), "where id=?");
        if(count($db_entity) !== 1) throw new Exception("bad entity mapping id");
        $jent = json_encode($db_entity[0]);
        $time = time();
        $cxn->insert(self::$_history_table, "iisi", Array(NULL, $this->id, $jent, $time));
        $cxn->close();
    }

    /**
     * check if an entity exist with this curation status, source, table_name, col and value
     *
     * @return Entity an entity or null
     */
    private static function getExistingCurated($source, $table_name, $col, $value, $curation_status){
        $entity = self::loadBy(Array("source", "table_name", "col", "value", "curation_status"), Array($source, $table_name, $col, $value, $curation_status));
        return $entity;
    }

    /**
     * check if an entity exists, these five fields must be unique
     *
     * @return bool
     */
    private static function checkExisting($source, $table_name, $col, $value, $identifier){
        $existing = self::loadBy(Array("source", "table_name", "col", "value", "identifier"), Array($source, $table_name, $col, $value, $identifier));
        return !is_null($existing);
    }

    private function checkExistingNotSelf(){
        $existing = self::loadArrayBy(Array("source", "table_name", "col", "value", "identifier"), Array($this->source, $this->table_name, $this->col, $this->value, $this->identifier));
        if(count($existing) > 1) return true;
        if(count($existing) === 0) return false;
        if($existing[0]->id === $this->id) return false;
        return true;
    }

    public function isSourceID($source_id){
        $holder = new Sources();
        $sources = $holder->getAllSources();
        foreach($sources as $nif => $src){
            $nif_array = explode("-", $nif);
            $nif_id = implode("-", array_slice($nif_array, 0, count($nif_array) - 1));
            if($nif_id === $source_id) return true;
        }
        return false;
    }

    public function arrayForm(){
        return Array(
            "source" => $this->source,
            "table_name" => $this->table_name,
            "col" => $this->col,
            "value" => $this->value,
            "identifier" => $this->identifier,
            "external_id" => $this->external_id,
            "relation" => $this->relation,
            "match_substring" => $this->match_substring,
            "curation_status" => $this->curation_status,
            "timestamp" => $this->timestamp,
            "status" => $this->status,
        );
    }

    public static function getAllSources() {
        $cxn = new Connection();
        $cxn->connect();
        $sources = $cxn->select(self::$_table_name, Array("distinct source"), "", Array(), "");
        $cxn->close();

        $fsources = Array();
        foreach($sources as $s) $fsources[] = $s["source"];
        return $fsources;
    }

    public static function getAllTables($source) {
        $cxn = new Connection();
        $cxn->connect();
        $tables = $cxn->select(self::$_table_name, Array("distinct table_name"), "s", Array($source), "where source = ?");
        $cxn->close();

        $ftables = Array();
        foreach($tables as $t) $ftables[] = $t["table_name"];
        return $ftables;
    }

    public static function getAllColumns($source, $table) {
        $cxn = new Connection();
        $cxn->connect();
        $columns = $cxn->select(self::$_table_name, Array("distinct col"), "ss", Array($source, $table), "where source = ? and table_name = ?");
        $cxn->close();

        $fcolumns = Array();
        foreach($columns as $c) $fcolumns = $c["col"];
        return $fcolumns;
    }
}
EntityMapping::init();

?>
