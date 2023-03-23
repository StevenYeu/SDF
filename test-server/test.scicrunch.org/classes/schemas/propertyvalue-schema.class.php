<?php

class PropertyValueSchema extends StructuredValueSchema
{
    public $propertyID;
    public $value;
    function __construct(){
        $this->type = "PropertyValue";
        $this->context = "http://schema.org";
    }

    function compile(){
        parent::compile();
        if (!empty($this->value)) {
            $value = $this->value;
        }
        if (!empty($this->propertyID)) {
            $propertyID = $this->propertyID;
        }
        $this->data = array_merge($this->data, compact('value', 'propertyID'));
    }
}

?>
