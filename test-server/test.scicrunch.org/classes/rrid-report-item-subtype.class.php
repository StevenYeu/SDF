<?php

class RRIDReportItemSubtype extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "rrid_report_item_subtype";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                    => self::fieldDef("id", "i", true),
            "rrid_report_item_id"   => self::fieldDef("rrid_report_item_id", "i", true),
            "timestamp"             => self::fieldDef("timestamp", "i", true),
            "subtype"               => self::fieldDef("subtype", "s", true),
        );
    }
    protected function _display_timestamp($val) { return self::displayTime($val); }

    private $_item;
    private $_user_data;

    public static function createNewObj(RRIDReportItem $rrid_report_item, $subtype) {
        $rrid_report_item_id = $rrid_report_item->id;
        if(is_null($rrid_report_item->subtypeInfo($subtype))) return NULL;

        $timestamp = time();

        $obj = self::insertObj(Array(
            "id" => NULL,
            "rrid_report_item_id" => $rrid_report_item_id,
            "timestamp" => $timestamp,
            "subtype" => $subtype,
        ));
        return $obj;
    }

    public static function deleteObj($obj, User $user = NULL) {
        $user_data = RRIDReportItemSubtypeUserData::loadArrayBy(Array("rrid_report_item_subtype_id"), Array($obj->id));
        foreach($user_data as $ud) {
            RRIDReportItemSubtypeUserData::deleteObj($ud);
        }

        $obj->saveHistory("delete", $user);
        parent::deleteObj($obj);
    }

    public function updateDB(User $user = NULL) {
        $this->saveHistory("update", $user);
        parent::updateDB();
    }

    public function arrayForm() {
        $user_data = RRIDReportItemSubtypeUserData::loadArrayBy(Array("rrid_report_item_subtype_id"), Array($obj->id));
        $return_array = Array(
            "subtype" => $this->subtype,
            "user_data" => Array(),
        );
        foreach($user_data as $ud) {
            $return_array["user_data"][] = $ud->arrayForm();
        }

        return $return_array;
    }

    public function item() {
        if(is_null($this->_item)) {
            $this->_item = RRIDReportItem::loadBy(Array("id"), Array($this->rrid_report_item_id));
        }
        return $this->_item;
    }

    public function userData() {
        if(is_null($this->_user_data)) {
            $this->_user_data = RRIDReportItemSubtypeUserData::loadArrayBy(Array("rrid_report_item_subtype_id"), Array($this->id));
        }
        return $this->_user_data;
    }

    public function userDataTypes() {
        $info = $this->item()->subtypeInfo($this->subtype);
        $user_data = $this->userData();
        if(!isset($info["user-data"])) return NULL;
        foreach($user_data as $ud) {
            if(isset($info["user-data"][$ud->name])) {
                $info["user-data"][$ud->name]["existing"] = $ud;
            }
        }

        return $info["user-data"];
    }

    public function setUserData($name, $value) {
        $existing = RRIDReportItemSubtypeUserData::loadBy(Array("name", "rrid_report_item_subtype_id"), Array($name, $this->id));
        if(!is_null($existing)) {
            $existing->data = $value;
            $existing->updateDB();
        } else {
            RRIDReportItemSubtypeUserData::createNewObj($this, $name, $value);
        }
    }

    public function getUserData($name) {
        foreach($this->userData() as $ud) {
            if($ud->name == $name) return $ud;
        }
        return NULL;
    }
}
RRIDReportItemSubtype::init();

?>
