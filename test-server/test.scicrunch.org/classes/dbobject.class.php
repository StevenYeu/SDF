<?php

abstract class DBObject extends Connection {
    // class property names must match mysql table field names

    // required properties and methods:
    // static public function createNewObj(){}
    // static public function deleteObj(){}
    // static protected $_table
    // static protected $_table_fields (these must correspond to all table fields)
    // static protected $_primary_key_field
    // static protected $_table_types

    /**
     * @param array $vals an array with all the members in class::_table_fields
     * @param array $opt_vals an optional array that can be passed if the child constructor needs it
     */
    public function __construct($vals, $opt_vals=NULL){
        $class_name = get_class($this);
        $properties = $class_name::$_table_fields;
        if(DBObject::isAssoc($properties)){
            foreach($properties as $field => $prop) $this->__set($prop, $vals[$field]);
        }
        else{
            foreach($properties as $prop) $this->__set($prop, $vals[$prop]);
        }
    }

    public function updateDB(){
        $class_name = get_class($this);
        $fields_values = Array();
        foreach($class_name::$_table_fields as $tf){
            $fields_values[] = $this->__get($tf);
        }
        $primary_key = $this->__get($class_name::$_primary_key_field);
        $fields_values[] = $primary_key;
        $table_types = $class_name::$_table_types;
        $table_types .= DBObject::mysqlType($primary_key);
        $where = "where %s=?";
        $db_table_fields = DBObject::isAssoc($class_name::$_table_fields) ? array_keys($class_name::$_table_fields) : array_values($class_name::$_table_fields);

        $this->connect();
        $this->update($class_name::$_table, $table_types, $db_table_fields, $fields_values, sprintf($where, $class_name::$_primary_key_field)); 
        $this->close();
    }

    /**
     * this function should not be called directly
     * @param string $field the name of the field in the table and the 
     * @param mixed $value the value to search on
     * @param bool $fuzzy do fuzzy searching
     */
    static private function _loadBy($field, $value, $fuzzy, $limit, $exact, $offset, $order_by){
        $class_name = get_called_class();
        $table_name = $class_name::$_table;

        $types = "";
        foreach($value as $val) $types .= DBObject::mysqlType($val);

        $search_vals = Array();
        $where_array = Array();
        if($fuzzy){
            foreach($value as $val) $search_vals[] = "%".$val."%";
            foreach($field as $f) $where_array[] = $f." like ?";
        }else{
            $search_vals = $value;
            foreach($field as $f) $where_array[] = $f."=?";
        }

        $where_empty = empty($where_array);
        $where_string = "";
        if(!$where_empty) $where_string .= "where";
        if($exact) $where_string .= " BINARY";
        if(!$where_empty) $where_string .= ' ' . implode(" and ", $where_array);
        if(!is_null($order_by)){
            $where_string .= DBObject::orderByString($order_by, $class_name);
        }
        $where_string .= " limit ?, ?";
        $types .= "ii";
        $search_vals[] = $offset;
        $search_vals[] = $limit;

        $cxn = new Connection();
        $cxn->connect();
        $vals = $cxn->select($table_name, Array("*"), $types, $search_vals, $where_string);
        $cxn->close();

        return $vals;
    }

    /**
     * function for loading a single entry from the database
     * @param string $field the string to search on
     * @param mixed $value the value to search on
     * @param bool $exact if true, the null will be returned unless there is exactly one db entry
     * @param bool $fuzzy do fuzzy searching
     * @param bool $exact_match string must match exactly
     * @return mixed|null returns either an instance of the object or null
     */
    static public function loadBy($field, $value, $exact=false, $fuzzy=false, $exact_match=false){
        $class_name = get_called_class();
        $vals = $class_name::_loadBy($field, $value, $fuzzy, 2, $exact_match, 0, NULL);    // load 2 to check if extras exist

        if(count($vals) == 0) return NULL;
        if(count($vals) > 1 && $exact) return NULL;

        $new_obj = new $class_name($vals[0]);
        return $new_obj;
    }

    /**
     * function for getting array from database
     * @param string $field the string to search on
     * @param mixed $value the value to search on
     * @param bool $fuzzy do fuzzy searching
     * @param int $limit the max number of results
     * @param bool $exact_match string must match exactly
     * @return array of zero or more object instances
     */
    static public function loadArrayBy($field, $value, $fuzzy=false, $limit = MAXINT, $exact_match=false, $offset = 0, $order_by = NULL){
        $class_name = get_called_class();
        $vals = $class_name::_loadBy($field, $value, $fuzzy, $limit, $exact_match, $offset, $order_by);
        $obj_vals = Array();
        if(count($vals) > 0){
            foreach($vals as $val){
                $obj_vals[] = new $class_name($val);
            }
        }
        return $obj_vals;
    }

    static protected function insertObj(&$obj){
        $class_name = get_called_class();
        $obj_vals = Array();
        if(DBObject::isAssoc($class_name::$_table_fields)){
            foreach($class_name::$_table_fields as $prop => $field){
                $verify_method = "_verifydb_".$field;
                if(method_exists($obj, $verify_method) && !$obj->$verify_method()){
                    $obj = NULL;
                    return $obj;
                }
                $obj_vals[] = $obj->$field;
            }
        }else{
            foreach($class_name::$_table_fields as $tf){
                $verify_method = "_verifydb_".$tf;
                if(method_exists($obj, $verify_method) && !$obj->$verify_method()){
                    $obj = NULL;
                    return $obj;
                }
                $obj_vals[] = $obj->$tf;
            }
        }

        $cxn = new Connection();
        $cxn->connect();
        $id = $cxn->insert($class_name::$_table, $class_name::$_table_types, $obj_vals);
        $cxn->close();

        $obj = $class_name::loadBy(Array($class_name::$_primary_key_field), Array($id));
        return $obj;
    }

    public function __get($prop){
        $get_fun = "_get_".$prop;
        if(method_exists($this, $get_fun)){
            return $this->$get_fun();
        }
        throw new \OutofRangeException("Get method " . $get_fun . " does not exist for class " . get_class($this));
    }

    public function __set($prop, $value){
        $set_fun = "_set_".$prop;
        if(method_exists($this, $set_fun)){
            return $this->$set_fun($value);
        }
        throw new \OutofRangeException("Set method " . $set_fun . " does not exist for class " . get_class($this));
    }

    static public function mysqlType($val){
        return gettype($val) == "string" ? "s" : "i";
    }

    static public function isAssoc($array){
        $counter = 0;
        if(count($array) === 0) return false;
        foreach($array as $key => $val){
            if($counter !== $key) return true;
            $counter += 1;
        }
        return false;
    }

    static private function orderByString($order_by, $class_name){
        $order_by_array = explode(" ", $order_by);
        $asc_desc = "";
        if(count($order_by_array) > 1){
            $given_asc_desc = $order_by_array[1];
            if($given_asc_desc === "asc") $asc_desc = " asc";
            elseif($given_asc_desc === "desc") $asc_desc = " desc";
            else throw new Exception("expected asc or desc");
        }
        $given_field = $order_by_array[0];
        $field = NULL;
        foreach($class_name::$_table_fields as $tf){
            if($tf === $given_field){
                $field = $tf;
            }
        }
        if(is_null($field)) throw new Exception("invalid field");

        return " order by " . $field . $asc_desc;
    }

    static public function totalCount(){
        $class_name = get_called_class();
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select($class_name::$_table, Array("count(*)"), "", Array(), "");
        $cxn->close();
        return $count[0]["count(*)"];
    }
}

?>
