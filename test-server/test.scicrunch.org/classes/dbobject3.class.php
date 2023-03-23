<?php

/****************************************************************************************************
DBObject3 is an abscract class that can be extended for classes that map to a MySQL table.

Convenience functions for accessing data:
=========================================
- public static function updateDB():
    saves any changes made to the object into the database.
- public static function loadBy($fields, $values, $options)
    load a single record.
    Returns NULL if the record does not exist.
    For example: $dataset = Dataset::loadBy(Array("id"), Array($datasetid)); will load a single
        dataset or NULL.
- public static function loadArrayBy($fields, $values, $options)
    load multiple records.
    Returns an empty array if no records are found.
    For example $dataset = Dataset::loadArrayBy(Array("uid"), Array($uid), Array("limit" => 10));
        will load the first ten datasets found.
- public static function getCount($fields, $values, $options)
    Like loadArrayBy but returns the count instead of objects.

Requirements for implementing a DBObject3 class
===============================================

Required properteies
--------------------
Every DBObject3 class must have the following properties:
- protected static $_fields_definitions = NULL
    set to null, then set as an array in the init static method
- protected static $_table_name = "mysql_database_table_name"
- protected static $_primary_key_field = "id"
     this is almost always "id".

Required methods
----------------
- public static function init()
    set the fields definitions to map the properties from the mysql table to the object
    for example if a mysql table has the following columns:
        (
            id int(11),
            name varchar(32),
            description text,
            status varchar(32),
            timestamp int(11)
        )
    then it would have the following fields definitions array
        Array(
            "id"            => self::fieldDef("id", "i", true),
            "name"          => self::fieldDef("name", "s", true),
            "description"   => self::fieldDef("description", "s", false),
            "status"        => self::fieldDef("status", "s", false),
            "timestamp"     => self::fieldDef("timestamp", "i", true),
        )
    the init() function should be called after the class definition
- public static function createNewObj
    function to create the new object and save it to the database
    createNewObj should call the self::insertObj function,
        which will automatically handle inserting and creating a new object from the inserted row
    using the example from above, the create new object function would look like:
    public static function createNewObj($name, $description) {
        $timestamp = time();
        $status = "default-status";
        return self::insertObj(Array(
            "id" => NULL,   // the primary key should always be NULL
            "name" => $name,
            "description" => $description,
            "status" => $status,
            "timestamp" => $timestamp,
        ));
    }
- public static function deleteObj
    an object to delete the record from the database
    this function can do nothing, change a state flag or actually delete the object from the database
    if it is actually deleted, then the parent::deleteObj function can be called which will take care
        of the deleting
- arrayForm
    the object data in an associative array. usually used for returning in an api endpoint

getting and setting
===================
all properties in field definitions are accessible throught $obj->$property_name
if a special getter or setter is required, then they can be implemented with:
    protected function _get_$property_name($value)
    protected function _set_$property_name($value)
these functions act as filters between the database data and the data in memory
****************************************************************************************************/

abstract class DBObject3 {

    /*
    the following fields need to be implemented in every class:
    - protected static $_fields_defintions; // assoc array of DBObjec2Field
    - protected static $_table_name;
    - protected static $_primary_key_field; // primary key field should be the assoc array key that points to the real DBObject2Field
    */

    /**
     * @var array all the values as they are represented in the database
     */
    protected $_fields_values;
    protected $_construct_mode;
    protected $_updated_since_db;

    /**
     * constructor
     *
     * @param array vals the array of values for each field
     */
    protected function __construct($vals) {
        $class_name = get_class($this);
        foreach($class_name::$_fields_definitions as $key => $fd) {
            if(!array_key_exists($fd["dbname"] ,$vals)) throw new Exception("missing value");
            $this->_fields_values[$key] = $vals[$fd["dbname"]];
        }
        $this->_updated_since_db = false;
        $this->_construct_mode = false;
    }

    /**
     * get
     * get the value of a field
     *
     * @param string fieldname
     * @return mixed
     */
    public function __get($fieldname) {
        $class_name = get_class($this);
        if(!isset($class_name::$_fields_definitions[$fieldname]) || !array_key_exists($fieldname, $this->_fields_values)) {
            throw new Exception("unknown field");
        }
        $method_name = "_get_" . $fieldname;
        if(method_exists($this, $method_name)) {
            return $this->$method_name($this->_fields_values[$fieldname]);
        }
        return $this->_fields_values[$fieldname];
    }

    /**
     * set
     * set the value of a field
     *
     * @param string fieldname
     * @param mixed val
     */
    public function __set($fieldname, $val) {
        $class_name = get_class($this);
        if(!isset($class_name::$_fields_definitions[$fieldname])) {
            throw new Exception("unknown field");
        }
        if(!$this->_construct_mode && $class_name::$_fields_definitions[$fieldname]["readonly"]) {
            throw new Exception("field is read only");
        }

        $set_val = $val;
        $method_name = "_set_" . $fieldname;
        if(method_exists($this, $method_name)) {
            $set_val = $this->$method_name($val);
        } elseif(is_array($class_name::$_fields_definitions[$fieldname]["allowed_values"])) {
            if(!in_array($val, $class_name::$_fields_definitions[$fieldname]["allowed_values"])) {
                throw new Exception("value not in allowed values");
            }
        }

        if($set_val !== false) {
            /* check if object is updated since last db sync or construction */
            if(!$this->_construct_mode && !$this->_updated_since_db && $this->_fields_values[$fieldname] !== $set_val) {
                $this->_updated_since_db = true;
            }
            $this->_fields_values[$fieldname] = $set_val;
        }
    }

    /**
     * display
     * get the dispaly value for the field
     *
     * @param string fieldname
     * @return mixed
     */
    public function display($fieldname) {
        $class_name = get_class($this);
        if(!isset($class_name::$_fields_definitions[$fieldname]) || !array_key_exists($fieldname, $this->_fields_values)) {
            throw new Exception("unknown field");
        }
        $method_name = "_display_" . $fieldname;
        if(method_exists($this, $method_name)) {
            return $this->$method_name($this->$fieldname);
        }
        return $this->$fieldname;
    }

    /**
     * getRaw
     * get the raw database value
     *
     * @param string field name
     * @return mixed the raw value from the database
     */
    public function getRaw($fieldname) {
        $class_name = get_class($this);
        if(!isset($class_name::$_fields_definitions[$fieldname]) || !array_key_exists($fieldname, $this->_fields_values)) {
            throw new Exception("unknown field");
        }
        return $this->_fields_values[$fieldname];
    }

    /**
     * getAllRaw
     * get all the raw fields in an associative array
     * where the keys are the database names
     *
     * @return array
     */
    public function getAllRaw() {
        $class_name = get_class($this);

        $data = Array();
        foreach($class_name::$_fields_definitions as $field_name => $definition) {
            $data[$definition["dbname"]] = $this->getRaw($field_name);
        }
        return $data;
    }

    /**
     * updateDB
     * updates the database based on the values in the field
     */
    public function updateDB() {
        /* dont need to update database if an update hasnt been done */
        if(!$this->_updated_since_db) {
            return;
        }
        $class_name = get_class($this);
        $fields_values = Array();
        $fields_types = "";
        $fields_names = Array();
        foreach($class_name::$_fields_definitions as $key => $fd) {
            if($fd["readonly"]) continue;
            if(!array_key_exists($key, $this->_fields_values)) throw new Exception("field not set");
            $fields_values[] = $this->_fields_values[$key];
            $fields_types .= $fd["type"];
            $fields_names[] = $fd["dbname"];
        }
        if(empty($fields_names)) {  // nothing to update
            return;
        }
        $where = "where " . $class_name::$_fields_definitions[$class_name::$_primary_key_field]["dbname"] . "=?";
        $fields_values[] = $this->_fields_values[$class_name::$_primary_key_field];
        $fields_types .= $class_name::$_fields_definitions[$class_name::$_primary_key_field]["type"];

        $cxn = new Connection();
        $cxn->connect();
        $cxn->update($class_name::$_table_name, $fields_types, $fields_names, $fields_values, $where);
        $cxn->close();

        /* after sync, no longer updated */
        $this->_updated_since_db = false;
    }

    /**
     * arrayFormAll
     *
     * @return array an assoc array with all the fields_names => fields_values
     */
    public function arrayFormAll() {
        $class_name = get_class($this);
        $values = Array();
        foreach($class_name::$_fields_definitions as $key => $fd) {
            $values[$key] = $this->_fields_values[$key];
        }
        return $values;
    }

    private static function _loadBy($fields, $values, $limit, $offset, $options) {
        if(count($fields) !== count($values)) throw new Exception("fields and values must have same length");

        $class_name = get_called_class();
        $table_name = $class_name::$_table_name;

        /* get the types */
        $types = "";
        foreach($fields as $f) {
            if(!isset($class_name::$_fields_definitions[$f])) throw new Exception("invalid field: " . $class_name . ":" . $f);
            $types .= $class_name::$_fields_definitions[$f]["type"];
        }

        /* get the values (either fuzzy or normal) */
        $search_vals = Array();
        $where_array = Array();
        foreach($fields as $i => $f) {
            if(isset($options["fuzzy"]) && $options["fuzzy"] && $types[$i] === "s") {
                $where_array[] = $class_name::$_fields_definitions[$f]["dbname"] . " like ?";
            } else {
                $where_array[] = $class_name::$_fields_definitions[$f]["dbname"] . "=?";
            }
        }
        foreach($values as $i => $v) {
            if(isset($options["fuzzy"]) && $options["fuzzy"] && $types[$i] === "s") {
                $search_vals[] = "%" . $v . "%";
            } else {
                $search_vals[] = $v;
            }
        }

        /* build the where string */
        $where_empty = empty($where_array);
        $where_string = "";
        if(!$where_empty) $where_string = "where";
        if(isset($options["exact"]) && $options["exact"]) $where_string .= " BINARY";
        if(!$where_empty) {
            if(isset($options["or-all"]) && $options["or-all"]) {
                $logical_op = " or ";
            } else {
                $logical_op = " and ";
            }
            $where_string .= ' ' . implode($logical_op, $where_array);
        }
        if(isset($options["order-by"])) $where_string .= " " . DBObject3::orderByString($options["order-by"], $class_name);
        $where_string .= " limit ?,?";
        $types .= "ii";
        $search_vals[] = $offset;
        $search_vals[] = $limit;

        /* build select array */
        if($options["get-count"]) {
            $select_vals = Array("count(*)");
        } else {
            $select_vals = Array("*");
        }

        $cxn = new Connection();
        $cxn->connect();
        $results = $cxn->select($table_name, $select_vals, $types, $search_vals, $where_string);
        $cxn->close();

        return $results;
    }

    /**
     * loadBy
     * loads an object from the database, returning either null or the object
     *
     * @param array fields the names of the fields
     * @param array values the values corresponding to the fields, must be same length as fields
     * @param array options associative array of options for the load
     * @param bool exactly_one if the select query is not unique return null
     * @return mixed returns either null or an object
     */
    public static function loadBy($fields, $values, $options, $exactly_one=false) {
        $class_name = get_called_class();
        $vals = $class_name::_loadBy($fields, $values, 2, 0, $options);
        if(count($vals) == 0) return NULL;
        if(count($vals) > 1 && $exactly_one) return NULL;

        $new_obj = new $class_name($vals[0]);
        return $new_obj;
    }

    /**
     * loadArrayBy
     * loads an array of objects from the database
     *
     * @param array fields the names of the fields
     * @param array values the values corresponding to the fields, must be same length as fields
     * @param array options associative array of options for the load
     * @return array an array of objects with length >= 0
     */
    public static function loadArrayBy($fields, $values, $options) {
        $class_name = get_called_class();
        $offset = isset($options["offset"]) ? $options["offset"] : 0;
        $limit = isset($options["limit"]) ? $options["limit"] : MAXINT;
        $vals = $class_name::_loadBy($fields, $values, $limit, $offset, $options);
        $obj_vals = Array();
        if(count($vals) > 0) {
            foreach($vals as $val) {
                $obj_vals[] = new $class_name($val);
            }
        }
        return $obj_vals;
    }

    /**
     * getCount
     * DESCRIPTION
     *
     * @param array fields the names of the fields
     * @param array values the values corresponding to the fields, must be same length as fields
     * @param the number of results
     */
    public static function getCount($fields, $values, $options) {
        $class_name = get_called_class();
        if(!$options) $options = Array();
        $options["get-count"] = true;
        $vals = $class_name::_loadBy($fields, $values, 1, 0, $options);
        return $vals[0]["count(*)"];
    }

    /**
     * getFields
     *
     * @param name string the key name
     * @return array strings for the field names of a DBObject type or DBObjectField if $name is not null
     */
    public static function getFields($name = NULL) {
        $class_name = get_called_class();
        if($name) {
            return $class_name::$_fields_definitions[$name];
        } else {
            return array_keys($class_name::$_fields_definitions);
        }
    }

    protected static function insertObj($vals, $no_load = false) {
        $class_name = get_called_class();
        $init_vals = Array();
        /* make an array of empty vals */
        foreach($vals as $key => $val) {
            $init_vals[$key] = NULL;
        }
        $db_types = "";
        $temp_obj = new $class_name($init_vals);
        $temp_obj->_construct_mode = true;
        foreach($class_name::$_fields_definitions as $key => $fd) {
            if(!array_key_exists($key, $vals)) throw new Exception("missing field: " . $key);
            if($class_name::$_primary_key_field === $key) { // primary key field is always uninitialized
                $temp_obj->_fields_values[$key] = NULL;
            } else {
                $temp_obj->$key = $vals[$key];
            }
            $db_types .= $fd["type"];
        }

        $cxn = new Connection();
        $cxn->connect();
        $id = $cxn->insert($class_name::$_table_name, $db_types, array_values($temp_obj->_fields_values));   // array_values converts assoc array to numeric
        $cxn->close();

        if($no_load) {
            return $id ? true : false;
        }
        $obj = $class_name::loadBy(Array($class_name::$_primary_key_field), Array($id));
        return $obj;
    }

    protected static function deleteObj($obj) {
        $class_name = get_called_class();

        $primary_key_field_obj = NULL;
        $primary_key_field_name = NULL;
        foreach($class_name::$_fields_definitions as $key => $fd) {
            if($fd["dbname"] === $class_name::$_primary_key_field) {
                $primary_key_field_obj = $fd;
                $primary_key_field_name = $key;
            }
        }
        if(is_null($primary_key_field_obj)) {
            throw new Exception("primary key field mismatch");
        }

        $cxn = new Connection();
        $cxn->connect();
        $cxn->delete(
            $class_name::$_table_name,
            $primary_key_field_obj["type"],
            Array($obj->$primary_key_field_name),
            "where " . $class_name::$_primary_key_field . "=?"
        );
        $cxn->close();
    }

    private static function orderByString($order_by, $class_name) {
        $order_by_array = explode(" ", $order_by);
        $asc_desc = "";
        if(count($order_by_array) !== 2) throw new Exception("order by string improperly formatted");

        /* get asc or desc */
        $given_asc_desc = $order_by_array[1];
        if($given_asc_desc === "asc") {
            $asc_desc = "asc";
        } elseif($given_asc_desc === "desc") {
            $asc_desc = "desc";
        } else {
            throw new Exception("expected asc or desc");
        }

        /* get field */
        $given_field = $order_by_array[0];
        if(!isset($class_name::$_fields_definitions[$given_field])) throw new Exception("invalid field");
        $field = $class_name::$_fields_definitions[$given_field]["dbname"];

        return "order by " . $field . " " . $asc_desc;
    }

    protected static function fieldDef($dbname, $type, $read_only, $options=Array()) {
        if(!$dbname) throw new Exception("bad field name");
        if($type !== "i" && $type !== "s" && $type !== "f")
            throw new Exception("bad field type");

        $field_def = Array("dbname" => $dbname, "type" => $type);
        $field_def["readonly"] = $read_only ? true : false;
        if(isset($options["allowed_values"]) && is_array($options["allowed_values"])) {
            $field_def["allowed_values"] = $options["allowed_values"];
        }

        return $field_def;
    }

    /**
     * dbTable
     * return the database table name
     *
     * @return string
     */
    public function dbTable() {
        $class_name = get_class($this);
        return $class_name::$_table_name;
    }

    /**
     * primaryKey
     * return the primary key data
     *
     * @return mixed
     */
    public function primaryKey() {
        $class_name = get_class($this);
        foreach($class_name::$_fields_definitions as $key => $fd) {
            if($fd["dbname"] === $class_name::$_primary_key_field) {
                return $this->$key;
            }
        }
        return NULL;
    }

    /**
     * loadDBCopy
     * creates a copy of the object from the db record
     *
     * @return mixed
     */
    public function loadDBCopy() {
        $class_name = get_class($this);
        $pk = $this->primaryKey();
        $pk_field = $class_name::$_primary_key_field;
        return $class_name::loadBy(Array($pk_field), Array($pk));
    }

    /**
     * saveHistory
     * make a history record for the object
     *
     * @param string the action
     * @param User user or null that made the action
     * @return bool success
     */
    protected function saveHistory($action, User $user = NULL) {
        /* if only updating and not object has not updated, skip */
        if($action == "update" && !$this->_updated_since_db) {
            return true;
        }
        $obj_copy = $this->loadDBCopy();
        if(is_null($obj_copy)) {
            return false;
        }

        /* TODO: get rid of this and pass in user to calling methods */
        if(is_null($user)) {
            $user = $_SESSION["user"];
        }
        return HistoryRecord::createFromDBO3($obj_copy, $action, $user);
    }

    /**
     * runTests
     * run tests on a dbobject class. should be overwritten by child class and call parent in first line
     *
     * @return DBOBject3TestResult
     */
    public static function runTests() {
        $class_name = get_called_class();
        $cxn = new Connection();
        $cxn->connect();
        $table_fields = $cxn->show($class_name::$_table_name);
        $cxn->close();

        if(count($table_fields) != count($class_name::$_fields_definitions)) {
            return DBObject3TestResult::failed($class_name, "table and class field length mismatch");
        }

        /* test that every column in the table is in the dbobject */
        foreach($table_fields as $tf) {
            $found = false;
            foreach($class_name::$_fields_definitions as $key => $fd) {
                if($fd["dbname"] == $tf["Field"]) {
                    $found = true;
                    break;
                }
            }
            if(!$found) {
                return DBObject3TestResult::failed($class_name, "mysql field " . $tf["Field"] . " in definition missing");
            }
        }

        return DBObject3TestResult::success($class_name);
    }

    /**************************************************************/
    /*               REUSABLE GETTERS AND SETTERS                 */
    /**************************************************************/
    public static function getBool($val) {
        if($val === 0) return false;
        return true;
    }

    public static function setBool($val) {
        if(!$val) return 0;
        return 1;
    }

    public static function setNotEmpty($val) {
        if(!!$val) return $val;
        return false;
    }

    public static function displayTime($val) {
        return date("r", $val);
    }

    public static function getJSON($val) {
        return json_decode($val, true);
    }

    public static function setJSON($val) {
        return json_encode($val, JSON_UNESCAPED_SLASHES);
    }
}

class DBObject3TestResult {
    public $success;
    public $class_name;
    public $message;

    private function __construct($success, $class_name, $message) {
        $this->success = $success;
        $this->class_name = $class_name;
        $this->message = $message;
    }

    public static function failed($class_name, $message) {
        return new self(false, $class_name, $message);
    }

    public static function success($class_name) {
        return new self(true, $class_name, "");
    }

    public function fullMessage() {
        return $this->class_name . ": " . $this->message;
    }
}

?>
