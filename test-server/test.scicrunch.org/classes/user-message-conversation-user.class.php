<?php

class UserMessageConversationUser extends DBObject {

    static protected $_table = "user_messages_conversations_users";
    static protected $_table_fields = Array("id", "uid", "conversation_id", "new_flag", "timestamp", "lastread_timestamp");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iiiiii";

    private $id;
        public function _get_id(){ return $this->id; }
        public function _set_id($val){ if(is_null($this->id)) $this->id = $val; }
    private $uid;
        public function _get_uid(){ return $this->uid; }
        public function _set_uid($val){ if(is_null($this->uid)) $this->uid = $val; }
    private $conversation_id;
        public function _get_conversation_id(){ return $this->conversation_id; }
        public function _set_conversation_id($val){ if(is_null($this->conversation_id)) $this->conversation_id = $val; }
    private $new_flag;
        public function _get_new_flag(){ return $this->new_flag; }
        public function _set_new_flag($val){ if($val === 1 || $val === 0) $this->new_flag = $val; }
    private $timestamp;
        public function _get_timestamp(){ return $this->timestamp; }
        public function _set_timestamp($val){ if(is_null($this->timestamp)) $this->timestamp = $val; }
    private $lastread_timestamp;
        public function _get_lastread_timestamp(){ return $this->lastread_timestamp; }
        public function _set_lastread_timestamp($val){ $this->lastread_timestamp = $val; }

    static public function createNewObj(User $user, UserMessageConversation $conversation, $new_flag = 1) {
        // check if user is already subscribed to the conversation and return the object
        $existing_user = UserMessageConversationUser::loadBy(Array("uid", "conversation_id"), Array($user->id, $conversation->id));
        if(!is_null($existing_user)) return $existing_user;

        // else make a new user
        $timestamp = time();
        $conversation_user = new UserMessageConversationUser(Array(
            "id" => NULL,
            "uid" => $user->id,
            "conversation_id" => $conversation->id,
            "new_flag" => $new_flag,
            "timestamp" => $timestamp,
            "lastread_timestamp" => $timestamp,
        ));
        UserMessageConversationUser::insertObj($conversation_user);
        return $conversation_user;
    }

    static public function deleteObj($conversation_user) {
        $cxn = new Connection();
        $cxn->connect();
        $cxn->delete(UserMessageConversationUser::$_table, "i", Array($conversation_user->id), "where id=?");
        $cxn->close();
    }

    public function loadNewMessages($offset) {
        // get the count of messages that have not been read
        $cxn = new Connection();
        $cxn->connect();
        $count_array = $cxn->select("user_messages", Array("count(*)"), "ii", Array($this->conversation_id, $this->lastread_timestamp), "where conversation_id = ? and timestamp > ?");
        $cxn->close();
        $count = $count_array[0]["count(*)"];

        // load the messages not read + the offset count
        $total = $count + $offset;
        if($offset == 0) return Array();
        $messages = UserMessage::loadArrayBy(Array("conversation_id"), Array($this->conversation_id), false, $total);

        // change the last read time to now
        $this->lastread_timestamp = time();
        $this->new_flag = 0;
        $this->updateDB();

        return $messages;
    }

    public function arrayForm() {
        // get conversation
        $conversation = UserMessageConversation::loadBy(Array("id"), Array($this->conversation_id));

        // if conversation is null, no point in continuing
        if(is_null($conversation)) return NULL;

        return Array(
            "conversation" => $conversation->arrayForm(),
            "new_flag" => $this->new_flag,
            "timestamp" => $this->timestamp,
        );
    }

}

?>
