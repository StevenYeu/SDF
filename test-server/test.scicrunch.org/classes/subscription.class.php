<?php

require_once __DIR__ . "/data.subscription.class.php";
require_once __DIR__ . "/resource-mention.data.subscription.class.php";
require_once __DIR__ . "/saved-search-data.data.subscription.class.php";
require_once __DIR__ . "/saved-search-summary.data.subscription.class.php";
require_once __DIR__ . "/saved-search-literature.data.subscription.class.php";

class Subscription extends DBObject {
    static protected $_table = "subscriptions";
    static protected $_table_fields = Array("id", "uid", "type", "fid", "scicrunch_notify", "email_notify", "new_data_scicrunch", "new_data_email", "data_scicrunch", "data_email", "time", "cid");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iissiiiissii";

    static private $allowed_types = Array(
        "resource-mention" => Array(
            "fid_field" => "rid",
            "ftable" => "resources",
            "data-class" => "ResourceMentionData"
        ),
        "saved-search-data" => Array(
            "fid_field" => "id",
            "ftable" => "saved_searches",
            "data-class" => "SavedSearchDataData"
        ),
        "saved-search-summary" => Array(
            "fid_field" => "id",
            "ftable" => "saved_searches",
            "data-class" => "SavedSearchSummaryData"
        ),
        "saved-search-literature" => Array(
            "fid_field" => "id",
            "ftable" => "saved_searches",
            "data-class" => "SavedSearchLiteratureData"
        ),
    );

    function __construct($vals, $opt_vals=NULL){
        parent::__construct($vals, $opt_vals);
        $this->initDataObj($this->type, $this->data_scicrunch, $this->data_email);
    }

    private $data_obj_scicrunch;
    private $data_obj_email;

    private $id;
        public function _get_id(){ return $this->id; }
        public function _set_id($val){ if(is_null($this->id)) $this->id = $val; }
    private $uid;
        public function _get_uid(){ return $this->uid; }
        public function _set_uid($val){ if(is_null($this->uid)) $this->uid = $val; }
    private $type;
        public function _get_type(){ return $this->type; }
        public function _set_type($val){
            if(is_null($this->type) && in_array($val, array_keys(Subscription::$allowed_types))){
                $this->type = $val;
            }
        }
    private $fid;
        public function _get_fid(){ return $this->fid; }
        public function _set_fid($val){
            if(!is_null($this->fid)) return;
            if(is_null($this->type)) throw new Exception("cannot set foreign id without type");
            $unique = $this->fidUnique($this->type, $val);
            if(!$unique) throw new Exception("bad foreign id");
            $this->fid = $val;
        }
    private $scicrunch_notify;
        public function _get_scicrunch_notify(){ return $this->scicrunch_notify; }
        public function _set_scicrunch_notify($val){
            if($val === 0 || $val === 1) $this->scicrunch_notify = $val;
            if($this->scicrunch_notify === 0){
                $this->_set_data_scicrunch(NULL);
                $this->_set_new_data_scicrunch(0);
            }
        }
    private $email_notify;
        public function _get_email_notify(){ return $this->email_notify; }
        public function _set_email_notify($val){
            if($val === 0 || $val === 1) $this->email_notify = $val;
            if($this->email_notify === 0){
                $this->_set_data_email(NULL);
                $this->_set_new_data_scicrunch(0);
            }
        }
    private $new_data_scicrunch;
        public function _get_new_data_scicrunch(){ return $this->new_data_scicrunch; }
        public function _set_new_data_scicrunch($val){ if($val === 0 || $val === 1) $this->new_data_scicrunch = $val; }
    private $new_data_email;
        public function _get_new_data_email(){ return $this->new_data_email; }
        public function _set_new_data_email($val){ if($val === 0 || $val === 1) $this->new_data_email = $val; }
    private $data_scicrunch;
        public function _get_data_scicrunch(){ return $this->data_scicrunch; }
        public function _set_data_scicrunch($val){ $this->data_scicrunch = $val; }
    private $data_email;
        public function _get_data_email(){ return $this->data_email; }
        public function _set_data_email($val){ $this->data_email = $val; }
    private $time;
        public function _get_time(){ return $this->time; }
        public function _set_time($val){ $this->time = $val; }
    private $cid;
        public function _get_cid(){ return $this->cid; }
        public function _set_cid($val){ $this->cid = $val; }

    static public function createNewObj($uid, $type, $fid, $scicrunch_notify=1, $email_notify=0, $data=NULL, $cid=0){
        if(Subscription::exists($uid, $type, $fid)) return NULL;
        $time = time();
        $sub = new Subscription(Array(
            "id" => NULL,
            "uid" => $uid,
            "type" => $type,
            "fid" => $fid,
            "scicrunch_notify" => $scicrunch_notify,
            "email_notify" => $email_notify,
            "new_data_scicrunch" => 0,
            "new_data_email" => 0,
            "data_scicrunch" => $data,
            "data_email" => $data,
            "time" => $time,
            "cid" => $cid,
        ));
        $sub->data_obj_scicrunch->initData($sub);
        $sub->data_obj_email->initData($sub);
        Subscription::insertObj($sub);

        return $sub;
    }

    static public function deleteObj($obj){
        $cxn = new Connection();
        $cxn->connect();
        $cxn->delete(Subscription::$_table, "i", Array($obj->id), "where id=?");
        $cxn->close();
    }

    static public function exists($uid, $type, $fid){
        $count = count(Subscription::loadArrayBy(Array("uid", "type", "fid"), Array($uid, $type, $fid)));
        if($count !== 0) return true;
        return false;
    }

    private function fidUnique($type, $fid){
        $table = Subscription::$allowed_types[$type]["ftable"];
        $key_field = Subscription::$allowed_types[$type]["fid_field"];
        $field_type = is_string($fid) ? "s" : "i";
        $where_string = "where " . $key_field . "=?";

        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select($table, Array("count(*)"), $field_type, Array($fid), $where_string);
        $cxn->close();

        return $count[0]["count(*)"] === 1;
    }

    public function arrayForm(){
        return Array(
            "type" => $this->_get_type(),
            "identifier" => $this->_get_fid(),
            "scicrunch_notify" => $this->_get_scicrunch_notify(),
            "email_notify" => $this->_get_email_notify(),
            "new_data_scicrunch" => $this->_get_new_data_scicrunch(),
            "new_data_email" => $this->_get_new_data_email(),
            "data_scicrunch" => $this->_get_data_scicrunch(),
            "data_email" => $this->_get_data_email(),
            "time" => $this->_get_time()
        );
    }

    static public function newData($type, $data){
        if($type === "resource-mention"){
            $resource = new Resource();
            $resource->getByID($data["rid"]);
            if(!$resource->id) throw new Exception("bad resource ID");
            $subs = Subscription::loadArrayBy(Array("type", "fid"), Array($type, $resource->rid));
            foreach($subs as $sub) $sub->setNewData($data["mentionid"]);
        }
    }

    public function setNewData($data){
        if($this->_get_scicrunch_notify() === 1){
            //$new_data = $this->data_obj_scicrunch->setNewData($data) || $this->_get_new_data_scicrunch() ? 1 : 0;
            $new_data = $this->data_obj_scicrunch->setNewData($data) ? 1 : 0;
            $this->_set_new_data_scicrunch($new_data);
            $this->_set_data_scicrunch($this->data_obj_scicrunch->json());
        }
        if($this->_get_email_notify() === 1){
            //$new_data = $this->data_obj_email->setNewData($data) || $this->_get_new_data_email() ? 1 : 0;
            $new_data = $this->data_obj_email->setNewData($data) ? 1 : 0;
            $this->_set_new_data_email($new_data);
            $this->_set_data_email($this->data_obj_email->json());
        }

        $this->updateDB();
    }

    public function searchNewData() {
        if(!method_exists($this->data_obj_scicrunch, "searchNewData")) return;
        if($this->_get_scicrunch_notify() === 1){
            $new_data = $this->data_obj_scicrunch->searchNewData($this);
            $new_data_flag = $this->data_obj_scicrunch->setNewData($new_data) ? 1 : 0;
            $this->_set_new_data_scicrunch($new_data_flag);
            $this->_set_data_scicrunch($this->data_obj_scicrunch->json());
        }
        if($this->_get_email_notify() === 1){
            $new_data = $this->data_obj_email->searchNewData($this);
            $new_data_flag = $this->data_obj_email->setNewData($new_data) ? 1 : 0;
            $this->_set_new_data_email($new_data_flag);
            $this->_set_data_email($this->data_obj_email->json());
        }
        $this->updateDB();
    }

    private function initDataObj($type, $data_obj_scicrunch, $data_obj_email){
        $class = Subscription::$allowed_types[$type]["data-class"];
        $this->data_obj_scicrunch = new $class($data_obj_scicrunch);
        $this->data_obj_email = new $class($data_obj_email);
    }

    public function getNewDataScicrunch(){
        return $this->data_obj_scicrunch->getNewData();
    }

    public function getNewDataEmail(){
        return $this->data_obj_email->getNewData();
    }

    public function resetNewDataScicrunch(){
        $this->data_obj_scicrunch->resetData();
        $this->_set_new_data_scicrunch(0);
        $this->_set_data_scicrunch($this->data_obj_scicrunch->json());
        $this->updateDB();
    }

    public function resetNewDataEmail(){
        $this->data_obj_email->resetData();
        $this->_set_new_data_email(0);
        $this->_set_data_email($this->data_obj_email->json());
        $this->updateDB();
    }

    static public function getAllowedTypes(){
        return array_keys(Subscription::$allowed_types);
    }

    static public function userUpdates($user, $type=NULL, $notify_type=NULL){
        $uid = $user->id;
        if($notify_type === "scicrunch") $notify_type_string = "new_data_scicrunch";
        elseif($notify_type === "email") $notify_type_string = "new_data_email";
        else $notify_type_string = NULL;
        
        if(is_null($notify_type_string)){
            $args = Array($uid, 1, 1);
            $args_types = "iii";
            $where_string = "where uid=? and (new_data_scicrunch=? or new_data_email=?)";
        }else{
            $args = Array($uid, 1);
            $args_types = "ii";
            $where_string = "where uid=? and ".$notify_type_string."=?";
        }
        if(!is_null($type)){
            $args[] = $type;
            $args_types .= "s";
            $where_string .= " and type=?";
        }

        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select(Subscription::$_table, Array("count(*)"), $args_types, $args, $where_string);
        $cxn->close();
        return $count[0]["count(*)"];
    }

    static public function uidsPendingEmails(){
        $cxn = new Connection();
        $cxn->connect();
        $users = $cxn->select(Subscription::$_table, Array("distinct uid"), "", Array(), "where new_data_email = 1");
        $cxn->close();

        $users_reshaped = Array();
        foreach($users as $user) $users_reshaped[] = $user["uid"];

        return $users_reshaped;
    }

    static public function clearNotification($sub_id, $user){
        $sub = Subscription::loadBy(Array("id", "uid"), Array($sub_id, $user->id));
        if(!is_null($sub)) $sub->resetNewDataScicrunch();
    }
}

?>
