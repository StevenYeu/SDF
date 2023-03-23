<?php

class DatasetFlags extends DBObject3 { 
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "dataset_flags";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                    => self::fieldDef("id", "i", true),
            "uid"                    => self::fieldDef("uid", "i", true),
            "dataset_id"             => self::fieldDef("dataset_id", "i", true),
            "type"                   => self::fieldDef("type", "s", false)
        );
    }

    public static function loadByDatasetAndUser($dataset_id, $user_id) {
        $cxn = new Connection();
        $cxn->connect();
        $results = $cxn->select(
            "dataset_flags df",
            Array("type"),
            "ii",
            Array($dataset_id, $user_id),
            "where dataset_id = ? AND uid = ?");
        $cxn->close();

        $flags = Array();
        if ($results) {
            foreach($results as $f) {
                $flags[] = $f['type'];
            }
        }
        return $flags;
    }

    public static function createNewObj($user_id, $dataset_id, $type) {
        if(!$dataset_id || !$user_id) return NULL;
        
        $cxn = new Connection();
        $cxn->connect();

        $df_id = $cxn->insert("dataset_flags", "iiis", Array(NULL, $user_id, $dataset_id, $type));
        
        if(!$df_id) throw new Exception("could not add flag");
        $cxn->close();

        return;
    }
}