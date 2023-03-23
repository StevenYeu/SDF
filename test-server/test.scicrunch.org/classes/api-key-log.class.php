<?php

class APIKeyLog extends DBObject3 {
    protected static $_fields_definitions = null;
    protected static $_table_name = "api_key_log";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"        => self::fieldDef("id", "i", true),
            "key_id"    => self::fieldDef("key_id", "i", true),
            "uid"       => self::fieldDef("uid", "i", true),
            "timestamp" => self::fieldDef("timestamp", "i", true),
            "ip"        => self::fieldDef("ip", "s", true),
            "action"    => self::fieldDef("action", "s", true),
            "allowed"   => self::fieldDef("allowed", "i", true),
        );
    }
    protected function _display_timestamp($val) { return self::displayTime($val); }
    protected function _get_allowed($val) { return self::getBool($val); }
    protected function _set_allowed($val) { return self::setBool($val); }

    public static function createNewObj(APIKey $api_key = null, User $user = NULL, $ip, $action, $allowed) {
        $timestamp = time();
        $key_id = is_null($api_key) ? NULL : $api_key->id;
        $uid = is_null($user) ? NULL : $user->id;

        return self::insertObj(Array(
            "id" => NULL,
            "key_id" => $key_id,
            "uid" => $uid,
            "timestamp" => $timestamp,
            "ip" => $ip,
            "action" => $action,
            "allowed" => $allowed,
        ));
    }

    public static function deleteObj($obj) {
        return; /* can't delete */
    }

    public function arrayForm() {
        return Array(
            "key_id" => $this->key_id,
            "uid" => $this->uid,
            "ip" => $this->ip,
            "action" => $this->action,
            "allowed" => $this->allowed,
        );
    }
}
APIKeyLog::init();

?>
