<?php

class DatasetFieldAnnotation extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "dataset_field_annotations";
    protected static $_primary_key_field = "id";

    private $_field;

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                => self::fieldDef("id", "i", true),
            "dataset_field_id"  => self::fieldDef("dataset_field_id", "i", true),
            "name"              => self::fieldDef("name", "s", true),
            "value"             => self::fieldDef("value", "s", false),
        );
    }

    protected function _set_name($value) {
        if(!isset(self::$allowed_names[$value])) return false;
        $max_count = self::$allowed_names[$value]["max-count"];
        $field = $this->field();
        if(is_null($field)) return false;
        $template = $field->template();
        if(is_null($template)) return false;
        if($max_count > 0) {
            $fields = DatasetField::getByAnnotation($template, $value);
            if(count($fields) >= $max_count) return false;
        }
        return $value;
    }

    protected static $allowed_names = Array(
        "subject" => Array(
            "max-count" => 1,
        ),
        "multi-time" => Array(
            "max-count" => 0,   // unlimited
        ),
    );

    protected static function createNewObj(DatasetField $dataset_field, $name, $value) {
        $obj = self::insertObj(Array(
            "id" => NULL,
            "dataset_field_id" => $dataset_field->id,
            "name" => $name,
            "value" => $value,
        ));
        if(!is_null($obj)) {
            $template = $obj->field()->template()->unsubmit();
        }
        return $obj;
    }

    public static function deleteObj($obj, User $user = NULL) {
        $template = $obj->field()->template()->unsubmit();
        $obj->saveHistory("delete", $user);
        parent::deleteObj($obj);
    }

    public function arrayForm() {
        return Array(
            "name" => $this->name,
            "value" => $this->value,
        );
    }

    public function updateDB(User $user = NULL) {
        $this->saveHistory("update", $user);
        parent::updateDB();
        $this->field()->template()->unsubmit();
    }

    public function field() {
        if(is_null($this->_field)) {
            $this->_field = DatasetField::loadBy(Array("id"), Array($this->dataset_field_id));
        }
        return $this->_field;
    }

    public static function upsert(DatasetField $dataset_field, $name, $value) {
        $dfa = self::loadBy(Array("dataset_field_id", "name"), Array($dataset_field->id, $name));
        if(is_null($dfa)) {
            $newObj = self::createNewObj($dataset_field, $name, $value);
            return $newObj;
        } else {
            $dfa->value = $value;
            $dfa->updateDB();
            return $dfa;
        }
    }
}
DatasetFieldAnnotation::init();

?>
