<?php

class ComponentDataMulti extends DBObject3 {
    protected static $_fields_definitions = null;
    protected static $_table_name = "component_data_multi";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                => self::fieldDef("id", "i", true),
            "uid"               => self::fieldDef("uid", "i", false),
            "component_data_id" => self::fieldDef("component_data_id", "i", true),
            "timestamp"         => self::fieldDef("timestamp", "i", false),
            "name"              => self::fieldDef("name", "s", true, Array("allowed_values" => Array("multi-1", "multi-2", "multi-3"))),
            "value"             => self::fieldDef("value", "s", false),
        );
    }

    public static function createNewObj(Component_Data $component_data, User $user, $name, $value) {
        $timestamp = time();
        return self::insertObj(Array(
            "id" => NULL,
            "uid" => $user->id,
            "component_data_id" => $component_data->id,
            "timestamp" => $timestamp,
            "name" => $name,
            "value" => $value,
        ));
    }

    public static function deleteObj($obj) {
        parent::deleteObj($obj);
    }

    public function arrayForm() {
        return Array(
            "timestamp" => $this->timestamp,
            "name" => $this->name,
            "value" => $this->value,
        );
    }

    public static function allowedNames() {
        return self::$_fields_definitions["name"]["allowed_values"];
    }

    public static function upsert(Component_Data $component_data, User $user, $name, $value) {
        $cdm = self::loadBy(Array("component_data_id", "name"), Array($component_data->id, $name));
        if(is_null($cdm)) {
            $newObj = self::createNewObj($component_data, $user, $name, $value);
            return $newObj;
        } else {
            $cdm->uid = $user->id;
            $cdm->timestamp = time();
            $cdm->value = $value;
            $cdm->updateDB();
            return $cdm;
        }
    }

}
ComponentDataMulti::init();

?>
