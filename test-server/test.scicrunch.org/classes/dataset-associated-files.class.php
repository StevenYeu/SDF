<?php

class DatasetAssociatedFiles extends DBObject3 { 
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "dataset_associated_files";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                    => self::fieldDef("id", "i", true),
            "uid"                    => self::fieldDef("uid", "i", true),
            "dataset_id"             => self::fieldDef("dataset_id", "i", true),
            "filename"               => self::fieldDef("filename", "s", false),
            "type"                   => self::fieldDef("type", "s", false),
            "date_added"             => self::fieldDef("date_added", "i", true)
        );
    }

//    public static function loadBy($dataset_id, $type) {
    public static function loadBy($fields, $values) {
        $cxn = new Connection();
        $cxn->connect();

        $results = $cxn->select("dataset_associated_files", Array("filename"), "is", Array($values[0], $values[1]), "where dataset_id=? AND type=? ORDER BY id desc LIMIT 1");
        $cxn->close();
        
        if ($results)
            return $results[0]['filename'];
        else
            return false;

/*
        if ($results) {
            foreach($results as $f) {
                $flags[] = $f['type'];
            }
        }
*/
    }

    public static function createNewObj($user_id, $dataset_id, $type, $filename) {
        if(!$dataset_id || !$user_id || !$type || !$filename) return NULL;
        
        $cxn = new Connection();
        $cxn->connect();

        $timestamp = time();
        $df_id = $cxn->insert("dataset_associated_files", "iiissi", Array(NULL, $user_id, $dataset_id, $filename, $type, $timestamp));
        
        if(!$df_id) throw new Exception("could not add file");
        $cxn->close();

        return;
    }
}