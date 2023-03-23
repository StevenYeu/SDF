<?php

class SavedSearchLiteratureData extends SubscriptionData {
    const OLD_COUNT = "old_count";
    const NEW_COUNT = "new_count";
    const NEW_DATA_FLAG = "new_data_flag";

    public function setNewData($data) {
        if(!isset($this->data[self::OLD_COUNT])) $this->data[self::OLD_COUNT] = $data[self::NEW_COUNT];
        $this->data[self::NEW_COUNT] = $data[self::NEW_COUNT];
        if($this->data[self::NEW_COUNT] !== $this->data[self::OLD_COUNT]) $this->data[self::NEW_DATA_FLAG] = true;
        return $this->data[self::NEW_DATA_FLAG];
    }

    public function resetData() {
        $this->data[self::OLD_COUNT] = $this->data[self::NEW_COUNT];
        $this->data[self::NEW_DATA_FLAG] = false;
    }

    public function getNewData() {
        return $this->data[self::NEW_COUNT];
    }

    public function initData($subscription) {
        $this->data = Array();
        $this->data[self::NEW_DATA_FLAG] = false;
        $new_data = $this->searchNewData($subscription);
        $subscription->setNewData($new_data);
    }

    public function searchNewData($subscription) {
        $saved_search = new Saved();
        $saved_search->getByID($subscription->fid);
        if(!$saved_search->id) return;
        $count = self::getLiteratureCount($saved_search);

        return Array(self::NEW_COUNT => $count);
    }

    static public function getLiteratureCount($saved_search) {
        $search_vars = $saved_search->searchVars();
        $search = new Search();
        $search->create($search_vars);
        $tmp = new Sources();
        $search->allSoures = $tmp->getAllSources();
        $results = $search->doSearch(false);
        if(isset($results["total"])) $count = $results["total"];
        else $count = $results["count"];

        return $count;
    }

}

?>
