<?php

class RRIDReportItemSubtypeUserData extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "rrid_report_item_subtype_user_data";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                            => self::fieldDef("id", "i", true),
            "rrid_report_item_subtype_id"   => self::fieldDef("rrid_report_item_subtype_id", "i", true),
            "timestamp"                     => self::fieldDef("timestamp", "i", true),
            "name"                          => self::fieldDef("name", "s", true),
            "data"                          => self::fieldDef("data", "s", false),
        );
    }
    protected function _display_timestamp($val) { return self::displayTime($val); }
    protected function _set_data($val) {
        $subtype = $this->subtype();
        $name = $this->name;

        $item = $subtype->item();
        $info = $item->subtypeInfo($subtype->subtype);
        if(!isset($info["user-data"][$name])) return false;
        $type_info = $info["user-data"][$name];
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

    private $_subtype;

    static public function createNewObj(RRIDReportItemSubtype $subtype, $name, $data) {
        $timestamp = time();

        $obj = self::insertObj(Array(
            "id" => NULL,
            "rrid_report_item_subtype_id" => $subtype->id,
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
            "name" => $this->name,
            "data" => $this->data,
        );
    }

    public function subtype() {
        if(is_null($this->_subtype)) {
            $this->_subtype = self::loadSubtypeByID($this->rrid_report_item_subtype_id);
        }
        return $this->_subtype;
    }

    public static function loadSubtypeByID($subtype_id) {
        return RRIDReportItemSubtype::loadBy(Array("id"), Array($subtype_id));
    }
}
RRIDReportItemSubtypeUserData::init();

?>
