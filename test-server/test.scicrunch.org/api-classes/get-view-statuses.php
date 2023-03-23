<?php

function getTableInfo() {

    $sources = new Sources();
    $sources = $sources->getAllSources();

    $all_views = Array();

    $cxn = new Connection();
    $cxn->connect();

    $view_ids = $cxn->select("viewstatuses", Array("distinct nif_id"), "", Array(), "");
    $max_date = $cxn->select("viewstatuses", array("max(date_checked) as max_date"),"", array(), "");

    foreach($view_ids as $view_id) {
        $view_data = $cxn->select(
            "viewstatuses",
            Array("*"),
            "s",
            Array($view_id["nif_id"]),
            "where nif_id=? order by id desc limit 2"
        );

        $date_added = $cxn->select(
            "viewstatuses",
            Array("date_checked"),
            "s",
            Array($view_id["nif_id"]),
            "where nif_id=? limit 1"
        );

        /* get the count difference */
        if(count($view_data) > 1) {
            $view_data[0]["count_diff"] = $view_data[0]["record_count"] - $view_data[1]["record_count"];
        } else {
            $view_data[0]["count_diff"] = 0;
        }
        $count_diff = $view_data[0]["count_diff"];
        $view_data[0]["good_count_diff"] = true;
        if($count_diff !== 0 && ($count_diff < -100 || ((float) $count_diff) / $view_data[0]["record_count"] < -0.1)) {
            $view_data[0]["good_count_diff"] = false;
        }

        /* get the rest of the data */
        $date_checked = $view_data[0]['date_checked'];
        $production_date = $view_data[0]['production_date'];
        $diff = abs($date_checked - $production_date);
        $days = floor($diff / (60*60*24) );

        if ($days > 31) {
            $view_data[0]["exceeds_month"] = "0";
        } else {
            $view_data[0]["exceeds_month"] = "1";
        }

        if ($view_data[0]['date_checked'] == $max_date[0]["max_date"]) {
            $view_data[0]["in_production"] = "1";
        } else {
            $view_data[0]["in_production"] = "0";
        }

        if ($view_data[0]["insc"] && $view_data[0]["indisco"] && $view_data[0]["sc_num_results"] && $view_data[0]["sc_page_loads"] && $view_data[0]["sc_title"] && $view_data[0]["sc_descr"] && $view_data[0]["sc_has_data"] && $view_data[0]["sc_valid_link"] && $view_data[0]["sc_descr_link"] && $view_data[0]["good_count_diff"]) {
            $view_data[0]["mark"] = "1";
        } else {
            $view_data[0]["mark"] = "0";
        }

        if ($view_data[0]['sc_num_results']) {
            $view_data[0]["final_count"] = $view_data[0]['sc_num_results'];
        } else {
            $view_data[0]["final_count"] = $view_data[0]['record_count'];
        }

        $view_data[0]['date_checked'] = date('m/d/y', $view_data[0]['date_checked']);
        $view_data[0]['production_date'] = date('m/d/y', $view_data[0]['production_date']);
        $view_data[0]['curr_date'] = date('m/d/y', $view_data[0]['curr_date']);
        $view_data[0]['date_added'] = date('m/d/y', $date_added[0]["date_checked"]);

        if(isset($sources[$view_data[0]["nif_id"]])) {
            $view_data[0]["view_name"] = $sources[$view_data[0]["nif_id"]]->getTitle();
        } else {
            $view_data[0]["view_name"] = "";
        }

        $all_views[] = $view_data[0];
    }

    $cxn->close();

    return $all_views;

}

// convert sql table to json and add item exceeds month to array
function getNifIdInfo($content) {
    $view_id_arr = array();

    foreach($content as $key => $value) {
        $view_id_arr[$value['nif_id']] = $content[$key];
    }
    return $view_id_arr;
}

?>
