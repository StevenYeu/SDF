<?php

class ScholarlyArticleSchema extends CreativeWorkSchema
{
    public $pageStart;
    public $pageEnd;
    function __construct(){
        $this->type = "ScholarlyArticle";
        $this->context = "http://schema.org";
    }

    function compile(){
        parent::compile();
        if (!empty($this->pageStart))
        {
            $pageStart = $this->pageStart;
        }
        if (!empty($this->pageEnd))
        {
            $pageEnd = $this->pageEnd;
        }
        $this->data = array_merge(
            $this->data, compact("pageStart", "pageEnd")
        );
    }
}

?>
