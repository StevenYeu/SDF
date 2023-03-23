<?php

class APIKeyPermission extends DBObject {
    static protected $_table = "api_key_permissions";
    static protected $_table_fields = Array("id", "key_id", "permission_type", "permission_data", "active", "created_time");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iissii";

    static public $allowed_permission_types = Array(
        "user",
        "curator",
        "ilx",
        "api-moderator",
        "rrid",
        "resource-mentions",
        "term",
        "resource-adder",
        "dataservices",
    );

    private $id;
        public function _get_id(){ return $this->id; }
        public function _set_id($val){ if(is_null($this->id)) $this->id = $val; }
    private $key_id;
        public function _get_key_id(){ return $this->key_id; }
        public function _set_key_id($val){ if(is_null($this->key_id)) $this->key_id = $val; }
    private $permission_type;
        public function _get_permission_type(){ return $this->permission_type; }
        public function _set_permission_type($val){
            if(in_array($val, APIKeyPermission::$allowed_permission_types) && is_null($this->permission_type)) $this->permission_type = $val;
        }
    private $permission_data;
        public function _get_permission_data(){ return $this->permission_data; }
        public function _set_permission_data($val){ $this->permission_data = $val; }
    private $active;
        public function _get_active(){ return $this->active; }
        public function _set_active($val){ if($val === 0 || $val === 1) $this->active = $val; }
    private $created_time;
        public function _get_created_time(){ return $this->created_time; }
        public function _set_created_time($val){ $this->created_time = $val; }

    static public function createNewObj($key_id, $permission_type, $permission_data=NULL, $active=1){
        if(is_null(APIKey::loadBy(Array("id"), Array($key_id)))) throw new Exception("API key id does not exist");
        if(!in_array($permission_type, APIKeyPermission::$allowed_permission_types)) throw new Exception("permission type does not exist");
        if(!is_null(APIKeyPermission::loadBy(Array("key_id", "permission_type"), Array($key_id, $permission_type)))) throw new Exception("permission already exists for this key");

        $time = time();
        $permission_obj = new APIKeyPermission(Array(
            "id" => NULL,
            "key_id" => $key_id,
            "permission_type" => $permission_type,
            "permission_data" => $permission_data,
            "active" => $active,
            "created_time" => $time
        ));
        APIKeyPermission::insertObj($permission_obj);
        return $permission_obj;
    }

    static public function deleteObj(){

    }

    public function arrayForm(){
        return Array(
            "permission_type" => $this->permission_type,
            "permission_data" => $this->permission_data,
            "active" => $this->active,
            "created_time" => $this->created_time
        );
    }
}

?>
