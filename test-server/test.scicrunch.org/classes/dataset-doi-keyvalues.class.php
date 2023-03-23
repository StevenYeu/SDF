<?php
class DatasetDoiKeyValues extends DBObject3 {
    protected static $_fields_definitions = NULL;
    protected static $_table_name = "dataset_doi_keyvalues";
    protected static $_primary_key_field = "id";

    public static function init() {
        self::$_fields_definitions = Array(
            "id"                            => self::fieldDef("id", "i", true),
            "dataset_id"                    => self::fieldDef("dataset_id", "i", true),
            "text"                          => self::fieldDef("text", "s", false),
            "type"                          => self::fieldDef("type", "s", false),
            "subtype"                       => self::fieldDef("subtype", "s", false),
            "position"                      => self::fieldDef("position", "i", false),
        );
    }

   // private $_user;


    public function arrayForm() {
        return Array(
            "id" => $this->id,
            "dataset_id" => $this->dataset_id,
            "text" => $this->text,
            "type" => $this->type,
            "subtype" => $this->subtype,
            "position" => $this->position,
        );
    }

    public static function createNewObj(Dataset $dataset, $dataset_id, $text, $type, $subtype, $position) {
        // can't use the next_function method since a set of keyvalues will have same position
//        $next_position = count(self::loadArrayBy(Array("dataset_id", "type"), Array($dataset_id, "contributors_name")));

        $obj = self::insertObj(Array(
            "id" => NULL,
            "dataset_id" => $dataset_id,
            "text" => $text,
            "type" => $type,
            "subtype" => $subtype,
            "position" => $position,
        ));
        return $obj;
    }


    public static function deleteObj($obj) {
        parent::deleteObj($obj);
    }

    public static function authorsOnly($field, $value) {
        $thedatasetid = $value[0];
        $thetype = $value[1];

        $cxn = new Connection();
        $cxn->connect();

        $results = $cxn->select("dataset_doi_keyvalues", Array("text", "subtype", "position"), "isis", Array($thedatasetid, $thetype, $thedatasetid, $thetype), "where dataset_id=? AND type=? AND 
            subtype IN ('name', 'firstname', 'lastname', 'initials', 'middleinitial', 'contact', 'email') AND
            position IN (
                select position
                from dataset_doi_keyvalues
                where dataset_id = ?
                    AND type=?
                    AND subtype='author'
                    AND text = 1)
                ORDER BY position
            ");
        $cxn->close();
        
        if ($results)
            return $results;
        else
            return false; 
    }

/*
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
*/
}
DatasetDoiKeyValues::init();

?>
