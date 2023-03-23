<?php

class DatasetUploadQueue extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "dataset_upload_queue";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                => self::fieldDef("id", "i", true),
            "dataset_id"        => self::fieldDef("dataset_id", "i", true),
            "uid"               => self::fieldDef("uid", "i", true),
            "timestamp"         => self::fieldDef("timestamp", "i", true),
            "updated_timestamp" => self::fieldDef("updated_timestamp", "i", false),
            "filename"          => self::fieldDef("filename", "s", true),
            "status"            => self::fieldDef("status", "s", false, Array("allowed_values" => self::$statuses)),
            "seen"              => self::fieldDef("seen", "i", false),
            "results"           => self::fieldDef("results", "s", false),
            "records_count"     => self::fieldDef("records_count", "i", true),
        );
    }
    protected function _get_seen($val) {
        return parent::getBool($val);
    }
    protected function _set_seen($val) {
        return parent::setBool($val);
    }
    protected function _get_results($val) {
        return parent::getJSON($val);
    }
    protected function _set_results($val) {
        return parent::setJSON($val);
    }

    const STATUS_NEW = "new";
    const STATUS_UPLOADING = "uploading";
    const STATUS_COMPLETE = "complete";
    const STATUS_ERROR = "error";
    private static $statuses = Array(
        self::STATUS_NEW,
        self::STATUS_UPLOADING,
        self::STATUS_COMPLETE,
        self::STATUS_ERROR,
    );

    public static function createNewObj(Dataset $dataset, User $user, $records_strings_array) {
        $timestamp = time();
        $records_count = count($records_strings_array);
        if($records_count == 0 || !$dataset->id || !$user->id) {
            return NULL;
        }

        // if more than 100 records, then split into 5 files. else just 1.
        if ($records_count > 100)
            $party = partition($records_strings_array, 5);
        else
            $party = partition($records_strings_array, 1);

        foreach ($party as $array) {
            // skip if $array has no records, which can happen if less than 5 lines ...
            if (count($array)) {
                $file_prefix = "records-".$user->id."-".$dataset->id."_";
                $new_file_name = tempnam(__DIR__ . "/../vars/dataset-queue", $file_prefix);

                $obj = self::insertObj(Array(
                    "id" => NULL,
                    "dataset_id" => $dataset->id,
                    "uid" => $user->id,
                    "timestamp" => $timestamp,
                    "updated_timestamp" => $timestamp,
                    "filename" => $new_file_name,
                    "status" => "new",
                    "seen" => false,
                    "results" => NULL,
                    "records_count" => count($array),
                ));

                if(is_null($obj)) return NULL;

                $new_file = fopen($new_file_name, "w");
                $write_string = '';

                fwrite($new_file, json_encode($array));
                //fwrite($new_file, print_r($records_strings_array, true));
                fclose($new_file);
                chmod($new_file_name, 0666);
            }
        }
        return $obj;
    }

    public static function deleteObj($obj) {
        return;
    }

    public function upload() {
        $user = new User();
        $user->getByID($this->uid);

        /* get dataset */
        $dataset = Dataset::loadBy(Array("id"), Array($this->dataset_id));
        if(is_null($dataset)) {
            $this->error("dataset does not exist");
            return;
        }

        /* read data file */
        ini_set('memory_limit', '150M');
        $file_data = file_get_contents($this->filename);
        $data = json_decode($file_data, true);
        if(!$data) {
            $this->error("could not read file data");
            return;
        }

        $this->setStatus(self::STATUS_UPLOADING, NULL);
        $counter = 0;
        $nrecords = count($data);
        $bad_rows = Array();
        $size_limit = 50000;
        $fields = $dataset->fields();
        usort($fields, function($a, $b) {
            if($a->position < $b->position) return -1;
            if($a->position > $b->position) return 1;
            return 0;
        });
        while($counter < $nrecords) {
            $size = 0;
            $records = Array();
            $starting_counter = $counter;
            while($counter < $nrecords && $size < $size_limit) {
                $current_record_string = $data[$counter];
                $size += strlen($current_record_string);
                $record_array = json_decode($current_record_string, true);
                $record = Array();
                foreach($fields as $i => $field) {
                    $record[$field->name] = $record_array[$i];
                }
                $records[] = $record;
                $counter += 1;
            }

            $validation = $dataset->insertManyRecords($records, $user, $this->timestamp);
            foreach($validation as $i => $v) {
                if($v["success"]) {
                    continue;
                }
                $bad_row_index = $starting_counter + $i;
                $mini_validation = Array(
                    "index" => $bad_row_index,
                    "record" => $data[$bad_row_index],
                    "missing_fields" => $v["missing_fields"],
                    "bad_fields" => $v["bad_fields"],
                    "messages" => $v["messages"],
                );
                $validations[] = $mini_validation;
            }
        }

        $this->setStatus(self::STATUS_COMPLETE, $validations);
        /* Should store files for post upload validation and future validation */
        /* unlink($this->filename); */

    }

    public function setStatus($status, $results) {
        $this->status = $status;
        $this->updated_timestamp = time();
        $this->results = $results;
        $this->updateDB();
    }

    public function error($message) {
        $this->setStatus(self::STATUS_ERROR, Array("error" => $message));

        /* Temporarly remove unlink for debugging */
        /* unlink($this->filename); */

    }

    public static function inQueueCountForDataset(Dataset $dataset) {
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select(self::$_table_name, Array("count(*)"), "iss", Array($dataset->id, self::STATUS_NEW, self::STATUS_UPLOADING), "where dataset_id = ? and (status = ? or status = ?)");
        $cxn->close();

        return $count[0]["count(*)"];
    }

    public static function inQueueRecordCountForDataset(Dataset $dataset) {
        $cxn = new Connection();
        $cxn->connect();
        $counts = $cxn->select(self::$_table_name, Array("records_count"), "iss", Array($dataset->id, self::STATUS_NEW, self::STATUS_UPLOADING), "where dataset_id = ? and (status = ? or status = ?)");
        $cxn->close();

        $total = 0;
        foreach($counts as $c) {
            $total += $c["records_count"];
        }
        return $total;
    }

    public static function uploadDatasets() {
        $lock_name = "dataset-upload-queue-lock";
        $lock = ServerCache::createNewObj($lock_name);
        if(is_null($lock)) { // lock already exists so exit
            return;
        }

        try {
            $queue_items = DatasetUploadQueue::loadArrayBy(Array("status"), Array(self::STATUS_NEW));
            foreach($queue_items as $qi) {
                $qi->upload();
            }
        } catch(Exception $e) {
            ServerCache::deleteObj($lock);
        }
        ServerCache::deleteObj($lock);
    }
}
DatasetUploadQueue::init();

/**
 * 
 * @param Array $list
 * @param int $p
 * @return multitype:multitype:
 * @link http://www.php.net/manual/en/function.array-chunk.php#75022
 */
 function partition(Array $list, $p) {
    $listlen = count($list);
    $partlen = floor($listlen / $p);
    $partrem = $listlen % $p;
    $partition = array();
    $mark = 0;
    for($px = 0; $px < $p; $px ++) {
        $incr = ($px < $partrem) ? $partlen + 1 : $partlen;
        $partition[$px] = array_slice($list, $mark, $incr);
        $mark += $incr;
    }
    return $partition;
}

?>
