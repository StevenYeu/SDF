<?php

class ThingSchema extends AbstractSchema
{
    public $name;
    public $alternateName;
    public $description;
    public $image;
    public $mainEntityOfPage;
    public $url;

    function __construct(){
        $this->type = "Thing";
        $this->context = "http://schema.org";
    }

    function compile(){
        parent::compile();
        $name = $this->name;
        $this->description = strip_tags($this->description);
        $this->description = str_replace(array('\r', '\n'), '', $this->description);
        if (!empty($this->description)) {
            $description = $this->description;
        }
        $alternateName = implode(', ', $this->alternateName);
        if (!empty($this->mainEntityOfPage)) {
            $mainEntityOfPage = $this->mainEntityOfPage;
        }
        if (!empty($this->image)) {
            $image = $this->image;
        }
        if (!empty($this->url)) {
            $url = $this->url;
        }
        $this->data = array_merge(
            $this->data, compact("name", "alternateName", "image", "description", "mainEntityOfPage", "url")
        );
    }
}

?>
