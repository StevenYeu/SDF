<?php

function termByAnnotationValues($user, $api_key, $search_term, $annotation_request_id, $annotation_ids, $annotation_labels, $type) {
    /* check args */
    $annotation_ids_count = count($annotation_ids);
    if(gettype($annotation_ids) != "array" || $annotation_ids_count > 5 || $annotation_ids_count < 1 || $annotation_ids_count !== count($annotation_labels)) return Array();

    /* make a map for ilx to label */
    $annotation_ids_map = Array();
    foreach($annotation_ids as $i => $ai) {
        $annotation_ids_map[$ai] = $annotation_labels[$i];
    }

    /* make the search query */
    if(!$search_term) $search_term = "";
    $search_term = "%".$search_term."%";
    if(!$type) $type = "cde";   // default to cde

    /* setup connection */
    $cxn = new Connection();
    $cxn->connect();

    /* get the request term database id */
    $request_db_id = $cxn->select("terms", Array("id"), "s", Array($annotation_request_id), "where ilx=?");
    if(empty($request_db_id)) {
        $cxn->close();
        return Array();
    }
    $request_db_id = $request_db_id[0]["id"];

    /* make the query for getting the annotation database ids */
    $types = str_repeat("s", $annotation_ids_count);
    $where_string = "where ilx in (" . implode(",", array_map(function($x) { return "?"; }, $annotation_ids)) . ")";

    /* get the annotation database ids */
    $termids_raw = $cxn->select("terms", Array("id", "ilx"), $types, $annotation_ids, $where_string);
    if(empty($termids_raw)) {
        $cxn->close();
        return Array();
    }

    /* format the the query for getting the terms */
    $termids = $termid_raw[0]["id"];
    $term_ids_vals = Array();
    foreach($termids_raw as $tr) {
        $term_ids_vals[] = Array("id" => $tr["id"], "val" => $annotation_ids_map[$tr["ilx"]]);
    }
    $types = str_repeat("is", count($term_ids_vals)) . "issi";
    $request_values = Array();
    foreach($term_ids_vals as $tiv) {
        $request_values[] = $tiv["id"];
        $request_values[] = $tiv["val"];
    }
    $request_values[] = count($term_ids_vals);
    $request_values[] = $search_term;
    $request_values[] = $type;
    $request_values[] = $request_db_id;
    $table_name =
        "term_annotations c inner join (select id from terms inner join (select distinct tid from term_annotations where(" .
        join(" or ", array_map(function($x) { return "(term_annotations.annotation_tid=? and term_annotations.value=?)"; }, $term_ids_vals)) .
        ") group by tid having count(*) = ?) a on terms.id=a.tid where terms.label like ? and terms.type=?) b on b.id=c.tid";

    /* get the terms with the matching annotation */
    $results = $cxn->select(
        $table_name,
        Array("c.value as value" , "count(*) as count"),
        $types,
        $request_values,
        "where c.annotation_tid=? group by c.value"
    );

    $cxn->close();

    return $results;
}

?>
