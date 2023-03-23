<?php

class DataFeedSchema extends DatasetSchema
{
    public $dataFeedElementSchema;
    function __construct(){
        $this->type = "DataFeed";
        $this->context = "http://schema.org";
    }

    function compile(){
        parent::compile();
        if (!empty($this->dataFeedElementSchema)) {
            $dataFeedElement = array();
            if (!is_array($this->dataFeedElementSchema)) {
                $this->dataFeedElementSchema = array($this->dataFeedElementSchema);
            }
            foreach ($this->dataFeedElementSchema as $dataFeedItem_i) {
                if ($dataFeedItem_i instanceof ThingSchema) {
                    $dataFeedElement[] = $dataFeedItem_i->getData();
                }
            }
        }
        $this->data = array_merge(
            $this->data
            ,
            compact('dataFeedElement')
        );
    }
}

?>
