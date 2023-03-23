<?php

class SavedSearchDataData extends SubscriptionData {
    const MODIFIED_TIME = "modified_time";  // takes the value of the UPDATED_TIME variable if the NEW_DATA_FLAG changes from false to true
    const UPDATED_TIME = "updated_time";    // takes the current time value every time this script runs.  this value is used for searching new data
    const MODIFIED_COUNT = "count";
    const NEW_DATA_FLAG = "new_data_flag";

    public function setNewData($data) {
        $this->data[self::UPDATED_TIME] = time();
        if($this->data[self::NEW_DATA_FLAG] === true) return true;
        if($data[self::MODIFIED_COUNT] > 0) {
            $this->data[self::MODIFIED_TIME] = $data[self::MODIFIED_TIME];
            $this->data[self::NEW_DATA_FLAG] = true;
        }
        return $this->data[self::NEW_DATA_FLAG];
    }

    public function resetData() {
        $this->data[self::NEW_DATA_FLAG] = false;
    }

    public function getNewData() {
        return $this->data[self::MODIFIED_TIME];
    }

    public function initData($subscription) {
        $this->data = Array();
        $this->data[self::NEW_DATA_FLAG] = false;
        $timestamp = time();
        $this->data[self::MODIFIED_TIME] = $timestamp;
        $this->data[self::UPDATED_TIME] = $timestamp;

        $new_data = $this->searchNewData($subscription, $timestamp);
        $subscription->setNewData($new_data);
    }

    public function searchNewData($subscription, $time = NULL) {
        $saved_search = new Saved();
        $saved_search->getByID($subscription->fid);
        if(!$saved_search->id) return;
        if(is_null($time)) $timestamp = $this->data[self::UPDATED_TIME];
        else $timestamp = $time;
        $count = self::getModifiedCount($saved_search, $timestamp);

        return Array(self::MODIFIED_COUNT => $count, self::MODIFIED_TIME => $timestamp);
    }

    static public function getModifiedCount($saved_search, $timestamp) {
        $search_vars = $saved_search->searchVars();
        $lastmodified_filter = "v_lastmodified_epoch:>" . (string) $timestamp;
        if(isset($search_vars["filter"])) $search_vars["filter"][] = $lastmodified_filter;
        else $search_vars["filter"] = Array($lastmodified_filter);
        $search = new Search();
        $search->create($search_vars);
        $tmp = new Sources();
        $search->allSources = $tmp->getAllSources();
        $results = $search->doSearch(false);
        if(isset($results["total"])) $count = $results["total"];
        else $count = $results["count"];

        return $count;
    }
}

?>
