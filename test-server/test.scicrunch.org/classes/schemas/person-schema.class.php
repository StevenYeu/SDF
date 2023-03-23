<?php

class PersonSchema extends ThingSchema
{
    public $givenName;
    public $familyName;
    public $email;
    function __construct(){
        $this->type = "Person";
        $this->context = "http://schema.org";
    }

    function compile(){
        parent::compile();
        $givenName = $this->givenName;
        $familyName = $this->familyName;
        $email = $this->email;
        $this->data = array_merge(
            $this->data, compact("givenName", "familyName", "email")
        );
    }
}

?>
