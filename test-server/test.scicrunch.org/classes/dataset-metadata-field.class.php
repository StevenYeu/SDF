<?php

class DatasetMetadataField extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "dataset_metadata_fields";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                => self::fieldDef("id", "i", true),
            "labid"             => self::fieldDef("labid", "i", true),
            "name"              => self::fieldDef("name", "s", false),
            "type"              => self::fieldDef("type", "s", false, Array("allowed_values" => self::$allowed_types)),
            "allowed_values"    => self::fieldDef("allowed_values", "s", false),
            "required"          => self::fieldDef("required", "i", false, Array("allowed_values" => Array(0, 1))),
            "position"          => self::fieldDef("position", "i", false),
        );
    }
    protected function _set_name($val) {
        if(!!$val) return false;
        $id = $this->id;
        $labid = $this->labid;
        if(!DatasetMetadataField::uniqueName($val, $id, $labid)) return false;
        return true;
    }

    
    const TYPE_INT = "int";
    const TYPE_STRING = "string";
    const TYPE_FLOAT = "float";
    public static $allowed_types = Array(self::TYPE_INT, self::TYPE_STRING, self::TYPE_FLOAT);

    public static function createNewObj(Lab $lab, $name, $type, $allowed_values, $required) {
        $position = count(self::loadArrayBy(Array("labid"), Array($lab->id)));

        if(!$name || !$lab->id) return NULL;

        $obj = self::insertObj(Array(
            "id" => NULL,
            "labid" => $lab->id,
            "name" => $name,
            "type" => $type,
            "allowed_values" => $allowed_values,
            "required" => $required,
            "position" => $position,
        ));

        return $obj;
    }

    static public function deleteObj($obj) {
        $labid = $obj->labid;

        parent::deleteObj($obj);

        self::resetPositions($labid);
    }

    public static function uniqueName($name, $id, $labid) {
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select(self::$_table_name, Array("count(*)"), "isi", Array($labid, $name, $id), "where labid=? and name=? and id!=?");
        $cxn->close();
        return $count[0]["count(*)"] == 0;
    }

    public function arrayForm() {
        return Array(
            "name" => $this->name,
            "type" => $this->type,
            "allowed_values" => $allowed_values,
            "position" => $this->position,
        );
    }

    public function filterVal($value) {
        switch($this->type) {
            case self::TYPE_FLOAT:
                return filter_var($value, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case self::TYPE_STRING:
                return filter_var($value, FILTER_SANITIZE_STRING);
            case self::TYPE_INT:
                return filter_var($value, FILTER_SANITIZE_NUMBER_INT);
            default:
                return NULL;
        }
    }

    public function movePositionUp() {
        if($this->position == 0) return;
        $new_pos = $this->position - 1;
        $all_metadata = self::loadArrayBy(Array("labid"), Array($this->labid));
        foreach($all_metadata as $am) {
            if($am->position == $new_pos) {
                $am->position += 1;
                $am->updateDB();
                break;
            }
        }
        $this->position = $new_pos;
        $this->updateDB();
    }

    public function movePositionDown() {
        $all_metadata = self::loadArrayBy(Array("labid"), Array($this->labid));
        if($this->position == count($all_metadata) - 1) return;
        $new_pos = $this->position + 1;
        foreach($all_metadata as $am) {
            if($am->position == $new_pos) {
                $am->position -= 1;
                $am->updateDB();
                break;
            }
        }
        $this->position = $new_pos;
        $this->updateDB();
    }

    static public function resetPositions($labid) {
        $all_metadata = self::loadArrayBy(Array("labid"), Array($labid));
        usort($all_metadata, function($a, $b) {
            if($a->position < $b->position) return -1;
            if($a->position > $b->position) return 1;
            return 0;
        });

        foreach($all_metadata as $i => $am) {
            if($am->position != $i) {
                $am->position = $i;
                $am->updateDB();
            }
        }
    }
}
DatasetMetadataField::init();

?>
