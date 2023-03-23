<?php

function getTermCurieCounts($user, $api_key, $type){
    $dbObj = new DbObj();

    if (isset($type)){
        # $total = Term::getTermCount($dbObj, $type);
        $external_curies = TermExistingId::getExternalCurieCountsByType($dbObj, $type);
    } else {
        # $total = Term::getTermCount($dbObj);
        $external_curies = TermExistingId::getExternalCurieCounts($dbObj);
    }
    $prefix2name = CurieCatalog::getPrefixToName($dbObj);
    //print_r($prefix2name);

    $total_curies_count = $external_curies['count'];
    //$other_count = $total - $curies_count;
    # $other_count = $curies_count - $total;

    $ratios = array();
    foreach ($external_curies as $name => $ar){
        if ($name == 'count') { continue; }
        if ($ar == 0) { continue; }
        $data = array();
        $data['name'] = $name;
        $data['prefix'] = $name;
        $data['percent'] = ($ar/$total_curies_count) * 100;
        $data['count'] = $ar;
        // $data['name'] = $ar['prefix']
        // $data['name'] = $prefix2name[$name];
        $ratios[] = $data;
    }
    # $other_ratio = ($other_count/$total) * 100;
    # $other_ratio = ($other_count/$curies_count) * 100;
    # $ratios[] = array("prefix"=>"Other", "percent"=>$other_ratio, "count"=>$other_count,"name"=>"Other");
    # $ratios[] = array("name"=>"Total", "count"=>$total);

    return $ratios;
}

?>
