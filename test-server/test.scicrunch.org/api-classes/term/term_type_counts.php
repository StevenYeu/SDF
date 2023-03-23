<?php

function getTermTypeCounts($user, $api_key){
    $counts = Term::getTermTypeCounts();

    $total = 0;
    foreach ($counts as $data){
        $total = $total + $data['count'];
    }

    $ratios = array();
    foreach ($counts as $data){
        $ratio = ($data['count']/$total) * 100;
        $data['percent'] = $ratio;
        $ratios[] = $data;
    }

    return $ratios;
}

?>