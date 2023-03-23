<?php

class SoftwareApplicationSchema extends CreativeWorkSchema
{
    function __construct(){
        $this->type = "SoftwareApplication";
        $this->context = "http://schema.org";
    }

    function compile(){
        parent::compile();
        // $this->data = array_merge(
            // $this->data, 
            // compact(
            // )
        // );
    }
}

?>
