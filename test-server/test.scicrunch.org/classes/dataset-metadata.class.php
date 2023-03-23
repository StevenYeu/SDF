<?php

class DatasetMetadata extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "dataset_metadata";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                    => self::fieldDef("id", "i", true),
            "datasetid"             => self::fieldDef("datasetid", "i", true),
            "dataset_metadata_id"   => self::fieldDef("dataset_metadata_id", "i", true),
            "val"                   => self::fieldDef("val", "s", false),
        );
    }
    protected function _set_val($val) {
        $metadata_field_id = $this->dataset_metadata_id;
        $mdf = DatasetMetadataField::loadBy(Array("id"), Array($this->dataset_metadata_id));
        if(is_null($mdf)) return false;
        $filter_val = $mdf->filterVal($val);
        if(is_null($filter_val) && $mdf->required === 1) return false;
        return $filter_val;
    }

    public static function createNewObj(Dataset $dataset, DatasetMetadataField $dataset_metadata, $val) {
        if(!$dataset->id || !$dataset_metadata->id) return NULL;

        $obj = self::insertObj(Array(
            "id" => NULL,
            "datasetid" => $dataset->id,
            "dataset_metadata_id" => $dataset_metadata->id,
            "val" => $val,
        ));

        return $obj;
    }

    public static function deleteObj($obj) {
        parent::deleteObj($obj);
    }

    private static function checkExists($dataset, DatasetMetadataField $dataset_metadata) {
        $cxn = new Connection();
        $cxn->connect();
        $count = $cxn->select(self::$_table_name, Array("count(*)"), "ii", Array($dataset->id, $dataset_metadata->id), "where datasetid=? and dataset_metadata_id=?");
        $cxn->close();
        if($count[0]["count(*)"] > 0) return true;
        return false;
    }

    public static function addMetadata($metadata, $dataset) {
        $vals = Array();
        $metadata_fields = DatasetMetadataField::loadArrayBy(Array("labid"), Array($dataset->lab()->id));
        foreach($metadata_fields as $mdf) {
            if($mdf->required === 1 && (is_null($metadata) || !isset($metadata[$mdf->name]))) return false;
            $val = $mdf->filterVal($metadata[$mdf->name]);
            if(is_null($val) && $mdf->required === 1) return false;
            $vals[$mdf->name] = Array("val" => $val, "metadata_field" => $mdf);
        }
        foreach($vals as $val) {
            $new_record = DatasetMetadata::createNewObj($dataset, $val["metadata_field"], $val["val"]);
            if(is_null($new_record)) return false;
        }
        return true;
    }
}
DatasetMetadata::init();

?>
