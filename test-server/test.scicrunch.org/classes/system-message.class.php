<?php

class SystemMessage extends DBObject {
    static protected $_table = "system_messages";
    static protected $_table_fields = Array("id", "cid", "uid", "start_time", "end_time", "active", "type", "redirect", "message", "timestamp");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iiiiiisssi";

    static public $allowed_types = Array("success", "info", "warning", "danger");

    const ALL_CID = "-1";

    private $id;
        public function _get_id() { return $this->id; }
        public function _set_id($val) { if(is_null($this->id)) $this->id = $val; }
    private $cid;
        public function _get_cid() { return $this->cid; }
        public function _set_cid($val) { if(is_null($this->cid)) $this->cid = $val; }
    private $uid;
        public function _get_uid() { return $this->uid; }
        public function _set_uid($val) { if(is_null($this->uid)) $this->uid = $val; }
    private $start_time;
        public function _get_start_time() { return $this->start_time; }
        public function _set_start_time($val) { $this->start_time = $val; }
    private $end_time;
        public function _get_end_time() { return $this->end_time; }
        public function _set_end_time($val) { $this->end_time = $val; }
    private $active;
        public function _get_active() { return $this->active; }
        public function _set_active($val) { if($val === 1 || $val === 0) $this->active = $val; }
    private $type;
        public function _get_type() { return $this->type; }
        public function _set_type($val) { $this->type = $val; }
    private $redirect;
        public function _get_redirect() { return $this->redirect; }
        public function _set_redirect($val) { $this->redirect = $val; }
    private $message;
        public function _get_message() { return $this->message; }
        public function _set_message($val) { $this->message = $val; }
    private $timestamp;
        public function _get_timestamp() { return $this->timestamp; }
        public function _set_timestamp($val) { if(is_null($this->timestamp)) $this->timestamp = $val; }

    static public function createNewObj($message, $uid, $cid, $start_time = NULL, $end_time = NULL, $type = NULL, $redirect = NULL) {
        $timestamp = time();
        $obj = new SystemMessage(Array(
            "id" => NULL,
            "cid" => $cid,
            "uid" => $uid,
            "start_time" => $start_time,
            "end_time" => $end_time,
            "active" => 1,
            "type" => $type,
            "message" => $message,
            "redirect" => $redirect,
            "timestamp" => $timestamp,
        ));
        SystemMessage::insertObj($obj);

        return $obj;
    }

    public function deleteObj() {

    }

    public function arrayForm() {
        if($this->cid === -1) {
            $comm_portal_name = "All communities";
        } else {
            $community = new Community();
            $community->getByID($this->cid);
            $comm_portal_name = $community->portal_name;
        }
        return Array(
            "id" => $this->id,
            "cid" => $this->cid,
            "comm_portal_name" => $comm_portal_name,
            "start_time" => $this->start_time,
            "end_time" => $this->end_time,
            "active" => $this->active,
            "type" => $this->type,
            "message" => $this->message,
            "timestamp" => $this->timestamp,
        );
    }

    static public function getNonExpiredMessages($cid = NULL) {
        $now = time();

        $cxn = new Connection();
        $cxn->connect();
        if(is_null($cid)) {
            $ids = $cxn->select(self::$_table, Array("id"), "i", Array($now), "where end_time > ? and active = 1");
        } else {
            $ids = $cxn->select(self::$_table, Array("id"), "ii", Array($now, $cid), "where end_time > ? and active = 1 and cid = ?");
        }
        $cxn->close();

        $messages = Array();
        foreach($ids as $ida) {
            $id = $ida["id"];
            $message = SystemMessage::loadBy(Array("id"), Array($id));
            if(!is_null($message)) $messages[] = $message;
        }

        return $messages;
    }

    static public function getActiveMessages($cid = NULL) {
        $now = time();

        $cxn = new Connection();
        $cxn->connect();
        if(is_null($cid)) {
            $ids = $cxn->select(self::$_table, Array("id"), "ii", Array($now, $now), "where start_time < ? and end_time > ? and active = 1");
        } else {
            $ids = $cxn->select(self::$_table, Array("id"), "iii", Array($now, $now, $cid), "where start_time < ? and end_time > ? and active = 1 and cid = ?");
        }
        $cxn->close();

        $messages = Array();
        foreach($ids as $ida) {
            $id = $ida["id"];
            $message = SystemMessage::loadBy(Array("id"), Array($id));
            if(!is_null($message)) $messages[] = $message;
        }

        return $messages;
    }

    static public function alertsHTML($cid) {
        $messages = self::getActiveMessages($cid);
        $html = "";
        foreach($messages as $message) {
            $alert_type = self::typeConvert($message->type);
            $extra_message = "";
            $class = "";
            if($alert_type == "info") {
                $class = 'alert-dismissible-hidden';
                $extra_message = ' <a class="js-alert-dismiss" data-id="' . $message->id . '" href="javascript:void(0)">Dismiss and don\'t show again</a>';
            }
            $html .= '<div data-id="' . $message->id . '" class="alert-dismissible alert alert-' . $alert_type . ' ' . $class . '">' . $message->message . $extra_message . '</div>';
        }
        return $html;
    }

    static public function typeConvert($type) {
        if(in_array($type, self::$allowed_types)) return $type;
        return "info";
    }
}

?>
