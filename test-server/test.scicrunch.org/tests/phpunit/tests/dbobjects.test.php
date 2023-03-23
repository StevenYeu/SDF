<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . "/base.php";

class DBObjectsTest extends TestCase {
    public static $classes = Array();

    public function testClasses() {
        foreach(array_keys($GLOBALS["class-map"]) as $class) {
            if($class == "DBObject3" || !is_subclass_of($class, "DBObject3")) {
                continue;
            }
            $test_result = $class::runTests();
            $this->assertTrue($test_result->success, $test_result->fullMessage());
        }
    }
}

?>
