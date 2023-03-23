<?php

class UsersExtraData extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "users_extra_data";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"        => self::fieldDef("id", "i", true),
            "uid"       => self::fieldDef("uid", "i", true),
            "timestamp" => self::fieldDef("timestamp", "i", true),
            "name"      => self::fieldDef("name", "s", true),
            "value"     => self::fieldDef("value", "s", false),
        );
    }
    public function _get_value($value) { return self::getJSON($value); }
    public function _set_value($value) { return self::setJSON($value); }

    public static function createNewObj(User $user, $name, $value) {
        if(!$name) return NULL;
        if(!$user->id) return NULL;
        $timestamp = time();
        return self::insertObj(Array(
            "id" => NULL,
            "uid" => $user->id,
            "timestamp" => $timestamp,
            "name" => $name,
            "value" => $value,
        ));
    }

    public static function deleteObj($obj, User $user = NULL) {
        $obj->saveHistory("delete", $user);
        parent::deleteObj($obj);
    }

    public function updateDB(User $user = NULL) {
        $this->saveHistory("update", $user);
        parent::updateDB();
    }

    public function getRRIDWorksByUser(User $user) {
        $cxn = new Connection();
        $cxn->connect();
        $rows = $cxn->select(self::$_table_name, Array("*"), "i", Array($user->id), "where uid=? and (name='orcid-works' or name='orcid-rrid')");
        $cxn->close();

        $data = Array();
        foreach($rows as $row) {
            $data[] = new UsersExtraData($row);
        }
        return $data;
    }

    public function getRRIDPMIDByUser(User $user) {
        $cxn = new Connection();
        $cxn->connect();
        $rows = $cxn->select(self::$_table_name, Array("*"), "i", Array($user->id), "where uid=? and (name='orcid-pmid' or name='orcid-rrid')");
        $cxn->close();

        $data = Array();
        foreach($rows as $row) {
            $data[] = new UsersExtraData($row);
        }
        return $data;
    }

    public function getWebsiteByUser(User $user) {
        $cxn = new Connection();
        $cxn->connect();
        $rows = $cxn->select(self::$_table_name, Array("*"), "i", Array($user->id), "where uid=? and name='lab-website'");
        $cxn->close();

        $data = Array();
        foreach($rows as $row) {
            $data[] = new UsersExtraData($row);
        }
        return $data;
    }

    public function arrayForm() {
        return Array(
            "timestamp" => $this->timestamp,
            "name" => $this->name,
            "value" => $this->value,
        );
    }
}
UsersExtraData::init();

?>
