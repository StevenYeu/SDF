<?php

class MongoDataset {
    const INFO_COLLECTION_NAME = "info";

    private function __construct() { }

    private static function connectOptions() {
        return Array(
            "uri" => "mongodb://" . $GLOBALS["config"]["mongo-datasets"]["user"] . ":" . $GLOBALS["config"]["mongo-datasets"]["password"] . "@" .  $GLOBALS["config"]["mongo-datasets"]["host"],
            "uri-options" => Array("ssl" => true, "replicaSet" => $GLOBALS["config"]["mongo-datasets"]["replica-set"], "authSource" => "admin"),
        );
    }

    private static function generate($database, $collection) {
        $options = self::connectOptions();
        return (new MongoDB\Client($options["uri"], $options["uri-options"]))->{$database}->{$collection};
    }

    public static function generateDataset($collection) {
        return self::generate($GLOBALS["config"]["mongo-datasets"]["database"], $collection);
    }

    public static function generateInfoCollection() {
        return self::generate($GLOBALS["config"]["mongo-datasets"]["database"], self::INFO_COLLECTION_NAME);
    }

    public static function generateDatasetDatabase() {
        $options = self::connectOptions();
        return (new MongoDB\Client($options["uri"], $options["uri-options"]))->{$GLOBALS["config"]["mongo-datasets"]["database"]};
    }
}

?>
