<?php

class LiteratureSearchSchema extends AbstractSchema {
    public function __construct($url) {
        $this->id = $url;
        $this->type = "CreativeWork";
        $this->context = "http://schema.org";
    }

    public function compile() {
        parent::compile();
        $this->data["@id"] = $this->id;
        $this->data["@type"] = $this->type;
    }
}

?>
