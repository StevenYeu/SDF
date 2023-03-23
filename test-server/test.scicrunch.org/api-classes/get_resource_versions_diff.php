<?php

function getResourceVersionDiff($user, $api_key, $scrid, $version1, $version2){
    $rid = \helper\getIDFromRID($scrid);
    $resource1 = new Resource();
    $resource1->getByID($rid);
    if(!$resource1->id) return APIReturnData::build(NULL, false, 400, "invalid id");
    $resource2 = new Resource();
    $resource2->getByID($rid);

    $resource1->version = $version1;
    $resource1->getColumns();
    $ver1_cols = $resource1->columns;
    $resource2->version = $version2;
    $resource2->getColumns();
    $ver2_cols = $resource2->columns;

    $diffs = diffCols($ver1_cols, $ver2_cols);
    return APIReturnData::build($diffs, true);
}

function diffCols($v1_cols, $v2_cols){
    $v1_keys = Array();
    $v2_keys = Array();
    $skip_cols = Array("original_id", "rid");
    foreach($v1_cols as $col => $val){
        if(in_array($col, $skip_cols)) continue;
        if($val != "") $v1_keys[] = $col;
    }
    foreach($v2_cols as $col => $val){
        if(in_array($col, $skip_cols)) continue;
        if($val != "") $v2_keys[] = $col;
    }

    $in_v1 = array_values(array_diff($v1_keys, $v2_keys));
    $in_v2 = array_values(array_diff($v2_keys, $v1_keys));
    $mod = Array();
    foreach($v1_cols as $col => $val){
        if(isset($v2_cols[$col]) && $v2_cols[$col] != "" && $val != ""){
            if($val !== $v2_cols[$col]){
                $mod[] = $col;
            }
        }
    }
    $diffs = Array("in_version1" => $in_v1, "in_version2" => $in_v2, "modified" => $mod);
    return $diffs;
}

?>
