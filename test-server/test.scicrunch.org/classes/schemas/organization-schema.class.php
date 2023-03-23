<?php

class OrganizationSchema extends ThingSchema
{
    public $sourceOrganizationSchema;

    function __construct(){
        $this->type = "Organization";
        $this->context = "http://schema.org";
    }

    function compile(){
        parent::compile();
        if (!empty($this->sourceOrganizationSchema)) {
            $sourceOrganization = array();
            if (!is_array($this->sourceOrganizationSchema)) {
                $this->sourceOrganizationSchema = array($this->sourceOrganizationSchema);
            }
            foreach ($this->sourceOrganizationSchema as $sourceOrganizationSchema_i) {
                if ($sourceOrganizationSchema_i instanceof AbstractSchema) {
                    $sourceOrganization[] = $sourceOrganizationSchema_i->getData();
                }
            }
        }
        $this->data = array_merge(
            $this->data, compact("sourceOrganization")
        );
    }
}

?>
