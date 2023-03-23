<?php

class RRIDPrefixRedirect extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "rrid_prefix_redirects";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"        => self::fieldDef("id", "i", true),
            "uid"       => self::fieldDef("uid", "i", true),
            "timestamp" => self::fieldDef("timestamp", "i", true),
            "viewid"    => self::fieldDef("viewid", "s", true),
            "prefix"    => self::fieldDef("prefix", "s", true),
        );
    }
    protected function _set_viewid($val) {
        if(!isset(self::$_views[$val])) return false;
        return $val;
    }
    protected function _set_prefix($val) {
        return self::setNotEmpty($val);
    }

    private static $_views = Array(
        "nif-0000-07730-1" => Array(
            "name" => "Antibody",
            "field" => "Antibody ID",
        ),
        "nlx_154697-1" => Array(
            "name" => "Animal",
            "field" => "Database",
        ),
        "SCR_013869-1" => Array(
            "name" => "Cell lines",
            "field" => "Disease",
        ),
        "nif-0000-03179-1" => Array(
            "name" => "Taxonomy",
            "field" => "Taxonomy ID",
        ),
        "nlx_143929-1" => Array(
            "name" => "BioSamples",
            "field" => "ID",
        ),
        "nif-0000-11872-1" => Array(
            "name" => "AddGene",
            "field" => "Plasmid Name",
        ),
    );

    public static function createNewObj(User $user, $viewid, $prefix) {
        if(!$user->id || !$viewid || !$prefix) {
            return NULL;
        }
        $timestamp = time();

        return self::insertObj(Array(
            "id" => NULL,
            "uid" => $user->id,
            "timestamp" => $timestamp,
            "viewid" => $viewid,
            "prefix" => $prefix,
        ));
    }

    public static function deleteObj($obj) {
        parent::deleteObj($obj);
    }

    public function arrayForm() {
        return Array(
            "timestamp" => $this->timestamp,
            "viewid" => $this->viewid,
            "prefix" => $this->prefix,
        );
    }

    public static function extractHref($html) {                                                             
        $dom_data = new DOMDocument();
        $dom_data->loadHTML($html);
        foreach($dom_data->getElementsByTagName("a") as $anchor) {
            $href = $anchor->getAttribute("href");
            if($href) return $href;
        }
        return NULL;
    }

    public static function shouldRedirect($viewid, $name) {
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select(self::$_table_name, Array("count(*)"), "ss", Array($viewid, $name), "where viewid=? and ? like concat(prefix, '%')");
        $cxn->close();

        if($count[0]["count(*)"] > 0) {
            return true;
        }
        return false;
    }

    public static function redirectURL($record, $viewid) {
        if(!isset(self::$_views[$viewid])) return NULL;
        $field = self::$_views[$viewid]["field"];
        return self::extractHref($record[$field]);
    }

    public static function views() {
        return self::$_views;
    }
}
RRIDPrefixRedirect::init();

?>
