<?php

class SavedSearchSummaryData extends SubscriptionData {
    const COUNT_DATA = "count_data";
    const NEW_DATA_FLAG = "new_data_flag";
    const MODIFIED_NIFIDS = "modified_nifids";
    const MODIFIED_TIME = "modified_time";
    const UPDATED_TIME = "updated_time";
    const TIME_DIFF = 86400;    // subtract 24 hours for time difference

    public function setNewData($data) {
        // updated time is now
        $this->data[self::UPDATED_TIME] = time();

        if(!isset($this->data[self::MODIFIED_NIFIDS])) $this->data[self::MODIFIED_NIFIDS] = Array();

        // initialize modified nifids
        if($this->data[self::NEW_DATA_FLAG] === true) $modified_nifids = $this->data[self::MODIFIED_NIFIDS];
        else $modified_nifids = Array();

        // add new records with updated values
        foreach($data[self::COUNT_DATA] as $nifid => $count) {
            $modified_nifids[] = $nifid;
        }

        // set flags if there are new data, only change modified data if there is new modified data to change
        // modified nifids should never be empty after initial nifids found
        $modified_nifids = array_unique($modified_nifids);
        if(!empty($modified_nifids)) {
            if(!$this->data[self::NEW_DATA_FLAG]) {
                $this->data[self::MODIFIED_TIME] = $data[self::MODIFIED_TIME];
                $this->data[self::NEW_DATA_FLAG] = true;
            }
            $this->data[self::MODIFIED_NIFIDS] = $modified_nifids;
        }

        return $this->data[self::NEW_DATA_FLAG];
    }

    public function resetData() {
        $this->data[self::NEW_DATA_FLAG] = false;
    }

    public function getNewData() {
        return Array(self::MODIFIED_NIFIDS => $this->data[self::MODIFIED_NIFIDS], self::MODIFIED_TIME => ($this->data[self::MODIFIED_TIME] - self::TIME_DIFF));
    }

    public function initData($subscription) {
        $this->data = Array();
        $this->data[self::NEW_DATA_FLAG] = false;
        $timestamp = time();
        $this->data[self::MODIFIED_TIME] = $timestamp;
        $this->data[self::UPDATED_TIME] = $timestamp;

        $new_data = $this->searchNewData($subscription);
        $subscription->setNewData($new_data);
    }

    public function searchNewData($subscription, $time = NULL) {
        $saved_search = new Saved();
        $saved_search->getByID($subscription->fid);
        if(!$saved_search->id) return;
        $timestamp = is_null($time) ? $this->data[self::UPDATED_TIME] : $time;
        if(is_null($timestamp)) {
            $timestamp = time();
            $this->data[self::UPDATED_TIME] = $timestamp;
        }
        $nif_counts = self::getNifCounts($saved_search, $timestamp);

        return Array(self::COUNT_DATA => $nif_counts, self::MODIFIED_TIME => $this->data[self::UPDATED_TIME]);
    }

    static public function getNifCounts($saved_search, $time) {
        $search_vars = $saved_search->searchVars();
        $search = new Search();
        $search->create($search_vars);
        $tmp = new Sources();
        $search->allSources = $tmp->getAllSources();
        $results = $search->doSearch(false);
        $nif_counts = Array();
        $lastmodified_filter = "v_lastmodified_epoch:>" . (string) $time;
        foreach($results["sources"] as $source => $data) {
            if($data["count"] > 0) {
                $single_search_vars = $search_vars;
                $single_search_vars["nif"] = $source;
                if(isset($single_search_vars["filter"])) $new_search_vars["filter"][] = $lastmodified_filter;
                else $single_search_vars["filter"] = Array($lastmodified_filter);
                $search->create($single_search_vars);
                $results = $search->doSearch(false);
                if($results["total"] == 0) continue;
                $nif_counts[$source] = $results["total"];
            }
        }
        return $nif_counts;
    }

}

?>
