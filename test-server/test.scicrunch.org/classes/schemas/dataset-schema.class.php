<?php

class DatasetSchema extends CreativeWorkSchema
{
    function __construct(){
        $this->type = "Dataset";
        $this->context = "http://schema.org";
    }

    function compile(){
        parent::compile();
        $this->data = array_merge(
            $this->data
            //,
        );
    }
}

?>
