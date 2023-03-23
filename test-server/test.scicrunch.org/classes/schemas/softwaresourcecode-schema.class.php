<?php

class SoftwareSourceCodeSchema extends CreativeWorkSchema
{
    function __construct(){
        $this->type = "SoftwareSourceCode";
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
