<?php

class RRIDMentionsLiteratureRecord extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "rrid_mentions_literature_records";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                    => self::fieldDef("Id", "i", true),
            "pmid"                  => self::fieldDef("PMID", "s", true),
            "journal_name"          => self::fieldDef("Journal_Name", "s", true),
            "title"                 => self::fieldDef("Title", "s", true),
            "author_name"           => self::fieldDef("Author_Name", "s", true),
            "author_affiliation"    => self::fieldDef("Author_Affiliation", "s", true),
            "publication_year"      => self::fieldDef("Publication_Year", "s", true),
            "issn"                  => self::fieldDef("ISSN", "s", true),
        );
    }

    private $_grant_infos;

    public static function createNewObj() {
        return NULL;
    }

    public static function deleteObj($obj) { }

    public function arrayForm() {
        $return_array = Array(
            "pmid" => $this->pmid,
            "journal_name" => $this->journal_name,
            "title" => $this->title,
            "author_name" => $this->author_name,
            "author_affiliation" => $this->author_affiliation,
            "publication_year" => $this->publication_year,
            "issn" => $this->issn,
            "grant_info" => Array(),
        );

        foreach($this->grantInfos() as $gi) {
            $return_array["grant_info"][] = $gi->arrayForm();
        }

        return $return_array;
    }

    public function grantInfos() {
        if(is_null($this->_grant_infos)) {
            $this->_grant_infos = RRIDMentionsGrantInfo::loadArrayBy(Array("journal_record_id"), Array($this->id));
        }
        return $this->_grant_infos;
    }
}
RRIDMentionsLiteratureRecord::init();

?>
