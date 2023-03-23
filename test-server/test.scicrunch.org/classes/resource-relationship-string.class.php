<?php

class ResourceRelationshipString extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "resource_relationship_strings";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"        => self::fieldDef("id", "i", true),
            "forward"   => self::fieldDef("forward", "s", true),
            "reverse"   => self::fieldDef("reverse", "s", true),
            "type1"     => self::fieldDef("type1", "s", true, Array("allowed_values" => self::$_allowed_types)),
            "type2"     => self::fieldDef("type2", "s", true, Array("allowed_values" => self::$_allowed_types)),
        );
    }

    protected static $_allowed_types = Array("res", "funding");

    public static function createNewObj() {
        return NULL;
    }

    public static function deleteObj($obj) {
        return;
    }

    public function arrayForm() {
        return $this->arrayFormAll();
    }

    public static function allowedTypes() {
        return self::$_allowed_types;
    }

    public static function loadByStringAndTypes($string, $type1, $type2) {
        $cxn = new Connection();
        $cxn->connect();
        $rows = $cxn->select(
            self::$_table_name,
            Array("*"),
            "ssssss",
            Array($string, $type1, $type2, $string, $type2, $type1),
            "where (forward=? and type1=? and type2=?) or (reverse=? and type1=? and type2=?)"
        );
        $cxn->close();
        if(count($rows) == 1) {
            return new ResourceRelationshipString($rows[0]);
        }
        return NULL;
    }
}
ResourceRelationshipString::init();

?>
