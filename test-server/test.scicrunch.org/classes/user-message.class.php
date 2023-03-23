<?php

class UserMessage extends DBObject {
    static protected $_table = "user_messages";
    static protected $_table_fields = Array("id", "uid", "conversation_id", "message", "timestamp");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iiisi";

    private $id;
        public function _get_id() { return $this->id; }
        public function _set_id($val) { if(is_null($this->id)) $this->id = $val; }
    private $uid;
        public function _get_uid() { return $this->uid; }
        public function _set_uid($val) { if(is_null($this->uid)) $this->uid = $val; }
    private $conversation_id;
        public function _get_conversation_id() { return $this->conversation_id; }
        public function _set_conversation_id($val) { if(is_null($this->conversation_id)) $this->conversation_id = $val; }
    private $message;
        public function _get_message() { return $this->message; }
        public function _set_message($val) { if(is_null($this->message)) $this->message = $val; }
    private $timestamp;
        public function _get_timestamp() { return $this->timestamp; }
        public function _set_timestamp($val) { if(is_null($this->timestamp)) $this->timestamp = $val; }

    static public function createNewObj($message, UserMessageConversation $conversation, User $user = null) {
        // get the user id, if no user then 0
        if(is_null($user)) {
            $uid = 0;
        } else {
            $uid = $user->id;
            
            // make sure if it's a user, they are actually a part of the conversation
            $conversation_user = UserMessageConversationUser::loadBy(Array("uid", "conversation_id"), Array($uid, $conversation->id));
            if(is_null($conversation_user)) return NULL;
        }

        // get the other fields
        $conversation_id = $conversation->id;
        $timestamp = time();

        // create new object
        $message_obj = new UserMessage(Array(
            "id" => NULL,
            "uid" => $uid,
            "conversation_id" => $conversation_id,
            "message" => $message,
            "timestamp" => $timestamp,
        ));
        UserMessage::insertObj($message_obj);
        return $message_obj;
    }

    static public function deleteObj() {

    }

    public function arrayForm() {
        $user = new User();
        $user->getByID($this->uid);
        if($user->id) $fullname = $user->firstname . " " . $user->lastname;
        else $fullname = "Unknown";

        return Array(
            "uid" => $this->uid,
            "fullname" => $fullname,
            "conversation_id" => $this->conversation_id,
            "message" => $this->message,
            "timestamp" => $this->timestamp,
        );
    }
}

?>
