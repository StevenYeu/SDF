<?php
class DatasetDoiTextFields extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "dataset_doi_text";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                            => self::fieldDef("id", "i", true),
            "dataset_id"                    => self::fieldDef("dataset_id", "i", true),
            "text"                          => self::fieldDef("text", "s", false),
            "type"                          => self::fieldDef("type", "s", false),
            "position"                      => self::fieldDef("position", "i", false),
        );
    }

   // private $_user;


    public function arrayForm() {
        return Array(
            "id" => $this->id,
            "dataset_id" => $this->dataset_id,
            "text" => $this->text,
            "type" => $this->type,
            "position" => $this->position,
        );
    }

}
DatasetDoiTextFields::init();

?>
