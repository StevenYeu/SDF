<?php

class RRIDMap extends DBObject {
    static protected $_table = "rrid_map";
    static protected $_table_fields = Array("id", "uid", "issued_rrid", "replace_by", "regex", "active", "time");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iissiii";

    private $id;
        public function _get_id(){ return $this->id; }
        public function _set_id($val){ if(is_null($this->id)) $this->id = $val; }
    private $uid;
        public function _get_uid(){ return $this->uid; }
        public function _set_uid($val){ $this->uid = $val; }
    private $issued_rrid;
        public function _get_issued_rrid(){ return $this->issued_rrid; }
        public function _set_issued_rrid($val){ if(!RRIDMap::exists($this->issued_rrid)) $this->issued_rrid = $val; }
    private $replace_by;
        public function _get_replace_by(){ return $this->replace_by; }
        public function _set_replace_by($val){ $this->replace_by = $val; }
    private $regex;
        public function _get_regex(){ return $this->regex; }
        public function _set_regex($val){ if($val == 1 || $val == 0) $this->regex = $val; }
    private $active;
        public function _get_active(){ return $this->active; }
        public function _set_active($val){ if($val == 1 || $val == 0) $this->active = $val; }
    private $time;
        public function _get_time(){ return $this->time; }
        public function _set_time($val){ $this->time = $val; }

    static public function createNewObj($uid, $issued_rrid, $replace_by, $regex=0, $active=1){
        if(RRIDMap::exists($issued_rrid)) return NULL;
        $time = time();
        $rrid_map = new RRIDMap(Array(
            "id" => NULL,
            "uid" => $uid,
            "issued_rrid" => $issued_rrid,
            "replace_by" => $replace_by,
            "regex" => $regex,
            "active" => $active,
            "time" => $time
        ));
        RRIDMap::insertObj($rrid_map);

        return $rrid_map;
    }

    static public function deleteObj($obj){
        $cxn = new Connection();
        $cxn->connect();
        $cxn->delete(RRIDMap::$_table, "i", Array($obj->id), "where id=?");
        $cxn->close();
    }

    static public function exists($altid){
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select(RRIDMap::$_table, Array("count(*)"), "s", Array($altid), "where issued_rrid=?");
        $cxn->close();
        if($count[0]["count(*)"] > 0) return true;
        return false;
    }

    public function arrayForm(){
        return Array(
            "issued_rrid" => $this->issued_rrid,
            "replace_by" => $this->replace_by,
            "regex" => $this->regex,
            "active" => $this->active,
            "time" => $this->time
        );
    }

    static public function totalCount(){
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select(RRIDMap::$_table, Array("count(*)"), "", Array(), "");
        $cxn->close();
        return $count[0]["count(*)"];
    }

}

?>
