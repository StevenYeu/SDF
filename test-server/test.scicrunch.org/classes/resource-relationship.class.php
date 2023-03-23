<?php

class ResourceRelationship extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "resource_relationships";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"            => self::fieldDef("id", "i", true),
            "uid"           => self::fieldDef("uid", "i", true),
            "reltype_id"    => self::fieldDef("reltype_id", "i", true),
            "canon_id"      => self::fieldDef("canon_id", "i", true),
            "id1"           => self::fieldDef("id1", "s", true),
            "id2"           => self::fieldDef("id2", "s", true),
            "timestamp"     => self::fieldDef("timestamp", "i", true),
        );
    }
    /* no setter validation since every field is read only */
    /* validate in createNewObj */

    private $_reltype;

    public static function createNewObj(User $user, $id1, $id2, ResourceRelationshipString $relationship_type, $canon_id) {
        if(!self::validateIDs($id1, $id2, $relationship_type, $canon_id)) return NULL;
        $timestamp = time();

        $obj = self::insertObj(Array(
            "id" => NULL,
            "uid" => $user->id,
            "reltype_id" => $relationship_type->id,
            "canon_id" => $canon_id,
            "id1" => $id1,
            "id2" => $id2,
            "timestamp" => $timestamp,
        ));
        return $obj;
    }

    public static function deleteObj($obj, User $user) {
        $new_timestamp = time();

        $cxn = new Connection();
        $cxn->connect();
        $cxn->insert("resource_relationships_history", "iiiiissiii", Array(
            NULL,
            $obj->id,
            $obj->uid,
            $obj->reltype_id,
            $obj->canon_id,
            $obj->id1,
            $obj->id2,
            $user->id,
            $obj->timestamp,
            $new_timestamp,
        ));
        $cxn->close();

        parent::deleteObj($obj);
    }

    public function arrayForm() {
        $name1 = "";
        $name2 = "";
        $uuid1 = NULL;
        $uuid2 = NULL;
        if($this->reltype()->type1 == "res") {
            $resource1 = ResourceDBO::loadBy(Array("rid"), Array($this->id1));
            if(!is_null($resource1)) {
                $name1 = $resource1->getColumn("Resource Name", true);
                $uuid1 = $resource1->uuid;
            }
        }
        if($this->reltype()->type2 == "res") {
            $resource2 = ResourceDBO::loadBy(Array("rid"), Array($this->id2));
            if(!is_null($resource2)) {
                $name2 = $resource2->getColumn("Resource Name", true);
                $uuid2 = $resource2->uuid;
            }
        }

        return Array (
            "id1" => $this->id1,
            "id2" => $this->id2,
            "name1" => $name1,
            "name2" => $name2,
            "uuid1" => $uuid1,
            "uuid2" => $uuid2,
            "canon_id" => $this->canon_id,
            "time" => $this->timestamp,
            "reltype" => $this->reltype()->arrayForm(),
        );
    }

    public function reltype() {
        if(is_null($this->_reltype)) {
            $this->_reltype = ResourceRelationshipString::loadBy(Array("id"), Array($this->reltype_id));
        }
        return $this->_reltype;
    }

    private static function validateIDs($id1, $id2, ResourceRelationshipString $relationship_type, $canon_id) {
        /* no relationship with self */
        if($id1 == $id2) return false;

        /* make sure resources actually exist */
        if($relationship_type->type1 == "res" && !self::validateResID($id1)) return false;
        if($relationship_type->type2 == "res" && !self::validateResID($id2)) return false;
        if($relationship_type->type1 == "funding" && !self::validateFundingID($id1)) return false;
        if($relationship_type->type2 == "funding" && !self::validateFundingID($id2)) return false;

        return true;
    }

    private static function validateResID($id) {
        $cxn = new Connection();
        $cxn->connect();
        $result = $cxn->select("resources", Array("count(*)"), "s", Array($id), "where rid=?");
        $cxn->close();
        if($result[0]["count(*)"] > 0) {
            return true;
        }
        return false;
    }

    private static function validateFundingID($id) {
        if(count(explode("|||", $id)) != 2) return false;
        return true;
    }

    public static function loadByID($id, $offset, $count, $canon_only) {
        $cxn = new Connection();
        $cxn->connect();
        if($canon_only) {
            $where_string = "where (id1=? and canon_id=0) or (id2=? and canon_id=1) order by id asc limit ?,?";
        } else {
            $where_string = "where id1=? or id2=? order by id asc limit ?,?";
        }
        $rows = $cxn->select("resource_relationships", Array("*"), "ssii", Array($id, $id, $offset, $count), $where_string);
        $cxn->close();

        $relationships = Array();
        foreach($rows as $row) {
            $relationships[] = new ResourceRelationship($row);
        }

        return $relationships;
    }

    public static function getCountByID($id, $canon_only) {
        $cxn = new Connection();
        $cxn->connect();
        if($canon_only) {
            $where_string = "where (id1=? and canon_id=0) or (id2=? and canon_id=1)";
        } else {
            $where_string = "where id1=? or id2=?";
        }
        $rows = $cxn->select("resource_relationships", Array("count(*)"), "ss", Array($id, $id), $where_string);
        $cxn->close();
        return $rows[0]["count(*)"];
    }
}
ResourceRelationship::init();

?>
