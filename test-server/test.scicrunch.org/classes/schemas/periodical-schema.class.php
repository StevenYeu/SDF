<?php

class PeriodicalSchema extends CreativeWorkSchema
{
    public $issn;
    function __construct(){
        $this->type = "Periodical";
        $this->context = "http://schema.org";
    }

    function compile(){
        parent::compile();
        if (!empty($this->issn)) {
            $issn = $this->issn;
        }
        $this->data = array_merge(
            $this->data, compact("issn")
        );
    }
}

?>
