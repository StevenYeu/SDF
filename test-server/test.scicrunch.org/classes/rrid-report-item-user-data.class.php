<?php

class RRIDReportItemUserData extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "rrid_report_item_user_data";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                    => self::fieldDef("id", "i", true),
            "rrid_report_item_id"   => self::fieldDef("rrid_report_item_id", "i", true),
            "timestamp"             => self::fieldDef("timestamp", "i", true),
            "name"                  => self::fieldDef("name", "s", true),
            "data"                  => self::fieldDef("data", "s", false),
        );
    }
    protected function _display_timestamp($val) { return self::displayTime($val); }
    protected function _set_data($val) {
        $item = $this->item();
        $name = $this->name;

        $type_info = RRIDReportItem::$allowed_types[$item->type]["user-data"][$name];

        $type = $type_info["type"];
        switch($type) {
            case "text":
            case "group-select":
                $options = Array("min" => $type_info["min"], "max" => $type_info["max"]);
                if(DataTypes::verifyMemDataByType("text", $val, $options)) {
                    return $val;
                }
                break;
            case "literature":
                if(DataTypes::verifyMemDataByType($type, $val)) {
                    return $val;
                }
                break;
        }
        return false;
    }

    private $_item;

    static public function createNewObj(RRIDReportItem $rrid_report_item, $name, $data) {
        if(!$rrid_report_item->id) return NULL;
        $timestamp = time();

        $obj = self::insertObj(Array(
            "id" => NULL,
            "rrid_report_item_id" => $rrid_report_item->id,
            "timestamp" => $timestamp,
            "name" => $name,
            "data" => $data,
        ));
        return $obj;
    }

    static public function deleteObj($obj, User $user = NULL) {
        $obj->saveHistory("delete", $user);
        parent::deleteObj($obj);
    }

    public function updateDB(User $user = NULL) {
        $this->saveHistory("update", $user);
        parent::updateDB();
    }

    public function arrayForm() {
        return Array(
            "id" => $this->id,
            "rrid_report_item_id" => $this->rrid_report_item_id,
            "timestamp" => $this->timestamp,
            "name" => $this->name,
            "data" => $this->data,
        );
    }

    public function item() {
        if(is_null($this->_item)) {
            $this->_item = self::loadItemByID($this->rrid_report_item_id);
        }
        return $this->_item;
    }

    public static function loadItemByID($rrid_report_item_id) {
        return RRIDReportItem::loadBy(Array("id"), Array($rrid_report_item_id));
    }
}
RRIDReportItemUserData::init();

?>
