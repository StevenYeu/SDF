<?php

/*
    this database object is for storing limited use keys, for very specific actions, usually sent as links in urls (eg, unsubscribe from email notifications links).
    usually the key should be one time use and should be deleted after it's been used.
*/
class ActionKey extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "action_keys";
    protected static $_primary_key_field = "id";

    public static $allowed_types = Array("email-unsubscribe", "lab-create", "lab-join");

    public static function init() {
        self::$_fields_definitions = Array(
            "id"            => self::fieldDef("id", "i", true),
            "timestamp"     => self::fieldDef("timestamp", "i", true),
            "key_val"       => self::fieldDef("key_val", "s", true),
            "type"          => self::fieldDef("type", "s", true, Array("allowed_values" => self::$allowed_types)),
            "fkey"          => self::fieldDef("fkey", "i", true),
            "expire_time"   => self::fieldDef("expire_time", "i", true),
            "data"          => self::fieldDef("data", "s", true),
            "active"        => self::fieldDef("active", "i", false),
        );
    }
    protected function _get_active($val) {
        return self::getBool($val);
    }
    protected function _set_active($val) {
        return self::setBool($val);
    }

    public static function createNewObj($type, $foreign_key, $expire_time, $data) {
        $timestamp = time();
        $key_val = APIKey::getRandomKeyString(32);
        if(!is_null($data)) {
            $jdata = json_encode($data);
        } else {
            $jdata = NULL;
        }

        $obj = self::insertObj(Array(
            "id" => NULL,
            "timestamp" => $timestamp,
            "key_val" => $key_val,
            "type" => $type,
            "fkey" => $foreign_key,
            "expire_time" => $expire_time,
            "data" => $jdata,
            "active" => true,
        ));

        return $obj;
    }

    public static function deleteObj($obj) {
        $obj->active = false;
        $obj->updateDB();
    }

    public function arrayForm() {
        return Array();
    }

    public static function loadByKey($key_val) {
        $key = self::loadBy(Array("key_val", "active"), Array($key_val, 1), true, false, true);
        if(is_null($key)) return NULL;
        if($key->expired()) {
            self::deleteObj($key);
            return NULL;
        }
        return $key;
    }

    public function expired() {
        if(is_null($this->expire_time)) return false;   // no expire time, so can't expire
        $now = time();
        if($this->expire_time < $now) return true;
        return false;
    }
}
ActionKey::init();

?>
