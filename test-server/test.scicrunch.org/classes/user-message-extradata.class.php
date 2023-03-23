<?php

class UserMessageExtradata extends DBObject {
    static protected $_table = "user_messages_extradata";
    static protected $_table_fields = Array("id", "uid", "message_id", "type", "data");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iiiss";

    private $id;
        public function _get_id() { return $this->id; }
        public function _set_id($val) { if(is_null($this->id)) $this->id = $val; }
    private $uid;
        public function _get_uid() { return $this->uid; }
        public function _set_uid($val) { if(is_null($this->uid)) $this->uid = $val; }
    private $message_id;
        public function _get_message_id() { return $this->message_id; }
        public function _set_message_id($val) { if(is_null($this->message)) $this->message_id = $val; }
    private $type;
        public function _get_type() { return $this->type; }
        public function _set_type($val) { if(is_null($this->type)) $this->type = $val; }
    private $data;
        public function _get_data() { return $this->data; }
        public function _set_data($val) { $this->data = $val; }

    static public function createNewObj(User $user, UserMessage $message, $type, $data = NULL) {
        $data_obj = new UserMessageExtradata(Array(
            "id" => NULL,
            "uid" => $user->id,
            "message_id" => $message->id,
            "type" => $type,
            "data" => $data,
        ));
        UserMessageExtradata::insertObj($data_obj);
        return $data_obj;
    }

    static public function deleteObj($data_obj) {
        $cxn = new Connection();
        $cxn->connect();
        $cxn->delete(UserMessageExtradata::$_table, "i", Array($data_obj->id), "where id=?");
        $cxn->close();
    }
}

?>
