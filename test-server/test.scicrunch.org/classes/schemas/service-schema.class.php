<?php

class ServiceSchema extends IntangibleSchema
{
    public $providerSchema;
    function __construct(){
        $this->type = "Service";
        $this->context = "http://schema.org";
    }

    function compile(){
        parent::compile();
        if (!empty($this->providerSchema)) {
            $provider = array();
            if (!is_array($this->providerSchema)) {
                $this->providerSchema = array($this->providerSchema);
            }
            foreach ($this->providerSchema as $providerSchema_i) {
                if ($providerSchema_i instanceof AbstractSchema) {
                    $provider[] = $providerSchema_i->getData();
                }
            }
        }
        $this->data = array_merge(
            $this->data, compact("provider")
        );
    }

}

?>
