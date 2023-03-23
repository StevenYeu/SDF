<?php

class DataFeedItemSchema extends IntangibleSchema
{
    public $dateModified;
    public $dateCreated;
    public $dateDeleted;
    public $itemSchema;
    function __construct(){
        $this->type = "DataFeedItem";
        $this->context = "http://schema.org";
        $itemSchema = array();
    }

    function compile(){
        parent::compile();
        if (!empty($this->dateModified) && $this->dateModified instanceof DateTime) {
            $dateModified = $this->dateModified->format('Y-m-d');
        }
        if (!empty($this->dateCreated) && $this->dateCreated instanceof DateTime) {
            $dateCreated = $this->dateCreated->format('Y-m-d');
        }
        if (!empty($this->dateDeleted) && $this->dateDeleted instanceof DateTime) {
            $dateDeleted = $this->dateDeleted->format('Y-m-d');
        }
        if (!empty($this->itemSchema)) {
            $item = array();
            if (!is_array($this->itemSchema)) {
                $this->itemSchema = array($this->itemSchema);
            }
            foreach ($this->itemSchema as $itemSchema_i) {
                if ($itemSchema_i instanceof AbstractSchema) {
                    $item[] = $itemSchema_i->getData();
                }
            }
        }
        $this->data = array_merge(
            $this->data
            ,
            compact(
                'dateCreated',
                'dateDeleted',
                'dateModified',
                'item'
            )
        );
    }
}

?>
