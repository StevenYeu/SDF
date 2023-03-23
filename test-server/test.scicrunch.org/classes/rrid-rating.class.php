<?php

class RRIDRating extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "rrid_ratings";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"        => self::fieldDef("id", "i", true),
            "timestamp" => self::fieldDef("timestamp", "i", true),
            "viewid"    => self::fieldDef("viewid", "i", true),
            "rrid"      => self::fieldDef("rrid", "s", true),
            "source"    => self::fieldDef("source", "s", true),
            "rating"    => self::fieldDef("rating", "s", false),
        );
    }
    protected function _get_rating($val) {
        return parent::getJSON($val);
    }
    protected function _set_rating($val) {
        return parent::setJSON($val);
    }

    public static $sources = Array(

    );

    public static function createNewObj($nifid, Sources $view, $rrid, $source, $rating) {
        $timestamp = time();
        $viewid = $view->id;
        if(!\helper\startsWith($rrid, "RRID:")) {
            return NULL;
        }
        if(!isset(self::$sources[$source])) {
            return NULL;
        }

        return self::insertObj(Array(
            "id" => NULL,
            "timestamp" => $timestamp,
            "viewid" => $viewid,
            "rrid" => $rrid,
            "source" => $source,
            "rating" => $rating,
        ));
    }

    public static function deleteObj($obj) {
        parent::deleteObj($obj);
    }

    public function arrayForm() {
        return Array(
            "viewid" => $this->viewid,
            "rrid" => $this->rrid,
            "source" => $this->source,
            "rating" => $this->rating,
        );
    }
}
RRIDRating::init();

?>
