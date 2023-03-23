<?php

abstract class DBObject2 {

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

    /**
     * constructor
     *
     * @param array vals the array of values for each field
     */
    protected function __construct($vals) {
        $class_name = get_class($this);
        foreach($class_name::$_fields_definitions as $key => $fd) {
            if(!array_key_exists($fd->dbname() ,$vals)) throw new Exception("missing value");
            $this->_fields_values[$key] = $vals[$fd->dbname()];
        }
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
        return $class_name::$_fields_definitions[$fieldname]->get($this->_fields_values[$fieldname], $this);
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
        $set_val = $class_name::$_fields_definitions[$fieldname]->set($val, $this, NULL, false);
        if($set_val !== false) {
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
        return $class_name::$_fields_definitions[$fieldname]->display($this->_fields_values[$fieldname], $this);
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
     * updateDB
     * updates the database based on the values in the field
     */
    public function updateDB() {
        $class_name = get_class($this);
        $fields_values = Array();
        $fields_types = "";
        $fields_names = Array();
        foreach($class_name::$_fields_definitions as $key => $fd) {
            if($fd->readOnly()) continue;
            if(!array_key_exists($key, $this->_fields_values)) throw new Exception("field not set");
            $fields_values[] = $this->_fields_values[$key];
            $fields_types .= $fd->type();
            $fields_names[] = $fd->dbname();
        }
        $where = "where " . $class_name::$_fields_definitions[$class_name::$_primary_key_field]->dbname() . "=?";
        $fields_values[] = $this->_fields_values[$class_name::$_primary_key_field];
        $fields_types .= $class_name::$_fields_definitions[$class_name::$_primary_key_field]->type();

        $cxn = new Connection();
        $cxn->connect();
        $cxn->update($class_name::$_table_name, $fields_types, $fields_names, $fields_values, $where);
        $cxn->close();
    }

    static private function _loadBy($fields, $values, $limit, $offset, $options) {
        if(count($fields) !== count($values)) throw new Exception("fields and values must have same length");

        $class_name = get_called_class();
        $table_name = $class_name::$_table_name;

        /* get the types */
        $types = "";
        foreach($fields as $f) {
            if(!isset($class_name::$_fields_definitions[$f])) throw new Exception("invalid field");
            $types .= $class_name::$_fields_definitions[$f]->type();
        }

        /* get the values (either fuzzy or normal) */
        $search_vals = Array();
        $where_array = Array();
        foreach($fields as $i => $f) {
            if(isset($options["fuzzy"]) && $options["fuzzy"] && $types[$i] === "s") {
                $where_array[] = $class_name::$_fields_definitions[$f]->dbname() . " like ?";
            } else {
                $where_array[] = $class_name::$_fields_definitions[$f]->dbname() . "=?";
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
        if(isset($options["order-by"])) $where_string .= " " . DBObject2::orderByString($options["order-by"], $class_name);
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
    static public function loadBy($fields, $values, $options, $exactly_one=false) {
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
    static public function loadArrayBy($fields, $values, $options) {
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
    static public function getCount($fields, $values, $options) {
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

    protected static function insertObj($vals) {
        $class_name = get_called_class();
        $db_vals = Array();
        $initial_vals = Array();
        $db_types = "";
        foreach($class_name::$_fields_definitions as $key => $fd) {
            if(!array_key_exists($key, $vals)) throw new Exception("missing field: " . $key);
            if($class_name::$_primary_key_field === $key) { // primary key field is always uninitialized
                $db_vals[$key] = NULL;
            } else {
                $set_val = $fd->set($vals[$key], NULL, $db_vals, true);
                if($set_val === false) {
                    $db_vals[$key] = NULL;
                } else {
                    $db_vals[$key] = $set_val;
                }
            }
            $db_types .= $fd->type();
        }

        $cxn = new Connection();
        $cxn->connect();
        $id = $cxn->insert($class_name::$_table_name, $db_types, array_values($db_vals));   // array_values converts assoc array to numeric
        $cxn->close();

        $obj = $class_name::loadBy(Array($class_name::$_primary_key_field), Array($id));
        return $obj;
    }

    static protected function deleteObj($obj) {
        $class_name = get_called_class();

        $primary_key_field_obj = NULL;
        $primary_key_field_name = NULL;
        foreach($class_name::$_fields_definitions as $key => $fd) {
            if($fd->dbname() === $class_name::$_primary_key_field) {
                $primary_key_field_obj = $fd;
                $primary_key_field_name = $key;
                break;
            }
        }
        if(is_null($primary_key_field_obj)) {
            throw new Exception("primary key field mismatch");
        }

        $cxn = new Connection();
        $cxn->connect();
        $cxn->delete(
            $class_name::$_table_name,
            $primary_key_field_obj->type(),
            Array($obj->$primary_key_field_name),
            "where " . $class_name::$_primary_key_field . "=?"
        );
        $cxn->close();
    }

    static private function orderByString($order_by, $class_name) {
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
        $field = $class_name::$_fields_definitions[$given_field]->dbname();

        return "order by " . $field . " " . $asc_desc;
    }
}

?>
