<?php

function resourceFieldAutocomplete($user, $api_key, $key, $val, $max){
    $exact_results = _search($key, $val, $max, true);
    $new_max = $max - count($exact_results);
    if($new_max > 0){
        $fuzzy_results = _search($key, $val, $new_max, false);
        $all_results = array_merge($exact_results, $fuzzy_results);
    }else{
        $all_results = $exact_results;
    }
    return APIReturnData::build($all_results, true);
}

function _search($key, $val, $max, $exact){
    $cxn = new Connection();
    $cxn->connect();

    if(!$exact) $search_string = '%'.$val.'%';
    else $search_string = $val;
    $results = $cxn->select("resource_columns", Array("rid"), "ssi", Array($key, $search_string, $max), "where name=? and value like ? group by rid limit ?");
    $reshaped_results = Array();
    foreach($results as $res){
        $resource = $cxn->select("resources", Array("*"), "i", Array($res['rid']), "where id=?");
        $resource_name = $cxn->select("resource_columns", Array("value"), "ii", Array($res["rid"], $resource[0]['version']), "where rid=? and version=? and name='Resource Name'");
        if(count($resource) == 0 || count($resource_name) == 0) throw new Exception("resource not found");
        $reshaped_result = Array(
            "rid" => $resource[0]['rid'],
            "original_id" => $resource[0]['original_id'],
            "name" => $resource_name[0]['value']
        );
        array_push($reshaped_results, $reshaped_result);
    }

    $cxn->close();
    return $reshaped_results;
}

?>
