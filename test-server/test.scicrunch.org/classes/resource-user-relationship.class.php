<?php

class ResourceUserRelationship extends DBObject{
    static protected $_table = "resource_user_relationships";
    static protected $_table_fields = Array("id", "rid", "uid", "type", "timestamp", "text_data");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iiisis";

    const TYPE_OWNER = "owner";
    const TYPE_PENDING_OWNER = "pending-owner";
    static public $allowed_types = Array(self::TYPE_OWNER, self::TYPE_PENDING_OWNER);
    static private $history_table = "resource_user_relationships_history";

    private $id;
        public function _get_id(){ return $this->id; }
        public function _set_id($val){ if(is_null($this->id)) $this->id = $val; }
    private $rid;
        public function _get_rid(){ return $this->rid; }
        public function _set_rid($val){
            if(Resource::idExists($val)) $this->rid = $val;
            else throw new Exception("invalid rid");
        }
    private $uid;
        public function _get_uid(){ return $this->uid; }
        public function _set_uid($val){
            if(User::idExists($val)) $this->uid = $val;
            else throw new Exception("invalid uid");
        }
    private $type;
        public function _get_type(){ return $this->type; }
        public function _set_type($val){
            if(in_array($val, ResourceUserRelationship::$allowed_types)) $this->type = $val;
            else throw new Exception("invalid resource owner relationship type");
        }
    private $timestamp;
        public function _get_timestamp(){ return $this->timestamp; }
        public function _set_timestamp($val){ $this->timestamp = $val; }
    private $text_data;
        public function _get_text_data(){ return $this->text_data; }
        public function _set_text_data($val){ $this->text_data = $val; }


    static public function deleteObj($obj, $uid){
        $cxn = new Connection();
        $cxn->connect();
        $new_timestamp = time();
        $cxn->insert(
            ResourceUserRelationship::$history_table,
            "iiiiisisi",
            Array(NULL, $uid, $obj->id, $obj->rid, $obj->uid, $obj->type, $obj->timestamp, $obj->text_data, $new_timestamp)
        );
        $cxn->delete(ResourceUserRelationship::$_table, "i", Array($obj->id), "where id=?");
        $cxn->close();
    }

    static public function createNewObj($rid, $uid, $type, $text_data=NULL){
        if(ResourceUserRelationship::exists($rid, $uid, $type)) return NULL;
        $time = time();
        $rur = new ResourceUserRelationship(Array(
            "id" => NULL,
            "rid" => $rid,
            "uid" => $uid,
            "type" => $type,
            "timestamp" => $time,
            "text_data" => $text_data,
        ));
        ResourceUserRelationship::insertObj($rur);

        return $rur;
    }

    static function isResourceOwner($rid, $uid){
        return ResourceUserRelationship::exists($rid, $uid, "owner");
    }

    static public function exists($rid, $uid, $type){
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select(ResourceUserRelationship::$_table, Array("count(*)"), "iis", Array($rid, $uid, $type), "where rid=? and uid=? and type=?");
        $cxn->close();
        if($count[0]["count(*)"] > 0) return true;
        return false;
    }

    public function arrayForm(){
        $user_holder = new User();
        $user_holder->getByID($this->uid);
        $resource = new Resource();
        $resource->getByID($this->rid);
        if($user_holder->id && $resource->id) return Array(
            "id" => $this->id,
            "uid" => $this->uid,
            "name" => $user_holder->getFullName(),
            "type" => $this->type,
            "rid" => $resource->rid,
            "email" => $user_holder->email,
            "text_data" => $this->text_data,
        );
        else return NULL;
    }

    static public function reviewPending($rur, $review, $uid){
        if ($rur->type !== self::TYPE_PENDING_OWNER) return NULL;
        if ($review === "reject") {
            self::deleteObj($rur, $uid);
        } elseif ($review === "accept") {
            $owner = self::createNewObj($rur->rid, $rur->uid, self::TYPE_OWNER);
            self::deleteObj($rur, $uid);
            return $owner;
        }
        return NULL;
    }
}

?>
