<?php

/**
 * Base class for all schema classes
 * @author Yifei Zhang
 */
abstract class AbstractSchema
{
    protected $context = "http://schema.org";
    protected $type = "";
    protected $data;
    public $id;

    function generateJSON($hex=false) {
        if($hex) {
            return json_encode($this->getData(), JSON_UNESCAPED_SLASHES | JSON_HEX_TAG);
        } else {
            return json_encode($this->getData(), JSON_UNESCAPED_SLASHES);
        }
    }

    function compile(){
        $context = $this->context;
        $type = $this->type;
        $data = $this->data;
        $id = $this->id;
        $this->data = array("@context" => $context, "@type" => $type);
        if (!empty($id)) {
            $this->data["@id"] = $id;
        }
    }

    function getData(){
        $this->compile();
        return array_filter($this->data, 'AbstractSchema::is_not_null');
    }

    static function is_not_null($val){
        return !is_null($val);
    }

    public static function buildPMIDURL($protocol, $pmid){
        return "$protocol://$_SERVER[HTTP_HOST]/".$pmid;
    }

    public static function buildReferenceSchema($url){
        $reference = new IDSchema();
        $reference->id = $url;
        return $reference;
    }

    public static function buildResourceURL($protocol, $rid){
        return "$protocol://$_SERVER[HTTP_HOST]/resolver/".$rid;
    }

    public static function buildSourceTableView($protocol, $portal, $nlxView){
        if (empty($portal)) {
            $portal = 'scicrunch';
        }
        return "$protocol://$_SERVER[HTTP_HOST]/$portal/data/source/$nlxView/search?q=%2A&l=";
    }

    public static function buildSourceTableViewQuery($protocol, $portal, $nlxView, $query){
        if (empty($portal)) {
            $portal = 'scicrunch';
        }
        return "$protocol://$_SERVER[HTTP_HOST]/$portal/data/source/$nlxView/search?q=$query";
    }
}

?>
