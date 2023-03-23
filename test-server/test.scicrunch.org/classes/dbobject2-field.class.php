<?php

class DBObject2Field {
    private $_dbname;
    private $_type;
    private $_read_only;
    private $_allowed_values;
    private $_getter;
    private $_setter;
    private $_displayer;

    /**
     * Constructor
     *
     * @param string dbname the name of the field in the database
     * @param string type float(f), int(i) or string(s)
     * @param bool read_only
     * @param array options additional options for the field
     */
    public function __construct($dbname, $type, $read_only, $options=Array()) {
        if(!$dbname) throw new Exception("bad field name");
        if($type !== "i" && $type !== "s" && $type !== "f") throw new Exception("bad field type");
        $this->_dbname = $dbname;
        $this->_type = $type;
        $this->_read_only = $read_only ? true : false;

        if(isset($options["allowed_values"]) && is_array($options["allowed_values"])) {
            $this->_allowed_values = $options["allowed_values"];
        }
        if(isset($options["getter"]) && is_callable($options["getter"])) {
            $this->_getter = $options["getter"];
        }
        if(isset($options["setter"]) && is_callable($options["setter"])) {
            $this->_setter = $options["setter"];
        }
        if(isset($options["displayer"]) && is_callable($options["displayer"])) {
            $this->_displayer = $options["displayer"];
        }
    }

    /**
     * get
     * getter filter for the field
     *
     * @param mixed val the value from the database
     * @param mixed calling_obj the object that is owns this data
     * @return mixed the value from the database after filter
     */
    public function get($val, $calling_obj) {
        if(!is_callable($this->_getter)) return $val;
        return call_user_func($this->_getter, $val, $calling_obj);
    }

    /**
     * set
     * run the setter filter function before saving to a database
     *
     * @param mixed val the value that will be filtered before going into the database
     * @param mixed calling_obj the object that is owns this data
     * @param array initial_data an array of the initial data, only not null when object is initially created
     * @param bool override_ro if the value is read only, should that be ignored (only should be true when first creating object)
     * @return mixed the filtered value
     */
    public function set($val, $calling_obj, $initial_data, $override_ro = false) {
        if($this->readOnly() && !$override_ro) {
            throw new Exception("field is read only");
        }
        if(is_callable($this->_setter)) {
            return call_user_func($this->_setter, $val, $calling_obj, $initial_data);
        }
        if(is_array($this->allowedValues()) && !in_array($val, $this->allowedValues())) {
            throw new Exception("value not in allowed values");
        }
        return $val;
    }

    /**
     * display
     * display the data for the data dashboard
     *
     * @param mixed val the value from the database
     * @param mixed calling_obj the object that is owns this data
     * @return mixed the data in a format that will be displayed on the dashboard
     */
    public function display($val, $calling_obj) {
        if(!is_callable($this->_displayer)) return $val;
        return call_user_func($this->_displayer, $val, $calling_obj);
    }

    /**
     * dbname
     *
     * @return string the name of the field's column in the database
     */
    public function dbname() {
        return $this->_dbname;
    }

    /**
     * type
     *
     * @return string the type float(f), string(s), integer(i)
     */
    public function type() {
        return $this->_type;
    }

    /**
     * readOnly
     *
     * @return bool
     */
    public function readOnly() {
        return $this->_read_only;
    }

    /**
     * allowedValues
     *
     * @return array an array of the values that are allowed
     */
    public function allowedValues() {
        return $this->_allowed_values;
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
}

?>
