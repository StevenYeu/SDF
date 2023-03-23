<?php

class DatasetFieldTemplate extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "dataset_fields_templates";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"        => self::fieldDef("id", "i", true),
            "uid"       => self::fieldDef("uid", "i", true),
            "timestamp" => self::fieldDef("timestamp", "i", true),
            "labid"     => self::fieldDef("labid", "i", true),
            "name"      => self::fieldDef("name", "s", false),
            "active"    => self::fieldDef("active", "i", false),
            "parent_id" => self::fieldDef("parent_id", "i", false),
            "submitted" => self::fieldDef("submitted", "i", false),
        );
    }
    public function _get_submitted($val) {
        return self::getBool($val);
    }
    public function _set_submitted($val) {
        if($val) {
            if(empty($this->fields()) || !$this->hasSubjectField()) {
                return false;
            }
        }
        return self::setBool($val);
    }

    private $_fields;
    private $_lab;
    private $_datasets;

    public static function createNewObj(User $user, Lab $lab, $name, DatasetFieldTemplate $parent = NULL) {
        if(!$user->id || !$lab->id || !$name) return NULL;

        $timestamp = time();
        $active_flag = 1;
        $submitted = false;

        if(is_null($parent)) {
            $parent_id = 0;
        } else {
            $parent_id = $parent->id;
        }

        $obj = self::insertObj(Array(
            "id" => NULL,
            "uid" => $user->id,
            "timestamp" => $timestamp,
            "labid" => $lab->id,
            "name" => $name,
            "active" => $active_flag,
            "parent_id" => $parent_id,
            "submitted" => $submitted,
        ));

        return $obj;
    }

    public static function deleteObj($obj, User $user = NULL) {
        if($obj->inUse()) return;
        $obj->active = 0;
        $obj->updateDB();
    }

    public function updateDB(User $user = NULL) {
        $this->saveHistory("update", $user);
        parent::updateDB();
    }

    public function arrayForm($no_fields = false) {
        $fields = $this->fields();
        if(!$no_fields) {
            $fields_array_form = Array();
            foreach($fields as $field) {
                $fields_array_form[] = $field->arrayForm();
            }
        }

        $datasets = $this->datasets();
        $datasets_array = Array();
        foreach($datasets as $d) {
            $datasets_array[] = Array(
                "id" => $d->id,
                "name" => $d->name,
            );
        }

        return Array(
            "id" => $this->id,
            "name" => $this->name,
            "fields" => $fields_array_form,
            "fields_count" => count($fields),
            "submitted" => $this->submitted,
            "in_use" => !empty($datasets_array),
            "dataset_names" => $datasets_array,
        );
    }

    public function inUse() {
        $check = Dataset::loadBy(Array("dataset_fields_template_id"), Array($this->id));
        if(!is_null($check)) return true;
        return false;
    }

    public function copyTemplate() {
        $user = new User();
        $user->getByID($this->uid);

        $obj = self::createNewObj($user, $this->lab(), $this->name, $this);
        $fields = $this->fields();
        usort($fields, function($a, $b) { 
            if($a->position < $b->position) return -1;
            if($a->position > $b->position) return 1;
            return 0;
        });
        foreach($fields as $field) {
            $term = $field->term();
            $new_field = DatasetField::createNewObj($obj, $field->name, $term->ilx, $field->required, $field->queryable);
            $annotations = $field->annotations();
            foreach($annotations as $a) {
                DatasetFieldAnnotation::upsert($new_field, $a->name, $a->value);
            }
        }

        return $obj;
    }

    public function fields() {
        if(is_null($this->_fields)) {
            $this->_fields = DatasetField::loadArrayBy(Array("dataset_fields_template_id"), Array($this->id));
        }
        return $this->_fields;
    }

    public function lab() {
        if(is_null($this->_lab)) {
            $this->_lab = Lab::loadBy(Array("id"), Array($this->labid));
        }
        return $this->_lab;
    }

    public function hasAllFields(DatasetFieldTemplate $other_template) {
        $this_ilx = Array();
        foreach($this->fields() as $field) {
            $ilx = $field->term()->ilx;
            if(!isset($this_ilx[$ilx])) {
                $this_ilx[$ilx] = 1;
            } else {
                $this_ilx[$ilx] += 1;
            }
        }

        $other_ilx = Array();
        foreach($other_template->fields() as $field) {
            $ilx = $field->term()->ilx;
            if(!isset($other_ilx[$ilx])) {
                $other_ilx[$ilx] = 1;
            } else {
                $other_ilx[$ilx] += 1;
            }
        }

        foreach($this_ilx as $ilx => $count) {
            if(!isset($other_ilx[$ilx])) return false;
            if($count - $other_ilx[$ilx] > 0) return false;
        }

        return true;
    }

    public function isUsable() {
        if(empty($this->fields())) return false;
        if(!$this->active || !$this->submitted) return false;
        return true;
    }

    public function hasSubjectField() {
        if(count(DatasetField::getByAnnotation($this, "subject")) == 1) {
            return true;
        }
        return false;
    }

    public function unsubmit() {
        if($this->submitted) {
            $this->submitted = false;
            $this->updateDB();
        }
    }

    public function nfields() {
        $count = DatasetField::getCount(Array("dataset_fields_template_id"), Array($this->id));
        return $count;
    }

    public function datasets() {
        if(is_null($this->_datasets)) {
            $this->_datasets = Dataset::loadArrayBy(Array("dataset_fields_template_id"), Array($this->id));
        }
        return $this->_datasets;
    }

    public function defaultILXCount() {
        $ilx_id = $GLOBALS["config"]["dataset-config"]["term"]["ilx"]["default"];
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select("dataset_fields", Array("count(*)"), "is", Array($this->id, $ilx_id), "where dataset_fields_template_id = ? and termid in (select id from terms where ilx = ?)");
        $cxn->close();
        return $count[0]["count(*)"];
    }
}
DatasetFieldTemplate::init();

?>
