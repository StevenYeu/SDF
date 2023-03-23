<?php

class DatasetField extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "dataset_fields";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                            => self::fieldDef("id", "i", true),
            "dataset_fields_template_id"    => self::fieldDef("dataset_fields_template_id", "i", true),
            "name"                          => self::fieldDef("name", "s", false),
            "termid"                        => self::fieldDef("termid", "i", false),
            "required"                      => self::fieldDef("required", "i", false, Array("allowed_values" => Array(0,1))),
            "position"                      => self::fieldDef("position", "i", false),
            "queryable"                     => self::fieldDef("queryable", "i", false, Array("allowed_values" => Array(0, 1))),
            "multi"                         => self::fieldDef("multi", "i", true),
            "multi_suffixes"                => self::fieldDef("multi_suffixes", "s", true),
        );
    }
    protected function _set_name($val) {
        if(!$val) return false;
        if(!$this->checkUniqueName($val)) return false;
        if(strtolower($val) == "notes") return false;
        if(\helper\startsWith($val, "_")) return false;
        return $val;
    }
    protected function _get_multi($val) { return self::getBool($val); }
    protected function _set_multi($val) { return self::setBool($val); }
    protected function _get_multi_suffixes($val) {
        /* returns an array of the suffixes */
        return explode(",", $val);
    }
    protected function _set_multi_suffixes($val) {
        /* if not multi, then just store empty string */
        if(!$this->multi) return "";
        /* if multi but invalid return false */
        if(!DatasetField::validateMultiSuffixes($val)) return false;
        /* store as comma separated list of values */
        return implode(",", $val);
    }

    private $_term;
    private $_template;
    private $_annotations;
    private $_mongo_encoded_name;
    private $_is_subject;

    public static function createNewObj(DatasetFieldTemplate $dataset_fields_template, $name, $ilxid, $required, $queryable, $multi, $multi_suffixes = NULL) {
        if(!$dataset_fields_template->id) return NULL;
        /*
        if($dataset_fields_template->inUse()) {
            return NULL;
        }
        */
        $position = count(self::loadArrayBy(Array("dataset_fields_template_id"), Array($dataset_fields_template->id)));  // set the position to the length of the dataset fields

        $term = TermDBO::loadBy(Array("ilx"), Array($ilxid));
        if(is_null($term) || $term->type !== "cde") return NULL;

        $obj = self::insertObj(Array(
            "id" => NULL,
            "dataset_fields_template_id" => $dataset_fields_template->id,
            "name" => $name,
            "termid" => $term->id,
            "required" => (int) $required,
            "position" => $position,
            "queryable" => (int) $queryable,
            "multi" => $multi,
            "multi_suffixes" => $multi_suffixes,
        ));

        if($obj) {
            $dataset_fields_template->unsubmit();
        }

        return $obj;
    }

    public static function deleteObj($obj, User $user = NULL) {
        /* notify dataset of deletion */
        $dataset_fields_template = DatasetFieldTemplate::loadBy(Array("id"), Array($obj->dataset_fields_template_id));
/*
        if($dataset_fields_template->inUse()) {
            return NULL;
        }
*/
        $field = null;
        foreach($dataset_fields_template->fields() as $dft_field) {
            if($dft_field->name == $obj->name) {
                $field = $dft_field;
                break;
            }
        }

        if(!is_null($field)) {
            /* delete annotations */
            $annotations = $field->annotations();
            foreach($annotations as $a) {
                DatasetFieldAnnotation::deleteObj($a);
            }

            /* delete from this dataset */
            $field->saveHistory("delete", $user);
            parent::deleteObj($field);
        }

        /* move the other fields up */
        self::resetDatasetPositions($dataset_fields_template);

        /* unsubmit dataset template */
        $dataset_fields_template->unsubmit();
    }

    public function updateDB(User $user = NULL) {
        $this->saveHistory("update", $user);
        parent::updateDB();
    }

    public function LoadByTrimmed($fields, $values) {
        $cxn = new Connection();
        $cxn->connect();
        $rows = $cxn->select("dataset_fields", Array("id", "name"), "is", Array($values[0], $values[1]), "where dataset_fields_template_id=? and trim(name)=?");
        $cxn->close();

        if ($rows)
            return $rows[0]['name'];
        else
            return false;
    }

    public function arrayForm() {
        $term = $this->term();
        if(is_null($term)) {
            $term_array = Array();
        } else {
            $term_array = $term->arrayForm();
        }

        $annotations_array = Array();
        $annotations = DatasetFieldAnnotation::loadArrayBy(Array("dataset_field_id"), Array($this->id));
        foreach($annotations as $a) {
            $annotations_array[] = $a->arrayForm();
        }

        return Array(
            "name" => $this->name,
            "termid" => $term_array,
            "required" => $this->required,
            "position" => $this->position,
            "queryable" => $this->queryable,
            "multi" => $this->multi,
            "multi_suffixes" => $this->multi_suffixes,
            "annotations" => $annotations_array,
        );
    }

    public function term() {
        if(is_null($this->_term)) {
            $this->_term = TermDBO::loadBy(Array("id"), Array($this->termid));
        }
        return $this->_term;
    }

    public function checkUniqueName($name) {
        $cxn = new Connection();
        $cxn->connect();
        $fields = $cxn->select(self::$_table_name, Array("*"), "is", Array($this->dataset_fields_template_id, $name), "where dataset_fields_template_id=? and name=?");
        $cxn->close();

        if(count($fields) > 0) {
            if(count($fields) == 1 && $fields[0]["id"] == $this->id) return true;
            return false;
        }
        return true;
    }

    public static function checkUniqueNameStatic($name, DatasetFieldTemplate $dataset_field_template) {
        $count = self::getCount(Array("dataset_fields_template_id", "name"), Array($dataset_field_template->id, $name));
        if($count > 0) return false;
        return true;
    }

    public function sanitizeFieldValue($value) {
        return (string) filter_var($value, FILTER_SANITIZE_STRING);
    }

    public function isIndexable() {
        return true;
    }

    public function movePositionUp() {
        if($this->template()->inUse()) return;
        /* check if already at lowest */
        if($this->position == 0) return;

        /* get the expected new position */
        $new_pos = $this->position - 1;

        /* get all the fields */
        $all_fields = self::loadArrayBy(Array("dataset_fields_template_id"), Array($this->dataset_fields_template_id));

        /* find the field to switch positions with and update it */
        foreach($all_fields as $field) {
            if($field->position == $new_pos) {
                $field->position += 1;
                $field->updateDB();
                break;
            }
        }

        /* change this fields position and update */
        $this->position = $new_pos;
        $this->updateDB();
    }

    public function movePositionDown() {
        if($this->template()->inUse()) return;
        /* get all the fields */
        $all_fields = self::loadArrayBy(Array("dataset_fields_template_id"), Array($this->dataset_fields_template_id));

        /* return if already the last field */
        if($this->position == count($all_fields) - 1) return;

        /* get the expected next postion */
        $new_pos = $this->position + 1;

        /* find and update the field with the same position */
        foreach($all_fields as $field) {
            if($field->position == $new_pos) {
                $field->position -= 1;
                $field->updateDB();
                break;
            }
        }

        /* update this field's position */
        $this->position = $new_pos;
        $this->updateDB();
    }

    public function resetDatasetPositions($dataset_fields_template) {
        /* load the dataset fields */
        $dataset_fields = self::loadArrayBy(Array("dataset_fields_template_id"), Array($dataset_fields_template->id));

        /* sort the dataset */
        usort($dataset_fields, function($a, $b) {
            if($a->position < $b->position) return -1;
            if($a->position > $b->position) return 1;
            return 0;
        });

        /* reset the dataset positions */
        foreach($dataset_fields as $i => $df) {
            if($df->position != $i) {
                $df->position = $i;
                $df->updateDB();
            }
        }
    }

    public function validateValue($value) {
        $return_value = Array("success" => true, "message" => null);

        $term = $this->term();
        $term_annotations = $term->annotations();

        /* find required values */
        $required_values = Array();
        $required_range = NULL;
        foreach($term_annotations as $ta) {
            $annotation_id = $ta["annotation_tid"];
            if($annotation_id == self::annotationTIDRestriction()) {
                $required_values[$ta["value"]] = true;
            } elseif($annotation_id == self::annotationTIDRange()) {
                $required_range = array_map(function($x) { return (float) trim($x); }, explode(",", str_replace(Array("[", "]"), "",$ta["value"])));
                if(count($required_range) != 3) $required_range = NULL;
            }
        }

        /* test if in required values */
        if(!empty($required_values)) {
            if(!isset($required_values[$value])) {
                $return_value["success"] = false;
                $return_value["message"] = "'" . $value . "' is an invalid value";
                return $return_value;
            }
        }

        /* test if in required range */
        if(!is_null($required_range)) {
            $fvalue = (float) $value;
            if(!is_numeric($value)) {
                $return_value["success"] = false;
                $return_value["message"] = "'" . $value . "' is not a number";
                return $return_value;
            }
            if($fvalue < $required_range[0] || $fvalue > $required_range[1]) {
                $return_value["success"] = false;
                $return_value["message"] = "'" . $value . "' is out of the expected range";
                return $return_value;
            }
            $lowerDiff = $fvalue - $required_range[0];
            $diffDivide = $lowerDiff / $required_range[2];
            if(!\helper\floatCompare(fmod($diffDivide, 1.0), 0.0)) {
                $return_value["success"] = false;
                $return_value["message"] = "'" . $value . "' is out of the expected range";
                return $return_value;
            }
        }

        /* test if subject and has a value */
        if($this->isSubject() && !$value) {
            $return_value["success"] = false;
            $return_value["message"] = "Subject must have a value";
            return $return_value;
        }

        return $return_value;
    }

    public static function validateMultiSuffixes($vals) {
        if(!is_array($vals) || empty($vals)) return false;
        $used_vals = Array();
        foreach($vals as $val) {
            if(!is_string($val) || strpos($val, ",") !== false || strlen($val) == 0 || isset($used_vals[$val])) return false;
            $used_vals[$val] = true;
        }
        return true;
    }

    public static function getByAnnotation(DatasetFieldTemplate $template, $annotation) {
        $cxn = new Connection();
        $cxn->connect();
        $rows = $cxn->select("dataset_fields d inner join dataset_field_annotations a on d.id = a.dataset_field_id", Array("d.*"), "is", Array($template->id, $annotation), "where d.dataset_fields_template_id=? and a.name=?");
        $cxn->close();
        $fields = Array();
        foreach($rows as $row) {
            $fields[] = new DatasetField($row);
        }
        return $fields;
    }

    public function template() {
        if(is_null($this->_template)) {
            $this->_template = DatasetFieldTemplate::loadBy(Array("id"), Array($this->dataset_fields_template_id));
        }
        return $this->_template;
    }

    public function nameMongoEncode() {
        if(is_null($this->_mongo_encoded_name)) {
            $this->_mongo_encoded_name = self::nameMongoEncodeStatic($this->name);
        }
        return $this->_mongo_encoded_name;
    }

    public static function nameMongoEncodeStatic($name) {
        $new_name = str_replace(".", "%2E", $name);
        $new_name = str_replace("$", "%24", $new_name);
        return $new_name;
    }

    public static function nameMongoDecodeStatic($name) {
        $new_name = str_replace("%2E", ".", $name);
        $new_name = str_replace("%24", "$", $new_name);
        return $new_name;
    }

    public function annotations() {
        if(is_null($this->_annotations)) {
            $this->_annotations = DatasetFieldAnnotation::loadArrayBy(Array("dataset_field_id"), Array($this->id));
        }
        return $this->_annotations;
    }

    public function isSubject() {
        if($this->_is_subject === null) {
            $this->_is_subject = false;
            foreach($this->annotations() as $a) {
                if($a->name == "subject") {
                    $this->_is_subject = true;
                    break;
                }
            }
        }
        return $this->_is_subject;
    }

    public static function annotationTIDRestriction() {
        return $GLOBALS["config"]["dataset-config"]["term"]["ilx"]["annotation-value-restriction-id"];
    }

    public static function annotationTIDRange() {
        return $GLOBALS["config"]["dataset-config"]["term"]["ilx"]["annotation-value-range-id"];
    }

    // $id = dataset_fields_†emplate_id
    public static function movePositionOneQuery($id, $desired, $current) {
        $move = $desired > $current ? 'down' : 'up';
              
        $cxn = new Connection();
        $cxn->connect();

        // temporarily set position = -1 for the item being moved
        $cxn->update('dataset_fields', 'iii', Array('position'), Array(-1, $id, $current), 'where dataset_fields_template_id=? AND position=?');

        // single query to move stack up/down
        if ($move == 'down') {
            $cxn->updateNonDiscreteValue("update dataset_fields set position = position - 1 where dataset_fields_template_id=? AND position > ? AND position <= ?", 'iii', Array($id, $current, $desired));
        } else {
            $cxn->updateNonDiscreteValue("update dataset_fields set position = position + 1 where dataset_fields_template_id=? AND position >= ? AND position < ?", 'iii', Array( $id, $desired, $current));
        }

        // set final position for item being moved
        $cxn->update('dataset_fields', 'iii', Array('position'), Array($desired, $id, -1), 'where dataset_fields_template_id=? AND position=?');

        $cxn->close();

        return APIReturnData::build(NULL, true);
    }
}
DatasetField::init();

?>
