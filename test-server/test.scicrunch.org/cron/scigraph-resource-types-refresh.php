<?php

$docroot = "..";
require_once __DIR__ . "/../classes/classes.php";

$ttl = file_get_contents("https://raw.githubusercontent.com/SciCrunch/NIF-Ontology/uri-switch/ttl/resources.ttl");
$lines = explode("\n", $ttl);

$types = Array();
$current_id = NULL;
foreach($lines as $line) {
    $s = trim($line);
    if(\helper\endsWith($s, " a owl:Class ;")) {
        $current_id = str_replace(" a owl:Class ;", "", $s);
        $types[$current_id] = Array();
    } elseif(!is_null($current_id) && \helper\startsWith($s, "rdfs:label")) {
        $found = preg_match('/rdfs:label "(.+)"/', $s, $matches);
        if($found) {
            $types[$current_id]["label"] = $matches[1];
        }
    } elseif(!is_null($current_id) && \helper\startsWith($s, "rdfs:subClassOf")) {
        $found = preg_match('/rdfs:subClassOf (.+) \./', $s, $matches);
        if($found) {
            $types[$current_id]["parent"] = $matches[1];
        }
    }
}

$types_file = __DIR__ . "/../vars/resource-types.php";
file_put_contents($types_file, serialize($types));

?>
