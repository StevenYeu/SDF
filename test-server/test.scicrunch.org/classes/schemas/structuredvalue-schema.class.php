<?php

class StructuredValueSchema extends IntangibleSchema
{
    function __construct(){
        $this->type = "StructuredValue";
        $this->context = "http://schema.org";
    }

    function compile(){
        parent::compile();
        $this->data = array_merge($this->data);
    }
}

?>
