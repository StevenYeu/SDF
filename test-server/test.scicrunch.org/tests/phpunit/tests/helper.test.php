<?php

use PHPUnit\Framework\TestCase;
require_once __DIR__ . "/base.php";

class HelperTest extends TestCase {
    public function testDerefArray() {
        $arr1 = Array(
            "one" => Array(
                "two" => Array(
                    "three" => "four",
                    "five" => "six",
                )
            ),
            "seven" => Array(
                "eight" => "nine",
            ),
        );
        $this->assertEquals("four", \helper\derefArray($arr1, "one.two.three"));
        $this->assertEquals("nine", \helper\derefArray($arr1, Array("seven", "eight")));

        $arr2 = Array(
            "one" => Array(
                Array(
                    "two" => "three",
                ),
                Array(
                    "two" => "four",
                ),
                Array(
                    "two" => "five",
                ),
            ),
        );
        $this->assertEquals(Array("three", "four", "five"), \helper\derefArray($arr2, "one.[].two"));

        $arr3 = Array(
            "one" => Array(
                Array(
                    "two" => Array(
                        "three" => Array(
                            Array(
                                "four" => "five",
                            ),
                        ),
                    ),
                ),
                Array(
                    "two" => Array(
                        "three" => Array(
                            Array(
                                "four" => "six",
                            ),
                        ),
                    ),
                ),
                Array(
                    "two" => Array(
                        "three" => Array(
                            Array(
                                "four" => "seven",
                            ),
                            Array(
                                "four" => "eight",
                            ),
                        ),
                    ),
                ),
            ),
        );
        $this->assertEquals(Array("five", "six", "seven", "eight"), \helper\derefArray($arr3, "one.[].two.three.[].four"));

        $arr4 = Array(
            Array("one" => "two"),
            Array("one" => "three"),
            Array("one" => "four"),
            Array("one" => "five"),
            Array("one" => "six"),
            Array("one" => "seven"),
        );
        $this->assertEquals(Array("two", "three", "four", "five", "six", "seven"), \helper\derefArray($arr4, "[].one"));
    }
}

?>
