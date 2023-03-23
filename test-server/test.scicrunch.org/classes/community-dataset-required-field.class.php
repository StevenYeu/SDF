<?php

class CommunityDatasetRequiredField extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "community_dataset_required_fields";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                    => self::fieldDef("id", "i", true),
            "cid"                   => self::fieldDef("cid", "i", true),
            "termid"                => self::fieldDef("termid", "i", false),
            "position"              => self::fieldDef("position", "i", false),
            "dataset_type_name"     => self::fieldDef("dataset_type_name", "s", true),
            "name"                  => self::fieldDef("name", "s", false),
            "timestamp"             => self::fieldDef("timestamp", "i", true),
            "multi"                 => self::fieldDef("multi", "i", false),
            "multi_suffixes"        => self::fieldDef("multi_suffixes", "s", false),
            "subject"               => self::fieldDef("subject", "i", false),
        );
    }
    protected function _set_name($val) {
        if(!$val) return false;
        if(!$this->checkUniqueName($val)) return false;
        if(strtolower($val) == "notes") return false;
        return $val;
    }
    protected function _get_multi($val) { return self::getBool($val); }
    protected function _set_multi($val) { return self::setBool($val); }
    protected function _get_multi_suffixes($val) {
        return explode(",", $val);
    }
    protected function _set_multi_suffixes($val) {
        if(!$this->multi) return "";
        if(!DatasetField::validateMultiSuffixes($val)) return false;
        return implode(",", $val);
    }
    protected function _get_subject($val) { return self::getBool($val); }
    protected function _set_subject($val) { return self::setBool($val); }

    private $_term;

    public static function createNewObj(Community $community, TermDBO $term, $name, $dataset_type_name, $subject, $multi, $multi_suffixes) {
        $next_position = count(self::loadArrayBy(Array("cid", "dataset_type_name"), Array($community->id, $dataset_type_name)));

        $timestamp = time();

        $obj = self::insertObj(Array(
            "id" => NULL,
            "cid" => $community->id,
            "termid" => $term->id,
            "name" => $name,
            "position" => $next_position,
            "dataset_type_name" => $dataset_type_name,
            "timestamp" => $timestamp,
            "multi" => $multi,
            "multi_suffixes" => $multi_suffixes,
            "subject" => $subject,
        ));
        return $obj;
    }

    public static function deleteObj($obj) {
        $cid = $obj->cid;
        $comm = new Community();
        $comm->getByID($cid);
        $dataset_type_name = $obj->dataset_type_name;

        parent::deleteObj($obj);

        self::resetPositionsAndSubject($comm, $dataset_type_name);
    }

    public function arrayForm() {
        return Array(
            "name" => $this->name,
            "position" => $this->position,
            "dataset_type_name" => $this->dataset_type_name,
            "timestamp" => $this->timestamp,
            "multi" => $this->multi,
            "multi_suffixes" => $this->multi_suffixes,
            "subject" => $this->subject,
        );
    }

    public function movePositionUp() {
        if($this->position == 0) return;
        $new_pos = $this->position - 1;
        $all_fields = self::loadArrayBy(Array("cid", "dataset_type_name"), Array($this->cid, $this->dataset_type_name));
        foreach($all_fields as $field) {
            if($field->position == $new_pos) {
                $field->position += 1;
                $field->updateDB();
                break;
            }
        }
        $this->position = $new_pos;
        $this->updateDB();
    }

    public function movePositionDown() {
        $all_fields = self::loadArrayBy(Array("cid", "dataset_type_name"), Array($this->cid, $this->dataset_type_name));
        if($this->position >= count($all_fields) - 1) return;
        $new_pos = $this->position + 1;
        foreach($all_fields as $field) {
            if($field->position == $new_pos) {
                $field->position -= 1;
                $field->updateDB();
                break;
            }
        }
        $this->position = $new_pos;
        $this->updateDB();
    }

    public static function resetPositionsAndSubject(Community $community, $type_name) {
        $fields = self::loadArrayBy(Array("cid", "dataset_type_name"), Array($community->id, $type_name));
        if(empty($fields)) return;
        usort($fields, function($a, $b) {
            if($a->position < $b->position) return -1;
            if($a->position > $b->position) return 1;
            return 0;
        });
        foreach($fields as $field) {
            if($field->position != $i) {
                $field->position = $i;
                $field->updateDB();
            }
        }

        /* check for subject fields */
        $has_subject = false;
        foreach($fields as $field) {
            if($field->subject) {
                $has_subject = true;
                break;
            }
        }
        /* default to first field being subject */
        if(!$has_subject) {
            $fields[0]->subject = true;
            $fields[0]->updateDB();
        }
    }

    public function checkUniqueName($name) {
        $fields = self::loadArrayBy(Array("cid", "dataset_type_name", "name"), Array($this->cid, $this->dataset_type_name, $name));
        if(!empty($fields)) {
            if(count($fields) == 1 && $fields[0]->id == $this->id) {
                return true;
            } else {
                return false;
            }
        }
        return true;
    }

    public function term() {
        if(is_null($this->_term)) {
            $this->_term = TermDBO::loadBy(Array("id"), Array($this->termid));
        }
        return $this->_term;
    }
}
CommunityDatasetRequiredField::init();

?>
