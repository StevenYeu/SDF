<?php

class ResourceSuggestion extends DBObject {
    static protected $_table = "resource_suggestions";
    static protected $_table_fields = Array("id", "rid", "cid", "typeid", "status", "timestamp", "email", "resource_name", "resource_url", "description", "defining_citation", "curator_comment");
    static protected $_primary_key_field = "id";
    static protected $_table_types = "iiiissssssss";

    
    const STATUS_REJECTED = "rejected";
    const STATUS_APPROVED = "approved";
    const STATUS_PENDING = "pending";
    static public $allowed_statuses = Array(
        self::STATUS_REJECTED,
        self::STATUS_APPROVED,
        self::STATUS_PENDING,
    );

    private $id;
        public function _get_id() { return $this->id; }
        public function _set_id($val) { if(is_null($this->id)) $this->id = $val; }
    private $rid;
        public function _get_rid() { return $this->rid; }
        public function _set_rid($val) { $this->rid = $val; }
    private $cid;
        public function _get_cid() { return $this->cid; }
        public function _set_cid($val) { if(is_null($this->cid)) $this->cid = $val; }
    private $typeid;
        public function _get_typeid() { return $this->typeid; }
        public function _set_typeid($val) { if(is_null($this->typeid)) $this->typeid = $val; }
    private $status;
        public function _get_status() { return $this->status; }
        public function _set_status($val) {
            if($this->status === self::STATUS_APPROVED) return; // cannot change once approved
            if(!in_array($val, self::$allowed_statuses)) return;    // bad status
            $this->status = $val;
        }
    private $timestamp;
        public function _get_timestamp() { return $this->timestamp; }
        public function _set_timestamp($val) { if(is_null($this->timestamp)) $this->timestamp = $val; }
    private $email;
        public function _get_email() { return $this->email; }
        public function _set_email($val) { if(is_null($this->email)) $this->email = $val; }
    private $resource_name;
        public function _get_resource_name() { return $this->resource_name; }
        public function _set_resource_name($val) { if(is_null($this->resource_name)) $this->resource_name = $val; }
    private $resource_url;
        public function _get_resource_url() { return $this->resource_url; }
        public function _set_resource_url($val) { if(is_null($this->resource_url)) $this->resource_url = $val; }
    private $description;
        public function _get_description() { return $this->description; }
        public function _set_description($val) { if(is_null($this->description)) $this->description = $val; }
    private $defining_citation;
        public function _get_defining_citation() { return $this->defining_citation; }
        public function _set_defining_citation($val) { if(is_null($this->defining_citation)) $this->defining_citation = $val; }
    private $curator_comment;
        public function _get_curator_comment() { return $this->curator_comment; }
        public function _set_curator_comment($val) { $this->curator_comment = $val; }

    static public function createNewObj($cid, $email, $typeid, $resource_name, $resource_url, $description, $defining_citation) {
        $time = time();
        $obj = new ResourceSuggestion(Array(
            "id" => NULL,
            "rid" => NULL,
            "cid" => $cid,
            "typeid" => $typeid,
            "status" => self::STATUS_PENDING,
            "timestamp" => $time,
            "email" => $email,
            "resource_name" => $resource_name,
            "resource_url" => $resource_url,
            "description" => $description,
            "defining_citation" => $defining_citation,
            "curator_comment" => NULL,
        ));
        ResourceSuggestion::insertObj($obj);
        return $obj;
    }

    static public function deleteObj($obj) {
        $cxn = new Connection();
        $cxn->connect();
        $cxn->delete(self::$_table, "i", Array($obj->id), "where id=?");
        $cxn->close();
    }

    public function createResource() {
        // if this suggestion already has a resource id, don't create new one, just return the old one
        if($this->rid) {
            $resource = new Resource();
            $resource->getByID($this->rid);
            return $resource;
        }

        // get user from email (user may not exist with email)
        $user = new User();
        $user->getByEmail($this->email);

        // prepare resource data array
        $varsR = Array();
        $varsR["uid"] = $user->id ? $user->id : 0;
        $varsR["email"] = $this->email;
        $varsR["type"] = "Resource";
        $varsR["typeID"] = 1;
        $varsR["cid"] = $this->cid;

        // create the resource
        $resource = new Resource();
        $resource->create($varsR);
        $resource->insertDB();

        // add resource to resource suggestion and save
        $this->rid = $resource->id;
        $this->updateDB();

        // prepare columns data array
        $resource_columns = Array();
        $resource_columns["Resource Name"] = Array($this->resource_name);
        $resource_columns["Description"] = Array($this->description);
        $resource_columns["Resource URL"] = Array($this->resource_url);

        // insert resource columns
        $resource->columns = $resource_columns;
        $resource->insertColumns2();

        return $resource;
    }
}

?>
