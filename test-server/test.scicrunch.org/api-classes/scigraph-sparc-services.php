<?php


function getAnnotationEntitiesTSV($terms) {
    if(!is_array($terms)) {
        return "";
    }

    $term_rows = Array();
    foreach($terms as $term) {
        $term_row = Array($term);
        $url = SPARCAPIURL . "/scigraph/annotations/entities";
        $term_fmt = str_replace(" ", "+", $term);
        $jresults = \helper\sendGetRequest($url, Array("content" => $term_fmt));
        $results = json_decode($jresults, true);
        foreach($results as $res) {
            $resid = $res["token"]["id"];
            foreach($res["token"]["terms"] as $found_term) {
                $term_row[] = $found_term;
                $term_row[] = $resid;
            }
        }
        $term_rows[] = implode("\t", $term_row);
    }

    return implode("\n", $term_rows);
}

?>
