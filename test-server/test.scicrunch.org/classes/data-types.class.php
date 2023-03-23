<?php

class DataTypes {
    public static function verifyMemDataByType($type, $data, $options=NULL) {
        switch($type) {
            case "text":
                return self::verifyMemDataText($data, $options);
            case "literature":
                return self::verifyMemDataLiterature($data, $options);
            default:
                return false;
        }
    }

    /****************************************************************************************************/

    private static function verifyMemDataText($data, $options) {
        if(gettype($data) !== "string") return false;

        $length = strlen($data);
        if(!is_null($options["min"]) && $length < $options["min"]) return false;
        if(!is_null($options["max"]) && $length > $options["max"]) return false;

        return true;
    }

    private static function verifyMemDataLiterature($data, $options) {
        if(!$data) {
            if($options["required"]) return false;
            return true;
        }

        return \helper\mentionIDFormat($data);
    }
}

?>
