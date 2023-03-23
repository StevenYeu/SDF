<?php

class RRIDMentionsGrantInfo extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "rrid_mentions_grant_info";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                => self::fieldDef("Id", "i", true),
            "journal_record_id" => self::fieldDef("Journal_Record_Id", "i", true),
            "country"           => self::fieldDef("Country", "s", true),
            "agency"            => self::fieldDef("Agency", "s", true),
            "identifier"        => self::fieldDef("Identifier", "s", true),
        );
    }

    public static function createNewObj() {
        return NULL;
    }

    public static function deleteObj($obj) { }

    public function arrayForm() {
        return Array(
            "country" => $this->country,
            "agency" => $this->agency,
            "identifier" => $this->identifier,
        );
    }
}
RRIDMentionsGrantInfo::init();

?>
