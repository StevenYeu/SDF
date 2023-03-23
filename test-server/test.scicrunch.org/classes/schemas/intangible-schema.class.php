<?php

class IntangibleSchema extends ThingSchema
{
    function __construct(){
        $this->type = "Intangible";
        $this->context = "http://schema.org";
    }

    function compile(){
        parent::compile();
        $this->data = array_merge($this->data);
    }
}

?>
