<?php

function getMappings($user, $api_key, $term_id, $from=0, $size=1, $curation_status){
    $dbObj = new DbObj();
    $tm = new TermMapping($dbObj);
    return $tm->getByTid($term_id, $from, $size, $curation_status);
}

?>
