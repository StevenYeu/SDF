<?php

class TermFlag extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "term_flags";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"        => self::fieldDef("id", "i", true),
            "termid"    => self::fieldDef("termid", "i", true),
            "uid"       => self::fieldDef("uid", "i", true),
            "timestamp" => self::fieldDef("timestamp", "i", true),
            "flag"      => self::fieldDef("flag", "s", true, Array("allowed_values" => Array("elastic-upsert"))),
            "active"    => self::fieldDef("active", "i", false),
        );
    }
    public function _get_active($val) {
        return self::getBool($val);
    }
    public function _set_active($val) {
        return self::setBool($val);
    }

    public static function createNewObj(TermDBO $term, User $user = NULL, $flag) {
        $existing = self::loadBy(Array("termid", "flag", "active"), Array($term->id, $flag, 1));
        if(!is_null($existing)) {
            return $existing;
        }

        $timestamp = time();
        $uid = $user ? $user->id : NULL;
        return self::insertObj(Array(
            "id" => NULL,
            "termid" => $term->id,
            "uid" => $uid,
            "timestamp" => $timestamp,
            "flag" => $flag,
            "active" => true,
        ));
    }

    public static function deleteObj($obj) {
        return;
    }

    public static function arrayForm() {
        return Array(
            "termid" => $this->termid,
            "uid" => $user->id,
            "timestamp" => $this->timestamp,
            "flag" => $this->flag,
            "active" => $this->active,
        );
    }
}
TermFlag::init();

?>
