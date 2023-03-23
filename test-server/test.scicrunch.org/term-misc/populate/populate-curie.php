<?php
$lines = file("./data/curie.yaml");

foreach ($lines as $line_num => $line) {
    if (preg_match('/^#/', $line, $matches)){
       continue;
    }
    if (preg_match('/:/', $line, $matches)){
      $l = preg_replace("/'/", "", $line);
      $fields = explode(": ", $l);
      $prefix = trim($fields[0], "'");
      $ns = trim($fields[1], "'\n");
      $iri = $ns;
      //echo $prefix . " " . $ns . "\n\n";

      $query = "INSERT INTO curie_catalog (user_id, prefix, namespace, iri) VALUES
             (0, \"$prefix\", \"$ns\", \"$iri\");";
      print $query . "\n"; 
    }
}

?>
