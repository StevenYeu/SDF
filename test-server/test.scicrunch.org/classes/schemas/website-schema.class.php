<?php

class WebsiteSchema extends CreativeWorkSchema
{
    function __construct(){
        $this->type = "Website";
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
