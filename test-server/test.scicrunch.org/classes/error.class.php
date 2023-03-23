<?php

class Error extends Connection {

    public $id;
    public $uid;
    public $type;
    public $message;
    public $hiddenmessage;
    public $seen;
    public $time;

    public function create($vars){
        $this->uid = $vars['uid'];
        $this->type = $vars['type'];
        $this->message = $vars['message'];
        $this->hiddenmessage = $vars['hiddenmessage'];
        $this->seen = 0;
        $this->time = time();
    }

    public function createFromRow($vars){
        $this->id = $vars['id'];
        $this->uid = $vars['uid'];
        $this->type = $vars['type'];
        $this->message = $vars['message'];
        $this->hiddenmessage = $vars['hiddenmessage'];
        $this->seen = $vars['seen'];
        $this->time = $vars['time'];
    }

    public function insertDB(){
        $this->connect();
        $this->id = $this->insert('error_notifications','iisssii',array(null,$this->uid,$this->type,$this->message,$this->hiddenmessage,$this->seen,$this->time));
        $this->close();
    }

    public function getByID($id){
        $this->connect();
        $return = $this->select('error_notifications',array('*'),'i',array($id),'where id=? and seen=0');
        $this->close();

        if(count($return)>0){
            $this->createFromRow($return[0]);
        }
    }

    public function setSeen(){
        $this->connect();
        $this->update('error_notifications','ii',array('seen'),array(1,$this->id),'where id=?');
        $this->close();
    }

    public static function staticCreate($vars) {
        $error = new Error();
        $error->create($vars);
        $error->insertDB();
        return $error;
    }

    public static function okayLoginFrequency($identifier, $time) {
        $timediff = $time - 300;
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select("error_notifications", Array("count(*)"), "si", Array($identifier, $timediff), "where hiddenmessage=? and time > ?");
        $cxn->close();
        if($count[0]["count(*)"] >= 5) return false;
        return true;
    }
}

class ErrorDBO extends DBObject3 {
    protected static $_fields_definitions = null;
    protected static $_table_name = "error_notifications";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"            => self::fieldDef("id", "i", true),
            "uid"           => self::fieldDef("uid", "i", true),
            "type"          => self::fieldDef("type", "s", true),
            "message"       => self::fieldDef("message", "s", true),
            "hiddenmessage" => self::fieldDef("hiddenmessage", "s", true),
            "seen"          => self::fieldDef("seen", "i", false),
            "time"          => self::fieldDef("time", "i", true),
        );
    }
    protected function _get_seen($val) { return self::getBool($val); }
    protected function _set_seen($val) { return self::setBool($val); }

    public static function createNewObj(User $user, $type, $message, $hiddenmessage="") {
        if(!$user->id) return NULL;
        $time = time();

        return self::insertObj(Array(
            "id" => NULL,
            "uid" => $user->id,
            "type" => $type,
            "message" => $message,
            "hiddenmessage" => $hiddenmessage,
            "seen" => false,
            "time" => $time,
        ));
    }

    public static function deleteObj($obj) {

    }

    public function arrayForm() {
        return Array(
            "uid" => $this->uid,
            "type" => $this->type,
            "message" => $this->message,
            "seen" => $this->seen,
            "time" => $this->time,
        );
    }
}
ErrorDBO::init();

?>
