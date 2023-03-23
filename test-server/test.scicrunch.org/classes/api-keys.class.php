<?php

class APIKey extends DBObject{
    static protected $_table = "api_key_values";
    static protected $_table_fields = Array("id", "key_val", "uid", "created_time", "expires", "expire_time", "active", "project_name", "description");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "isiiiiiss";

    private $_permissions;

    private $id;
        public function _get_id(){ return $this->id; }
        public function _set_id($val){ if(is_null($this->id)) $this->id = $val; }
    private $key_val;
        public function _get_key_val(){ return $this->key_val; }
        public function _set_key_val($val){ if(is_null($this->key_val)) $this->key_val = $val; }
    private $uid;
        public function _get_uid(){ return $this->uid; }
        public function _set_uid($val){ $this->uid = $val; }
    private $created_time;
        public function _get_created_time(){ return $this->created_time; }
        public function _set_created_time($val){ $this->created_time = $val; }
    private $expires;
        public function _get_expires(){ return $this->expires; }
        public function _set_expires($val){ if($val === 1 || $val === 0) $this->expires = $val; }
    private $expire_time;
        public function _get_expire_time(){ return $this->expire_time; }
        public function _set_expire_time($val){ $this->expire_time = $val; }
    private $active;
        public function _get_active(){ return $this->active; }
        public function _set_active($val){ if($val === 1 || $val === 0) $this->active = $val; }
    private $project_name;
        public function _get_project_name(){ return $this->project_name; }
        public function _set_project_name($val){ $this->project_name = $val; }
    private $description;
        public function _get_description(){ return $this->description; }
        public function _set_description($val){ $this->description = $val; }

    static public function createNewObj($uid=0, $expires=0, $expire_time=0, $active=1, $project_name="", $description=""){
        $new_key = APIKey::genNewKey();
        $time = time();
        $key_obj = new APIKey(Array(
            "id" => NULL,
            "key_val" => $new_key,
            "uid" => $uid,
            "created_time" => $time,
            "expires" => $expires,
            "expire_time" => $expire_time,
            "active" => $active,
            "project_name" => $project_name,
            "description" => $description
        ));
        APIKey::insertObj($key_obj);

        return $key_obj;
    }

    static public function deleteObj(){
        return; // keys cannot be deleted at this time
    }

    static private function genNewKey(){
        $unique = false;
        $count = 0;
        do{
            $count += 1;
            if($count > 1000) throw new Exception("could not generate new unique key");    // prevent infinite loop
            $x = APIKey::getRandomKeyString(32);
            if(count(APIKey::loadArrayBy(Array("key_val"), Array($x))) === 0) $unique = true;
        } while(!$unique);
        return $x;
    }

    static public function getRandomKeyString($key_len){
        require_once __DIR__ . "/../lib/vendor/paragonie/random_compat/lib/random.php";
        $count = 0;
        $counter = 0;
        while($count < $key_len){
            $counter += 1;
            if($counter > 1000) throw new Exception("could not generate new key");
            $x = random_bytes($key_len * 10);
            $x = preg_replace("/[^a-zA-Z0-9]+/", "", $x);
            $x = rtrim($x);
            $x = substr($x, 0,$key_len);
            $count = strlen($x);
        }
        return $x;
    }

    public function askPermission($action, $user=NULL, $data=NULL){
        $action_allowed = \APIPermissionActions\checkAction($action, $this, $user, $data);
        return $action_allowed;
    }

    public function checkActive(){
        if($this->active !== 1) return false;
        if($this->expires){
            $now = time();
            if($now > $this->expire_time) return false;
        }
        return true;
    }

    public function loadPermissions(){
        // dont check if _permissions is null, this can be used to refresh the permissions
        $permissions = APIKeyPermission::loadArrayBy(Array("key_id"), Array($this->id));
        $reshaped_permissions = Array();
        foreach($permissions as $p) $reshaped_permissions[$p->permission_type] = $p;
        $this->_permissions = $reshaped_permissions;
    }

    public function permissions($find_permissions=NULL){
        if(is_null($this->_permissions)) $this->loadPermissions();
        $results = Array();

        if(is_null($find_permissions)){
            $results = $this->_permissions;
        }else{
            foreach($find_permissions as $fp){
                if(isset($this->_permissions[$fp]) && $this->_permissions[$fp]->active === 1) $results[$fp] = $this->_permissions[$fp];
                else $results[$fp] = NULL;
            }
        }
        return $results;
    }

    public function hasPermission($perm){
        if(is_null($this->_permissions)) $this->loadPermissions();
        if(isset($this->_permissions[$perm]) && $this->_permissions[$perm]->active === 1) return true;
        return false;
    }

    public function arrayForm(){
        $array_permissions = Array();
        $this->loadPermissions();
        foreach($this->_permissions as $pk => $pv){
            $array_permissions[] = $pv->arrayForm();
        }
        return Array(
            "key_val" => $this->key_val,
            "uid" => $this->uid,
            "created_time" => $this->created_time,
            "expires" => $this->expires,
            "expire_time" => $this->expire_time,
            "active" => $this->active,
            "project_name" => $this->project_name,
            "description" => $this->description,
            "permissions" => $array_permissions
        );
    }

    public function addPermission($type, $data=NULL){
        APIKeyPermission::createNewObj($this->id, $type, $data);
        $this->loadPermissions();
    }

    public function enableDisablePermission($type, $able){
        $this->loadPermissions();
        if(!isset($this->_permissions[$type])) return;
        if($able === "enable"){
            $this->_permissions[$type]->active = 1;
        }elseif($able === "disable"){
            $this->_permissions[$type]->active = 0;
        }
        $this->_permissions[$type]->updateDB();
    }

    public function updatePermissionData($type, $data){
        $this->loadPermissions();
        if(!isset($this->_permissions[$type])) return;
        $this->_permissions[$type]->permission_data = $data;
        $this->_permissions[$type]->updateDB();
    }

    static public function loadByKey($keyval){
        return APIKey::loadBy(Array("key_val"), Array($keyval), true, false, true);
    }

    static public function userKeyCount(User $user) {
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select(self::$_table, Array("count(*)"), "i", Array($user->id), "where uid=?");
        $cxn->close();
        return $count[0]["count(*)"];
    }

}

?>
