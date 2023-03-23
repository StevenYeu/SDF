<?php

class UserMessageConversation extends DBObject {
    static protected $_table = "user_messages_conversations";
    static protected $_table_fields = Array("id", "name", "foreign_table", "foreign_key");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "issi";

    const TABLE_RESOURCE_OWNERS = "resource-owners";
    const TABLE_RESOURCE_SUGGESTIONS = "resource-suggestions";
    const TABLE_RESOURCES = "resources";
    static public $allowed_foreign_tables = Array(
        self::TABLE_RESOURCE_OWNERS,
        self::TABLE_RESOURCE_SUGGESTIONS,
        self::TABLE_RESOURCES,
    );

    private $id;
        public function _get_id() { return $this->id; }
        public function _set_id($val) { if(is_null($this->id)) $this->id = $val; }
    private $name;
        public function _get_name() { return $this->name; }
        public function _set_name($val) { $this->name = $val; }
    private $foreign_table;
        public function _get_foreign_table() { return $this->foreign_table; }
        public function _set_foreign_table($val) {
            if(is_null($this->foreign_table) && in_array($val, self::$allowed_foreign_tables)) $this->foreign_table = $val;
        }
    private $foreign_key;
        public function _get_foreign_key() { return $this->foreign_key; }
        public function _set_foreign_key($val) { if(is_null($this->foreign_key)) $this->foreign_key = $val; }

    static public function createNewObj($name = NULL, $foreign_table = NULL, $foreign_key = NULL) {
        if(is_null($foreign_table) xor is_null($foreign_key)) return NULL;  // one can't be set without the other

        // make sure conversation is unique for foreign table/key
        if(!is_null($foreign_table)) {
            $existing = UserMessageConversation::loadBy(Array("foreign_table", "foreign_key"), Array($foreign_table, $foreign_key));
            if(!is_null($existing)) return $existing;
        }

        $message_conversation = new UserMessageConversation(Array(
            "id" => NULL,
            "name" => $name,
            "foreign_table" => $foreign_table,
            "foreign_key" => $foreign_key,
        ));
        UserMessageConversation::insertObj($message_conversation);
        return $message_conversation;
    }

    static public function deleteObj() {

    }

    public function sendMessage($message_string, User $user = NULL) {
        // create the message
        $message = UserMessage::createNewObj($message_string, $this, $user);

        // get all the conversations subscribers
        $subscribers = UserMessageConversationUser::loadArrayBy(Array("conversation_id"), Array($this->id));

        // set new message flag for each subscriber except the current one
        foreach($subscribers as $sub) {
            // skip subscriber if it's the message sender
            if(!is_null($user) && $user->id === $sub->uid) continue;

            // set new data flag
            $sub->new_flag = 1;
            $sub->updateDB();
        }

        return $message;
    }

    public function addUser(User $user, $new_flag = 1) {
        $conversation_user = UserMessageConversationUser::createNewObj($user, $this, $new_flag);
        return $conversation_user;
    }

    public function removeUser(User $user) {
        $conversation_user = UserMessageConversationUser::loadBy(Array("uid", "conversation_id"), Array($user->id, $this->id));
        if(!is_null($conversation_user)) UserMessageConversationUser::deleteObj($conversation_user);
    }

    static public function newConversation(Array $to_users, User $from_user = NULL, $message_string, $conversation_name = NULL, $foreign_table = NULL, $foreign_key = NULL) {
        // conversation must have at least two people
        if(count($to_users) === 0) return NULL;

        // create the conversation
        $conversation = UserMessageConversation::createNewObj($conversation_name, $foreign_table, $foreign_key);

        // add the creater
        if(!is_null($from_user)) $conversation->addUser($from_user, 0);

        // add each user to the conversation with new flag = 1
        foreach($to_users as $user) $conversation->addUser($user, 1);

        // send the initial message
        $conversation->sendMessage($message_string, $from_user);

        return $conversation;
    }

    public function arrayForm() {
        $array_form = Array(
            "id" => $this->id,
            "name" => $this->name,
            "ref" => $this->foreign_table,
            "refid" => $this->foreign_key,
        );

        if($this->foreign_table && $this->foreign_key) {
            $foreign_reference = UserMessageConversationForeignReference::createObj($this->foreign_table, $this->foreign_key);
            if(!is_null($foreign_reference)) $array_form["ref_link"] = $foreign_reference->refLink();
        }

        return $array_form;
    }

    static public function newCuratorConversation(User $from_user, $foreign_table, $foreign_key) {
        // get all curators (level == 1)
        $cxn = new Connection();
        $cxn->connect();
        $user_rows = $cxn->select("users", Array("*"), "", Array(), "where level = 1 or level = 2");
        $cxn->close();

        // create the user objects
        $to_users = Array();
        foreach($user_rows as $row) {
            $user = new User();
            $user->createFromRow($row);
            $to_users[] = $user;
        }

        // create a foreign reference object
        $foreign_reference = UserMessageConversationForeignReference::createObj($foreign_table, $foreign_key);
        if(is_null($foreign_reference)) return NULL;

        // message and conversation name
        $conversation_name = $foreign_reference->conversationTitle();
        $message = $foreign_reference->conversationMessage();

        $conversation = self::newConversation($to_users, $from_user, $message, $conversation_name, $foreign_table, $foreign_key);
        return $conversation;
    }

}

class UserMessageConversationForeignReference {
    private $foreign_table;
    private $foreign_key;
    private $foreign_data;

    private function __construct($table, $key) {
        if(!in_array($table, UserMessageConversation::$allowed_foreign_tables)) throw new Exception("invalid foreign table");
        $this->foreign_table = $table;
        $this->foreign_key = $key;
        $this->foreign_data = Array();

        $this->initForeignData();
    }

    static public function createObj($table, $key) {
        try {
            return new UserMessageConversationForeignReference($table, $key);
        } catch(Exception $e) {
            return NULL;
        }
    }

    private function initForeignData() {
        $exception = new Exception("invalid foreign key");
        switch($this->foreign_table) {
            case UserMessageConversation::TABLE_RESOURCE_OWNERS:
                $rur = ResourceUserRelationship::loadBy(Array("id"), Array($this->foreign_key));
                if(is_null($rur)) throw $exception;
                $this->foreign_data["resource-user-relationship"] = $rur;
                $resource = new Resource();
                $resource->getByID($rur->rid);
                $resource->getColumns();
                $this->foreign_data["resource"] = $resource;
                $user = new User();
                $user->getByID($rur->uid);
                $this->foreign_data["user"] = $user;
                break;
            case UserMessageConversation::TABLE_RESOURCE_SUGGESTIONS:
                $rs = ResourceSuggestion::loadBy(Array("id"), Array($this->foreign_key));
                if(is_null($rs)) throw $exception;
                $this->foreign_data["resource-suggestion"] = $rs;
                break;
            case UserMessageConversation::TABLE_RESOURCES:
                $resource = new Resource();
                $resource->getByID($this->foreign_key);
                if(!$resource->id) throw $exception;
                $resource->getColumns();
                $this->foreign_data["resource"] = $resource;
                break;
        }
    }

    public function conversationTitle() {
        switch($this->foreign_table) {
            case UserMessageConversation::TABLE_RESOURCE_OWNERS:
                return "Ownership request for resource " . $this->foreign_data["resource"]->columns["Resource Name"] . " (" . $this->foreign_data["resource"]->rid . ") from user " . $this->foreign_data["user"]->getFullName() . " (" . $this->foreign_data["user"]->email . ")";
            case UserMessageConversation::TABLE_RESOURCE_SUGGESTIONS:
                return "Resource suggestion " . $this->foreign_data["resource-suggestion"]->resource_name;
            case UserMessageConversation::TABLE_RESOURCES:
                return "Resource " . $this->foreign_data["resource"]->columns["Resource Name"] . " (" . $this->foreign_data["resource"]->rid . ")";
        }
    }

    public function conversationMessage() {
        switch($this->foreign_table) {
            case UserMessageConversation::TABLE_RESOURCE_OWNERS:
                $message = "adding a user as a owner of a resource";
                break;
            case UserMessageConversation::TABLE_RESOURCE_SUGGESTIONS:
                $message = "adding a resource from a resource suggestion";
                break;
            case UserMessageConversation::TABLE_RESOURCES:
                $message = "a resource";
                break;
        }
        return "This conversation was created to discuss " . $message;
    }

    public function refLink() {
        switch($this->foreign_table) {
            case UserMessageConversation::TABLE_RESOURCE_OWNERS:
                return PROTOCOL . "://" . FQDN . "/account/curator/pending-owner-requests";
            case UserMessageConversation::TABLE_RESOURCE_SUGGESTIONS:
                return PROTOCOL . "://" . FQDN . "/account/curator/resource-suggestions";
            case UserMessageConversation::TABLE_RESOURCES:
                return PROTOCOL . "://" . FQDN . "/browse/resourcesedit/" . $this->foreign_data["resource"]->rid;
        }
    }

    public function toUsers() {
        // get the list of users to add to the initial conversation

        // get all curators, by default they are always added
        $cxn = new Connection();
        $cxn->connect();
        $user_rows = $cxn->select("users", Array("*"), "", Array(), "where level = 1");
        $cxn->close();

        // create the user objects
        $to_users = Array();
        foreach($user_rows as $row) {
            $user = new User();
            $user->createFromRow($row);
            $to_users[] = $user;
        }

        // get resource owners
        if($this->foreign_table == UserMessageConversation::TABLE_RESOURCES) {
            $cxn->connect();
            $uids = $cxn->select("resource_user_relationships", Array("uid"), "i", Array($this->foreign_key), "where type='owner' and rid=?");
            $cxn->close();
            foreach($uids as $uid) {
                $user = new User();
                $user->getByID($uid);
                if($user->id) $to_users[] = $user;
            }
        }
    }
}

?>
