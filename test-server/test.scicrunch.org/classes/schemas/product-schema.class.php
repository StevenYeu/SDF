
<?php

class ProductSchema extends ThingSchema
{
    public $additionalPropertySchema;
    public $manufacturerSchema;

    function __construct(){
        $this->type = "Product";
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
