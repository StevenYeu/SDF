<?php

class RRIDReport extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "rrid_report";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"            => self::fieldDef("id", "i", true),
            "uid"           => self::fieldDef("uid", "i", true),
            "timestamp"     => self::fieldDef("timestamp", "i", true),
            "active"        => self::fieldDef("active", "i", false),
            "collection_id" => self::fieldDef("collection_id", "i", true),
            "name"          => self::fieldDef("name", "s", false),
            "description"   => self::fieldDef("description", "s", false),
        );
    }
    protected function _display_timestamp($val) { return self::displayTime($val); }
    protected function _set_name($val) { return self::setNotEmpty($val); }

    private static $loaded_user_reports = Array();
    private $_items = NULL;
    private $_itemsUUIDSet = NULL;

    public static function createNewObj($name, $description, User $user, Collection $collection=NULL) {
        if(!$name) return NULL;
        $collection_id = is_null($collection) ? NULL : $collection->id;
        $uid = $user->id;
        $timestamp = time();
        $active = 1;

        $obj = self::insertObj(Array(
            "id" => NULL,
            "uid" => $uid,
            "timestamp" => $timestamp,
            "active" => $active,
            "collection_id" => $collection_id,
            "name" => $name,
            "description" => $description,
        ));
        return $obj;
    }

    public static function deleteObj($obj) { }

    public function updateDB(User $user = NULL) {
        $this->saveHistory("update", $user);
        parent::updateDB();
    }

    public function arrayForm() {
        return Array(
            "id" => $this->id,
            "uid" => $this->uid,
            "timestamp" => $this->timestamp,
            "active" => $this->active,
            "collection_id" => $this->collection_id,
            "name" => $this->name,
            "description" => $this->description,
        );
    }

    public static function getUserReports($user) {
        if(is_null($user)) return Array();
        if(!isset(self::$loaded_user_reports[$user->id])) {
            $reports = self::loadArrayBy(Array("uid", "active"), Array($user->id, 1));
            self::$loaded_user_reports[$user->id] = $reports;
        }
        return self::$loaded_user_reports[$user->id];
    }

    public function items() {
        if(is_null($this->_items)) {
            $this->refreshItems();
        }
        return $this->_items;
    }

    private function refreshItems() {
        $this->_items = RRIDReportItem::loadArrayBy(Array("rrid_report_id"), Array($this->id));
        $this->_itemsUUIDSet = Array();
        foreach($this->_items as $item) {
            $this->_itemsUUIDSet[$item->uuid] = true;
        }
    }

    public function hasItemUUID($uuid) {
        if(is_null($this->_itemsUUIDSet)) {
            $this->refreshItems();
        }
        if($this->_itemsUUIDSet[$uuid]) return true;
        return false;
    }

    public function uniqueUUIDCount() {
        if(is_null($this->_itemsUUIDSet)) {
            $this->refreshItems();
        }
        return count($this->_itemsUUIDSet);
    }
}
RRIDReport::init();

?>
