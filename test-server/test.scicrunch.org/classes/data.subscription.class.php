<?php

abstract class SubscriptionData {
    abstract public function setNewData($data);
    abstract public function resetData();
    abstract public function getNewData();
    abstract public function initData($subscription);

    protected $data;

    public function __construct($json_data){
        if(is_null($json_data)) $this->data = Array();
        $this->data = json_decode($json_data, true);
    }

    public function json(){
        if(is_null($this->data)) return $this->data;
        return json_encode($this->data);
    }

}

?>
