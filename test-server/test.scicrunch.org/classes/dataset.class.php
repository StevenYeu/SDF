<?php

class Dataset extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "datasets";
    protected static $_primary_key_field = "id";

    protected function __construct($vals) {
        parent::__construct($vals);
        if(is_null($this->record_count)) {
            $this->updateRecordCount();
        }
    }

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                            => self::fieldDef("id", "i", true),
            "uid"                           => self::fieldDef("uid", "i", true),
            "name"                          => self::fieldDef("name", "s", false),
            "long_name"                     => self::fieldDef("long_name", "s", false),
            "description"                   => self::fieldDef("description", "s", false),
            "publications"                  => self::fieldDef("publications", "s", false),
            "timestamp"                     => self::fieldDef("timestamp", "i", true),
            "dataset_fields_template_id"    => self::fieldDef("dataset_fields_template_id", "i", false),
            "lab_status"                    => self::fieldDef("lab_status", "s", false, Array("allowed_values" => Dataset::$lab_statuses)),
            "editor_status"                 => self::fieldDef("editor_status", "s", false),
            "curation_status"               => self::fieldDef("curation_status", "s", false),
//            "curation_status"               => self::fieldDef("curation_status", "s", false, Array("allowed_values" => Dataset::$curation_statuses)),
            "field_set"                     => self::fieldDef("field_set", "s", false),
            "record_count"                  => self::fieldDef("record_count", "i", false),
            "active"                        => self::fieldDef("active", "i", false),
            "last_updated_time"             => self::fieldDef("last_updated_time", "i", false),
            "last_uploaded_time"            => self::fieldDef("last_uploaded_time", "i", false),
        );
    }
    protected function _set_name($val) { return self::setNotEmpty($val); }
    protected function _set_long_name($val) { return self::setNotEmpty($val); }
    protected function _set_description($val) { return self::setNotEmpty($val); }
    protected function _set_last_updated_time($val) { return self::setNotEmpty($val); }
    protected function _set_publications($val) {
        if(!\helper\mentionIDFormatMultiple($val, ",")) return false;
        return $val;
    }
    protected function _set_editor_status($val) { return self::setNotEmpty($val); }
    protected function _set_curation_status($val) { return self::setNotEmpty($val); }

    protected function _get_field_set($val) {
        if(is_null($val)) return Array();
        return explode(",", $val);
    }
    protected function _set_field_set($val) {
        if(is_null($val)) return NULL;
        return implode(",", $val);
    }
    protected function _set_record_count($val) {
        if($val < 0) return false;
        return $val;
    }
    protected function _get_active($val) {
        return parent::getBool($val);
    }
    protected function _set_active($val) {
        return parent::setBool($val);
    }

    const LAB_STATUS_PENDING = "pending";
    const LAB_STATUS_REJECTED = "rejected";
    const LAB_STATUS_APPROVED = "approved-doi";
    const LAB_STATUS_APPROVEDINTERNAL = "approved-internal";    // only approved for internal viewing within lab
    const LAB_STATUS_APPROVEDCOMMUNITY = "approved-community";
    const LAB_STATUS_NOTSUBMITTED = "not-submitted";    // only dataset creator can see it
    const LAB_STATUS_REQUESTDOI = "request-doi";
    public static $lab_statuses = Array(self::LAB_STATUS_PENDING, self::LAB_STATUS_REJECTED, self::LAB_STATUS_APPROVED, self::LAB_STATUS_REQUESTDOI, self::LAB_STATUS_APPROVEDCOMMUNITY, self::LAB_STATUS_APPROVEDINTERNAL, self::LAB_STATUS_NOTSUBMITTED, self::LAB_STATUS_NOTSUBMITTED);
    public static $pretty_lab_statuses = Array(
        self::LAB_STATUS_PENDING => "Pending PI approval",
        self::LAB_STATUS_REJECTED => "Rejected",
        self::LAB_STATUS_APPROVED => "Public DOI",
        self::LAB_STATUS_REQUESTDOI => "Request DOI",
        self::LAB_STATUS_APPROVEDCOMMUNITY => "Community Space",
        self::LAB_STATUS_APPROVEDINTERNAL => "Lab Space",
        self::LAB_STATUS_NOTSUBMITTED => "Personal Space",
    );

    public static $pretty_lab_colors = Array(
        self::LAB_STATUS_PENDING => "red",
        self::LAB_STATUS_REJECTED => "red",
        self::LAB_STATUS_APPROVED => "green",
        self::LAB_STATUS_REQUESTDOI => "#F0E68C",
        self::LAB_STATUS_APPROVEDCOMMUNITY => "blue",
        self::LAB_STATUS_APPROVEDINTERNAL => "orange",
        self::LAB_STATUS_NOTSUBMITTED => "#8A2BE2",
    );
    const CURATION_STATUS_REQUESTDOI_UNLOCKED = "request-doi-unlocked";
    const CURATION_STATUS_REQUESTDOI_LOCKED = "request-doi-locked";
    const CURATION_STATUS_PUBLISHED = "published";

    public static $curation_statuses = Array(self::CURATION_STATUS_REQUESTDOI_UNLOCKED, self::CURATION_STATUS_REQUESTDOI_LOCKED, self::CURATION_STATUS_PUBLISHED);
    public static $pretty_curation_statuses = Array(
        self::CURATION_STATUS_REQUESTDOI_UNLOCKED => "DOI Requested <i class='fa fa-unlock'></i>",
        self::CURATION_STATUS_REQUESTDOI_LOCKED => "DOI Requested <i class='fa fa-lock'></i>",
        self::CURATION_STATUS_PUBLISHED => "",
    );

    private $_template;
    private $_metadata;
    private $_submissions;
    private $_submitted_field_names;
    private $_mongo_subjects = Array();
    private $_user;
    private $_can_edit = false;
    private $_is_moderator_viewing = false;
    private $_is_locked = false;


    static public function createNewObj(DatasetFieldTemplate $dataset_fields_template, $user, $name, $long_name, $description, $publications, $raw_metadata) {
        $timestamp = time();

        if(!$name || !$long_name || !$description || !$dataset_fields_template->id || !$dataset_fields_template->isUsable()) {
            return NULL;
        }
        if(!\helper\mentionIDFormatMultiple($publications, ",")) return NULL;
        if(!$dataset_fields_template->lab()->uniqueDatasetName($name)) {
            return NULL;
        }

        $dataset_fields_template_id = $dataset_fields_template->id;

        $obj = self::insertObj(Array(
            "id" => NULL,
            "uid" => $user->id,
            "name" => $name,
            "long_name" => $long_name,
            "description" => $description,
            "publications" => $publications,
            "timestamp" => $timestamp,
            "dataset_fields_template_id" => $dataset_fields_template_id,
            "lab_status" => self::LAB_STATUS_NOTSUBMITTED,
            "editor_status" => NULL,
            "curation_status" => NULL,
            "field_set" => NULL,
            "record_count" => 0,
            "active" => true,
            "last_updated_time" => $timestamp,
            "last_uploaded_time" => 0,
        ));

        if(is_null($obj)) return NULL;
        if(DatasetMetadata::addMetadata($raw_metadata, $obj) === false) {
            Dataset::deleteObj($obj);
            return NULL;
        }

        $obj->remakeCollection(true);
        return $obj;
    }

    public static function deleteObj($obj, User $user = NULL) {
        /* delete the collection (soft delete, doesn't actually delete documents) */
        $obj->dropCollection();

        /* set active to false */
        $obj->active = false;
        $obj->updateDB(true, $user);
    }

    public function updateDB($update_time = true, User $user = NULL) {
        if($update_time) {
            $this->last_updated_time = time();
        }
        $this->saveHistory("update", $user);
        parent::updateDB();
    }

    public static function loadBy($fields, $values, $options, $exactly_one = false, $allow_inactive = false) {
        if(!$allow_inactive && !in_array($fields, "active")) {
            $fields[] = "active";
            $values[] = 1;
        }
        return parent::loadBy($fields, $values, $exactly_once);
    }

    public static function loadArrayBy($fields, $values, $options, $allow_inactive = false) {
        if(!$allow_inactive && !in_array($fields, "active")) {
            $fields[] = "active";
            $values[] = 1;
        }
        return parent::loadArrayBy($fields, $values, $options);
    }

    public static function getCount($fields, $values, $options, $allow_inactive = false) {
        if(!$allow_inactive && !in_array($fields, "active")) {
            $fields[] = "active";
            $values[] = 1;
        }
        return parent::getCount($fields, $values, $options);
    }

    public function dropCollection() {
        $collection = MongoDataset::generateDataset($this->collectionName());
        $timestamp = time();
        $collection->updateMany(Array(), Array('$set' => Array("_deleted" => $timestamp)));
        $this->updateRecordCount();
    }

    public function template() {
        if(is_null($this->_template)) {
            $this->_template = DatasetFieldTemplate::loadBy(Array("id"), Array($this->dataset_fields_template_id));
        }
        return $this->_template;
    }

    public function fields() {
        return $this->template()->fields();
    }

    public function lab() {
        return $this->template()->lab();
    }

    public function mongoSubjects($refresh = false) {
        if($refresh || empty($this->_mongo_subjects)) {
            $coll = MongoDataset::generateInfoCollection();
            $raw_results = $coll->findOne(Array("dataset" => $this->collectionName()));
            if($raw_results) {
                $results = json_decode(json_encode($raw_results->jsonSerialize()), true);
                if($results["subjects"]) {
                    $this->_mongo_subjects = $results["subjects"];
                }
            }
        }
        return $this->_mongo_subjects;
    }

    public function collectionName() {
        $name = "dataset" . $this->id;
        return $name;
    }

    public function insertRecord($records, User $user) {
        if($this->isSubmitted()) return Array("success" => false);
        $validation = $this->validateRecord($records);
        if(!$validation["success"]) return $validation;
        $fields = $validation["fields"];
        $mongo = MongoDataset::generateDataset($this->collectionName());
        $fields["_inserted"] = time();
        $fields["_inserter"] = $user->id;
        $mongo->insertOne($fields);
        $this->updateSubjects(Array($fields));
        $this->updateRecordCount();
        $this->last_uploaded_time = time();
        $this->updateDB(false);
        return $validation;
    }

    public function insertManyRecords($records, User $user, $timestamp = NULL) {
        if($this->isSubmitted()) return false;
        $validations = Array();
        $mongo = MongoDataset::generateDataset($this->collectionName());
        $all_fields = Array();
        if(is_null($timestamp)) {
            $timestamp = time();
        }
        foreach($records as $record) {
            $validation = $this->validateRecord($record);
            $validations[] = $validation;
            if(!$validation["success"]) {
                continue;
            }
            $fields = $validation["fields"];
            $fields["_inserted"] = $timestamp;
            $fields["_inserter"] = $user->id;
            $all_fields[] = $fields;
        }
        $mongo->insertMany($all_fields);
// try this ...
        $this->updateRecordFieldSet();
        $this->updateSubjects($all_fields);
        $this->updateRecordCount();
        $this->last_uploaded_time = time();
        $this->updateDB(false);
        return $validations;
    }

    public function insertManyRecordsFromString($records_strings_array, User $user) {
        if($this->isSubmitted()) return false;

        $queue = DatasetUploadQueue::createNewObj($this, $user, $records_strings_array);
        return !is_null($queue);
    }

    public function deleteRecord($recordid) {
        if($this->isSubmitted()) return;
        $timestamp = time();
        $fid = filter_var($recordid, FILTER_SANITIZE_STRING);
        $object_id = new MongoDB\BSON\ObjectID($fid);
        $mongo = MongoDataset::generateDataset($this->collectionName());
        $mongo->updateOne(Array("_id" => $object_id), Array('$set' => Array("_deleted" => $timestamp)));
        $this->updateRecordCount();
    }

    /**
     * searchRecords
     * return an array of array of records or a string in csv format
     *
     * @param string query
     * @param int offset
     * @param int count
     * @param as_csv bool
     * @param csv_fields array of strings, required if as_csv == true
     * @return mixed either an array of associative arrays or a string in csv format
     */
    public function searchRecords($query, $offset, $count, $as_csv = false, $csv_fields = NULL) {
        /* build query filter */
        $filter = self::buildTextSearchFilter($query);

        /* subject field */
        $subject_field = DatasetField::getByAnnotation($this->template(), "subject")[0];

        /* build options */
        $options = Array("sort" => Array("_id" => 1));
        if(is_int($offset)) $options["skip"] = $offset;
        if(is_int($count)) $options["limit"] = $count;
        //$options['sort'] = Array($subject_field->name => 1);
        $options["batchSize"] = 2000;

        $mongo = MongoDataset::generateDataset($this->collectionName());
        $results = $mongo->find($filter, $options);
        
        /* create a map of encoded to decoded mongo field names */
        /* so only the encoded fields need to be iterated over, not every single field */
        $encoded_field_set = Array();

        /* get the fields */
        $fields = $this->fields();
        foreach($fields as $fs) {
            $encoded_name = DatasetField::nameMongoEncodeStatic($fs->name);
            
            if($encoded_name != $fs->name) {
                $encoded_field_set[$encoded_name] = $fs->name;
            }
        }

        $fresults_str = "";
        $fresults = Array();
        foreach($results as $r) {
            $res = $r;
            
            foreach($encoded_field_set as $encoded_name => $decoded_name) {
                if(isset($res[$encoded_name])) {
                    $res[$decoded_name] = $res[$encoded_name];

                    // hmm, why unset something that was just set
                    // unset($res[$decoded_name]);
                    unset($res[$encoded_name]);
                }
            }
            $res["_id"] = (string) $res["_id"];
            if($as_csv) {
                $fp = fopen("php://temp", "r+");
                $csv_array = Array();
                foreach($csv_fields as $cf) {
                    $csv_array[] = $res[$cf];
                }
                fputcsv($fp, $csv_array);
                rewind($fp);
                $line_str = fread($fp, 1048576);
                fclose($fp);
                $fresults_str .= $line_str;
            } else {
                $fresults[] = $res;
            }
            unset($r);
            unset($res);
        }

        if($as_csv) {
            return $fresults_str;
        } else {
            return $fresults;
        }
    }

    public function getRecordFieldSet() {
        $real_fields = $this->fields();
        usort($real_fields, function($a, $b) {
            return $a->position - $b->position;
        });

        $field_names = Array();
        foreach($real_fields as $rf) {
            $field_names[] = $rf->name;
        }

        return $field_names;
    }

    public function updateRecordFieldSet() {
        if($this->field_set) {
            return;
        }

        $fields = $this->getRecordFieldSet();
        $this->field_set = $fields;
        $this->updateDB(false);
    }

    public function getRecordCount($query) {
        $filter = self::buildTextSearchFilter($query);
        $mongo = MongoDataset::generateDataset($this->collectionName());
        $count = $mongo->count($filter);
        return $count;
    }

    private function validateRecord($records) {
        /* if validation fails, success = false.  missing fields are missing fields.  bad fields are fields that are poorly configured.  fields are an associative array of the filter_var'ed fields */
        $return_array = Array("success" => true, "missing_fields" => Array(), "bad_fields" => Array(), "fields" => Array(), "messages" => Array());

        /* Check if string value is a single digit 0 and set flag to handle field not being returned */
        $zero_flag = 0;
        $zero_tmp = "ODC+0+ZZYYXX";
        foreach	($records as $key => $value) {
          if ( strcmp($records[$key],"0") == 0 ) {
            $records[$key] = $zero_tmp;
            $zero_flag =	1;
          }
        }

        /* get the fields */
        $fields = $this->fields();
        $user_submitted_field_names = array_keys($records);
        $usable_field_names = Array();

        /* prevent repeats */
        $used_sfn = Array();
        foreach($fields as $field) {
            /* make sure the field name exists if it's required */
            if(!$records[$field->name]) {
                if($field->required === 1 || $field->isSubject()) {
                    $return_array["success"] = false;
                    $return_array["missing_fields"][] = $field->name;
                }
                continue;
            } else {
                $usable_field_names[] = Array("name" => $field->name, "field" => $field, "mongoname" => $field->nameMongoEncode());
            }
        }

        foreach($usable_field_names as $ufn) {

            /* get the value from the record */
            $value = $records[$ufn["name"]];

            /* sanitize and validate the record */
            $fvalue = $ufn["field"]->sanitizeFieldValue($value);
            $validation = $ufn["field"]->validateValue($fvalue);

            /* make sure the sanitization worked */
            if(is_null($fvalue) || !$validation["success"]) {
                $return_array["success"] = false;
                $return_array["bad_fields"][] = $ufn["name"];
                if(!is_null($validation["message"])) {
                    $return_array["messages"][] = $validation["message"];
                }
                continue;
            }

            /* Substitute out place holder for single digit zero*/
            if ($zero_flag == 1 && $fvalue == $zero_tmp) $fvalue = "0";

            /* insert the record */
            $return_array["fields"][$ufn["mongoname"]] = $fvalue;
        }

        if(isset($records["notes"])) {
            $return_array["fields"]["notes"] = (string) filter_var($records["notes"], FILTER_SANITIZE_STRING);
        } else {
            $return_array["fields"]["notes"] = "";
        }

        return $return_array;
    }

    public function arrayForm($no_template_fields = false) {
        $submissions = $this->submissions();
        $submissions_array = Array();
        foreach($submissions as $sub) {
            $submissions_array[] = $sub->arrayForm();
        }

        $in_queue_count = DatasetUploadQueue::inQueueRecordCountForDataset($this);
        $data = Array(
            "id" => $this->id,
            "uid" => $this->uid,
            "name" => $this->name,
            "long_name" => $this->long_name,
            "description" => $this->description,
            "publications" => $this->publications,
            "metadata" => $this->metadataArrayForm(),
            "total_records_count" => $this->record_count,
            "timestamp" => $this->timestamp,
            "submissions" => $submissions_array,
            "lab_status" => $this->lab_status,
            "curation_status" => $this->curation_status,
            "template_id" => $this->template()->id,
            "lab_id" => $this->lab()->id,
  //          "field_set" => $this->field_set, ... "field_set" is not reliable. many have null
            "field_set" => $this->fields(),
            "lab_status_pretty" => $this->labStatusPretty(),
            "lab_status_color" => $this->labStatusColor(),
            "editor_status" => $this->editor_status,
            "curation_status_pretty" => $this->curationStatusPretty(),
            "can_edit" => $this->_can_edit,
            "is_moderator_viewing" => $this->_is_moderator_viewing,
            "template" => $this->template()->arrayForm($no_template_fields),
            "in_queue_count" => $in_queue_count,
            "last_updated_time" => $this->last_updated_time == 0 ? $this->timestamp : $this->last_updated_time,
            "owner" => $this->user()->firstname . " " . $this->user()->lastname,
            "owner_reversed" => $this->user()->lastname . ", " . $this->user()->firstname,
            "is_locked" => $this->isLocked(),
            "subject_field_name" => $this->getSubject()
        );

        // Template holds the complete data in it's field values. Too large to pull for user info.
        if ($no_template_fields == false) {
            $template = Array("template" => $this->template()->arrayForm());
            $data = array_merge($data, $template);
        }

        return $data;
    }

    public function metadataArrayForm() {
        $metadata_objs = $this->metadata();

        $metadata_array = Array();
        foreach($metadata_objs as $mo_id => $mo) {
            $obj = $mo["obj"];
            $md = Array("name" => $mo["name"]);
            if(is_null($obj)) {
                $md["val"] = NULL;
            } else {
                $md["val"] = $obj->val;
            }
            $metadata_array[$mo_id] = $md;
        }

        return $metadata_array;
    }

    public function metadata() {
        if(is_null($this->_metadata)) {
            $metadata = DatasetMetadata::loadArrayBy(Array("datasetid"), Array($this->id));
            $metadata_fields = DatasetMetadataField::loadArrayBy(Array("labid"), Array($this->lab()->id));
            $metadata_records = Array();
            foreach($metadata_fields as $mdf) {
                $metadata_records[$mdf->id] = Array("name" => $mdf->name, "obj" => NULL);
            }
            foreach($metadata as $md) {
                $metadata_records[$md->dataset_metadata_id]["obj"] = $md;
            }

            $this->_metadata = $metadata_records;
        }

        return $this->_metadata;
    }

    public function remakeCollection($new) {
        /* delete the collection when fields are updated */
        $this->dropCollection();

        /* remake the collection */
        $database = MongoDataset::generateDatasetDatabase();
        if($new) {
            $database->createCollection($this->collectionName());
        }

        /* create new info record */
        $info_coll = MongoDataset::generateInfoCollection();
        $info_coll->replaceOne(Array("dataset" => $this->collectionName()), Array(
            "dataset" => $this->collectionName(),
            "subjects" => Array(),
        ), Array("upsert" => true));

        /* create a new index */
        $collection = MongoDataset::generateDataset($this->collectionName());
        $collection->createIndex(Array('$**' => "text"), Array("name" => "search_index"));
    }

    public function deleteAllRecords() {
        if($this->isSubmitted()) return;
        $this->remakeCollection(false);
    }

    static public function buildTextSearchFilter($query) {
        if($query && is_string($query)) {
            $filter = Array(
                '$and' => Array(
                    Array('$text' => Array('$search' => $query)),
                    Array('_deleted' => Array('$exists' => false)),
                )
            );
        } else {
            $filter = Array("_deleted" => Array('$exists' => false));
        }
        return $filter;
    }

    public function getTotalRecordsCount() {
        $count = $this->getRecordCount("");
        return $count;
    }

    public function checkCommunityTemplate($community) {
        $good = true;
        $comm_templates = $community->datasetFieldTemplates();
        if(!empty($comm_templates)) {
            $good = false;
            foreach($comm_templates as $ct) {
                if($ct->datasetTemplate()->hasAllFields($this->template())) {
                    $good = true;
                    break;
                }
            }
        }
        return $good;
    }

    public function submitToCommunity($community) {
        if($this->hasInQueue()) {
            return NULL;
        }
        if($this->lab_status !== self::LAB_STATUS_APPROVED) return NULL;
        $good = $this->checkCommunityTemplate($community);

        if($good) {
            $submission = CommunityDataset::createNewObj($this, $community);
            return $submission;
        }
        return NULL;
    }

    public function hasInQueue() {
        $count = DatasetUploadQueue::inQueueCountForDataset($this);
        if($count > 0) {
            return true;
        }
        return false;
    }

    public function isSubmitted() {
        $submission = CommunityDataset::loadBy(Array("datasetid"), Array($this->id));
        if(is_null($submission)) return false;
        return true;
    }

    public function submissions() {
        if(is_null($this->_submissions)) {
            $this->_submissions = CommunityDataset::loadArrayBy(Array("datasetid"), Array($this->id));
        }
        return $this->_submissions;
    }

    public function getSubject() {
        $annotation_field = DatasetField::getByAnnotation($this->template(), "subject")[0];
        if(!$annotation_field) 
            return NULL;

        return $annotation_field->name;
    }

    public function updateSubjects($fields) {
        $subjects = $this->mongoSubjects(true);
        $annotation_field = DatasetField::getByAnnotation($this->template(), "subject")[0];
        if(!$annotation_field) throw new Exception("missing subject field");
        $new_subjects = Array();
        $new_subjects_set = Array();
        foreach($fields as $field) {
            $exists = false;
            $field_value = $fields[$annotation_field->name];
            if(isset($new_subjects_set[$field_value])) {
                $exists = true;
            } else {
                foreach($subjects as $subject) {
                    if($subject["name"] == $field_value) {
                        $exists = true;
                        break;
                    }
                }
            }
            if(!$exists) {
                $new_subjects[] = Array("name" => $field_value, "id" => "");
                $new_subjects_set[$field_value] = true;
            }
        }
        if(!empty($new_subjects)) {
            $coll = MongoDataset::generateInfoCollection();
            $coll->updateOne(Array("dataset" => $this->collectionName()), Array('$push' => Array("subjects" => Array('$each' => $new_subjects))));
        }
    }

    public function validateAllRecords() {
        $response = Array();
        $template_fields = $this->template()->fields();
        $subject_field = DatasetField::getByAnnotation($this->template(), "subject");
        if(empty($subject_field)) {
            $response[] = "No subject field set";
            return $response;
        }
        $subject_field = $subject_field[0];
        $subjects = $this->mongoSubjects();
        $multi_fields = Array();
        foreach($template_fields as $tf) {
            if($tf->id == $subject_field->id) continue;
            if($tf->multi) {
                $multi_fields[$tf->name] = Array();
                foreach($tf->multi_suffixes as $ms) {
                    $multi_fields[$tf->name][$ms] = true;
                }
            }
        }
        if(empty($multi_fields)) return $response;
        $data = $this->searchRecords("", 0, MAXINT);
        $subject_data = Array();
        foreach($data as $d) {
            if(!isset($subject_data[$d[$subject_field->name]])) {
                $subject_data[$d[$subject_field->name]] = Array();
            }
            $subject_data[$d[$subject_field->name]][] = $d;
        }
        foreach($subject_data as $name => $val) {
            $missing_multi_fields = self::subjectMissingMultifFields($val, $multi_fields);
            foreach($missing_multi_fields as $field_name => $field_vals) {
                foreach($field_vals as $field_vals_name => $fv) {
                    $response[] = $name . " missing " . $field_vals_name . " for " . $field_name ;
                }
            }
        }
        return $response;
    }

    private static function subjectMissingMultifFields($subject_data, $multi_fields) {
        foreach($subject_data as $sd) {
            foreach($sd as $key => $val) {
                unset($multi_fields[$key][$val]);
                if(empty($multi_fields[$key])) {
                    unset($multi_fields[$key]);
                }
                if(empty($multi_fields)) return Array();
            }
        }
        return $multi_fields;
    }

    public function isVisible(User $user = NULL) {
        if($this->lab_status === self::LAB_STATUS_APPROVED) {   // dataset is public
            return true;
        }

        if(is_null($user)) {
            return false;
        }

        if($this->uid === $user->id) {
            return true;
        }

        if($this->lab_status == self::LAB_STATUS_APPROVEDCOMMUNITY && $this->lab()->community()->isMember($user)) {
            return true;
        }

        $lab_membership = LabMembership::loadByLabAndUser($this->lab(), $user);
        if(is_null($lab_membership)) {
            return false;
        }

        if($lab_membership->level >= 2) {
            return true;
        }

        if($lab_membership->level >= 1 && $this->lab_status === self::LAB_STATUS_APPROVEDINTERNAL) {
            return true;
        }

        return false;
    }

    public static function loadByCommunityAndUser(Community $community, User $user, $count, $offset) {
        $cxn = new Connection();
        $cxn->connect();
        $dataset_rows = $cxn->select(
            "datasets d
                inner join dataset_fields_templates dt on d.dataset_fields_template_id = dt.id
                inner join labs l on dt.labid = l.id
                inner join communities c on l.cid = c.id
                right join lab_memberships lm on l.id = lm.labid
                left join community_access ca on l.cid = ca.cid",
            Array("distinct(d.id)", "d.*"),
            "isisiisiisiii",
            Array($community->id, self::LAB_STATUS_APPROVED, $community->id, self::LAB_STATUS_APPROVEDINTERNAL, $user->id, $community->id, self::LAB_STATUS_APPROVEDCOMMUNITY, $user->id, $community->id, self::LAB_STATUS_REQUESTDOI, $user->id, $offset, $count),
            "where ((c.id=? and d.lab_status=?) or (c.id=? and d.lab_status=? and lm.uid=? and lm.level > 0) or (c.id=? and d.lab_status=? and ca.uid=? and ca.level > 0) or (c.id=? and d.lab_status=? and ca.uid=? and ca.level > 0)) and d.active = 1 limit ?,?"
        );
        $cxn->close();

        $datasets = Array();
        foreach($dataset_rows as $dr) {
            $datasets[] = new Dataset($dr);
        }

        return $datasets;
    }

    public function user() {
        if(is_null($this->_user)) {
            $this->_user = new User();
            $this->_user->getByID($this->uid);
        }
        return $this->_user;
    }

    public function canEdit(User $user = NULL) {
        if(is_null($user)) return false;
        if($user->id == $this->uid) return true;
        if($this->lab()->isModerator($user)) return true;
        return false;
    }

    // need to know if the person viewing the dataset is moderator/pi, so if delete can send them to admin page
    public function isModeratorViewing(User $user = NULL) {
        if(is_null($user)) return false;
        if($this->lab()->isModerator($user)) return true;
        return false;
    }

    public function isLocked() {
        if (($this->curation_status == 'request-doi-locked') || ($this->curation_status == 'curation-approved') || ($this->curation_status == 'published'))
            return true;
        return false;
    }

    public function updateRecordCount() {
        $this->record_count = $this->getTotalRecordsCount();
        $this->updateDB(false);
    }

    /**
     * canEditSave
     * if user can edit, saved in the objects state for use by arrayForm later
     *
     * @param User user
     */
    public function canEditSave(User $user = NULL) {
        $this->_can_edit = $this->canEdit($user);
    }

    /**
     * canDeleteDatasetAdmin
     * if user is Moderator/PI, saved in the objects state for use by arrayForm later so that can redirect to admin page
     *
     * @param User user
     */
    public function canDeleteDatasetAdmin(User $user = NULL) {
        $this->_is_moderator_viewing = $this->isModeratorViewing($user);
    }

    public function labStatusPretty() {
        return self::$pretty_lab_statuses[$this->lab_status] ?: "";
    }

    public function labStatusColor() {
        return self::$pretty_lab_colors[$this->lab_status] ?: "";
    }

    public function curationStatusPretty() {
        return self::$pretty_curation_statuses[$this->curation_status] ?: "";
    }

    public static function getCommunityCount(Community $community) {
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select("((communities c inner join labs l on c.id = l.cid) inner join dataset_fields_templates dft on l.id = dft.labid) inner join datasets d on d.dataset_fields_template_id = dft.id", Array("count(*)"), "i", Array($community->id), "where c.id = ? AND d.active = 1");
        $cxn->close();
        return $count[0]["count(*)"];
    }
/*
    public function requestDOI($datasetid) {
        $dataset = Dataset::loadBy(Array("id"), Array($datasetid));
        $templatefields = DatasetFieldTemplate::loadBy(Array("id"), Array($dataset->dataset_fields_template_id));
        $lab = Lab::loadBy(Array("id"), Array($templatefields->labid));

        $community = new Community();
        $community->getByID($lab->cid);

        // send email to curation team 
        $curator_email = 'michiu@ucsd.edu';

        $subject = "ODC-SCI Dataset: " . $dataset->id . " Request DOI";
        $html_message = Array("Dataset: " . $dataset->id . " Request DOI",
            '<a href="https://scicrunch.org/php/labs/curator.php">Go to curation dashboard</a>'
        );
        $text_message = 'A DOI has been requested. Follow this link https://scicrunch.org/php/labs/curator.php to go to the curation dashboard.';
        \helper\sendEmail($curator_email, \helper\buildEmailMessage($html_message, 1, $community), $text_message, $subject, NULL);

        // save overview information
        DatasetDoiKeyValues::createNewObj($dataset, $datasetid, $lab->name, 'overview', 'lab', 0);  
        DatasetDoiKeyValues::createNewObj($dataset, $datasetid, $community->name, 'overview', 'community', 0);  
        DatasetDoiKeyValues::createNewObj($dataset, $datasetid, $dataset->record_count, 'overview', 'recordcount', 0);
        DatasetDoiKeyValues::createNewObj($dataset, $datasetid, $templatefields->nfields(), 'overview', 'fields', 0);

        // save CSV file and get some metadata
        $saveonly = true;
        include $_SERVER["DOCUMENT_ROOT"] . "/php/dataset-csv.php";
        DatasetDoiKeyValues::createNewObj($dataset, $datasetid, $filesizee, 'overview', 'filesize', 0);
        DatasetDoiKeyValues::createNewObj($dataset, $datasetid, str_replace("/tmp/", "", $outfile), 'overview', 'csv', 0);
    }
*/
    public function curationStatus() {
        $this->curation_status = $this->getCurationStatus($this->id);
    }

    public static function getCurationStatus($datasetid) {
        $cxn = new Connection();
        $cxn->connect();
        $status = $cxn->select("dataset_status", Array("status"), "i", Array($datasetid), "where dataset_id=? ORDER BY id desc LIMIT 1");
        $cxn->close();
        return $status[0]["status"];
    }
}
Dataset::init();

?>
