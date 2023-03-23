<?php

class HistoryRecord extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "history_records";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"            => self::fieldDef("id", "i", true),
            "timestamp"     => self::fieldDef("timestamp", "i", true),
            "uid"           => self::fieldDef("uid", "i", true),
            "action"        => self::fieldDef("action", "s", true),
            "table"         => self::fieldDef("table", "s", true),
            "original_pk"   => self::fieldDef("original_pk", "i", true),
            "data"          => self::fieldDef("data", "s", true),
        );
    }
    protected function _get_data($val) {
        return parent::getJSON($val);
    }
    protected function _set_data($val) {
        return parent::setJSON($val);
    }

    protected static function createNewObj(User $user = NULL, $action, $table, $original_pk, $data) {
        $timestamp = time();
        if(!is_null($user)) {
            $uid = $user->id;
        } else {
            $uid = NULL;
        }

        $success = self::insertObj(Array(
            "id" => NULL,
            "timestamp" => $timestamp,
            "uid" => $uid,
            "action" => $action,
            "table" => $table,
            "original_pk" => $original_pk,
            "data" => $data,
        ), true);

        return $success;
    }

    public static function deleteObj($obj) {
        /* no delete */
        return;
    }

    public static function arrayForm() {
        return Array();
    }

    public static function createFromDBO3($obj, $action, User $user = NULL) {
        $table = $obj->dbTable();
        $original_pk = $obj->primaryKey();
        $data = $obj->getAllRaw();

        return self::createNewObj($user, $action, $table, $original_pk, $data);
    }
}
HistoryRecord::init();

?>
