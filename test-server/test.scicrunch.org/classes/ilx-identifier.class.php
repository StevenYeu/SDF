<?php

class IlxIdentifier extends DBObject {
    static protected $_table = "ilx_identifiers";
    static protected $_table_fields = Array("id", "uid", "fragment", "term", "datetime", "note", "defining_url");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iississ";

    private $id;
        public function _get_id(){ return $this->id; }
        public function _set_id($val){ if(is_null($this->id)) $this->id = $val; }
    private $uid;
        public function _get_uid(){ return $this->uid; }
        public function _set_uid($val){ $this->uid = $val; }
    private $fragment;
        public function _get_fragment(){ return $this->fragment; }
        public function _set_fragment($val){ if(is_null($this->fragment)) $this->fragment = $val; }
    private $term;
        public function _get_term(){ return $this->term; }
        public function _set_term($val){
            if(is_null($this->term)){
                if(is_null($val) || strlen($val) == 0) throw new Exception("invalid term value");
                $this->term = $val;
            }
        }
    private $datetime;
        public function _get_datetime(){ return $this->datetime; }
        public function _set_datetime($val){ $this->datetime = $val; }
    private $note;
        public function _get_note(){ return $this->note; }
        public function _set_note($val){ $this->note = $val; }
    private $defining_url;
        public function _get_defining_url(){ return $this->defining_url; }
        public function _set_defining_url($val){ $this->defining_url = $val; }

    static public function createNewObj($term, $uid, $note=NULL, $defining_url=NULL, $fragment=NULL, $type="term") {
        // todo will be moved to config file
        $cxn = new Connection(); // Open mysql connection.
        $cxn->connect();
        if ($type == 'pde') {
            $prefix = 'pde_';
        } elseif ($type == 'cde') {
            $prefix = 'cde_';
        } else {
            // fde type does not have it's own prefix
            $prefix = ILX_FRAGMENT_PREFIX . '_';
        }
        $term = $cxn->mysqli->real_escape_string($term);
        $note = !empty($note) ? "'$note'" : "NULL"; // insure NULL insert if empty.
        $note = $cxn->mysqli->real_escape_string($note);
        $defining_url = !empty($defining_url) ? "'$defining_url'" : "NULL"; // insure NULL insert if empty.
        $time = time(); // get timestamp here to avoid a secondary query.
        /*
            Custom mysql insert function for table ilx_identifiers.
            Gapless insert perfect for ontological work at the cost of asnyc speed.
            Returns nameless field with value InterLex ID only -> {'0': ilx_#}.
        */
        $sql = "select ilx_identity('".$prefix."', $uid, '".$term."', $time, '".$note."', '".$defining_url."');";
        // $sql = 'select ilx_identity("'.$prefix.'", $uid, "'.$term.'", $time, "'.$note.'", "'.$defining_url.'");';
        $response = $cxn->mysqli->query($sql) or NULL; // query custom insert or NULL if failed.
        if (is_null($response))
            return NULL;
        $ilx_id = mysqli_fetch_array($response)['0']; // pull rows from sql obj & exact first & only element.
        $ilx_entity = new IlxIdentifier(Array(
            "id" => $cxn->mysqli->insert_id, // ilx_identifiers PK id for new row
            "uid" => $uid, // user id determined by APIKEY used
            "fragment" => $ilx_id, // InterLex official identifier
            "term" => $term, // string label of entity
            "datetime" => $time, // time of mysql insert
            "note" => $note, // unoffical comment about inserted entited; will not show in interface.
            "defining_url" => $defining_url, // source of entity inserted.
        ));
        $cxn->close(); // Close mysql connections.
        return $ilx_entity; // Json Dictionary object in the end.
    }

    static public function deleteObj($obj){
        return;
    }

    public function arrayForm(){
        return Array(
            "fragment" => $this->_get_fragment(),
            "term" => $this->_get_term(),
            "datetime" => $this->_get_datetime(),
            "note" => $this->_get_note(),
            "defining_url" => $this->_get_defining_url()
        );
    }

    static public function checkExisting($term, $uid){
        return IlxIdentifier::loadBy(Array("term", "uid"), Array($term, $uid));
    }
}

?>
