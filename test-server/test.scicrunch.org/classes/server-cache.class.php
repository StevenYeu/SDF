<?php

class ServerCache extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "server_cache";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"        => self::fieldDef("id", "i", true),
            "timestamp" => self::fieldDef("timestamp", "i", true),
            "name"      => self::fieldDef("name", "s", true),
            "value"     => self::fieldDef("value", "s", false),
        );
    }

    public static function createNewObj($name, $value) {
        if(!$name) return NULL;
        $timestamp = time();
        return self::insertObj(Array(
            "id" => NULL,
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

    public static function loadByName($name) {
        return ServerCache::loadBy(Array("name"), Array($name));
    }
}
ServerCache::init();

?>
