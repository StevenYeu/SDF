<?php

header("Content-Type: application/csv");
header("Content-Disposition: attachment; filename=template-resource-mentions.csv");    

$output = fopen("php://output", "w");
$header_line = Array("Resource ID", "Publication ID", "Snippet", "Verified", "Upload");
fputcsv($output, $header_line);
fclose($output);

?>
