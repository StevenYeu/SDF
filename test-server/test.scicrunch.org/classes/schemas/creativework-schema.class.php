<?php

class CreativeWorkSchema extends ThingSchema
{
    public $headline;
    public $datePublished;
    public $authorSchema;
    public $aboutSchema;
    public $award;
    public $keywords;
    public $sponsorSchema;
    public $sourceOrganizationSchema;
    public $providerSchema;
    public $isPartOfSchema;
    public $hasPartSchema;
    public $mentionsSchema;
    function __construct(){
        $this->type = "CreativeWork";
        $this->context = "http://schema.org";
        $this->authorSchema = array();
        $this->aboutSchema = array();
        $this->isPartOfSchema = array();
        $this->hasPartSchema = array();
        $this->mentionsSchema = array();
    }

    function compile(){
        parent::compile();
        if (!empty($this->headline)) {
            $headline = $this->headline;
        }
        if (!empty($this->keywords)) {
            $keywords = $this->keywords;
        }
        if (!empty($this->datePublished) && $this->datePublished instanceof DateTime) {
            $datePublished = $this->datePublished->format('Y-m-d');
        }
        if (!empty($this->aboutSchema)) {
            $about = array();
            if (!is_array($this->aboutSchema)) {
                $this->aboutSchema = array($this->aboutSchema);
            }
            foreach ($this->aboutSchema as $about_i) {
                if ($about_i instanceof AbstractSchema) {
                    $about[] = $about_i->getData();
                }
            }
        }
        if (!empty($this->authorSchema)) {
            $author = array();
            if (!is_array($this->authorSchema)) {
                $this->authorSchema = array($this->authorSchema);
            }
            foreach ($this->authorSchema as $author_i) {
                if ($author_i instanceof AbstractSchema) {
                    $author[] = $author_i->getData();
                }
            }
        }
        if (!empty($this->award)) {
            $award = $this->award;
        }
        if (!empty($this->isPartOfSchema)) {
            $isPartOf = array();
            if (!is_array($this->isPartOfSchema)) {
                $this->isPartOfSchema = array($this->isPartOfSchema);
            }
            foreach ($this->isPartOfSchema as $creativeWork_i) {
                if ($creativeWork_i instanceof AbstractSchema) {
                    $isPartOf[] = $creativeWork_i->getData();
                }
            }
        }
        if (!empty($this->hasPartSchema)) {
            $hasPart = array();
            if (!is_array($this->hasPartSchema)) {
                $this->hasPartSchema = array($this->hasPartSchema);
            }
            foreach ($this->hasPartSchema as $creativeWork_i) {
                if ($creativeWork_i instanceof AbstractSchema) {
                    $hasPart[] = $creativeWork_i->getData();
                }
            }
        }
        if (!empty($this->mentionsSchema)) {
            $mentions = array();
            if (!is_array($this->mentionsSchema)) {
                $this->mentionsSchema = array($this->mentionsSchema);
            }
            foreach ($this->mentionsSchema as $thing_i) {
                if ($thing_i instanceof AbstractSchema) {
                    $mentions[] = $thing_i->getData();
                }
            }
        }
        if (!empty($this->sponsorSchema)) {
            $sponsor = array();
            if (!is_array($this->sponsorSchema)) {
                $this->sponsorSchema = array($this->sponsorSchema);
            }
            foreach ($this->sponsorSchema as $sponsorSchema_i) {
                if ($sponsorSchema_i instanceof AbstractSchema){
                    $sponsor[] = $sponsorSchema_i->getData();
                }
            }
        }
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
            $this->data, compact("headline", "datePublished", "author",
            "about", "keywords", "award", "sponsor" , "sourceOrganization",
            "isPartOf", "hasPart", "mentions")
        );
    }
}

?>
