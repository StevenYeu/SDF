<?php

function termByAnnotation($user, $api_key, $search_term, $annotation_ids, $annotation_labels, $type, $count, $offset) {
    /* check args */
    $annotation_ids_count = count($annotation_ids);
    if($annotation_ids_count == 0) return normalSearch($search_term, $type, $count, $offset);
    if(gettype($annotation_ids) != "array" || $annotation_ids_count > 5 || $annotation_ids_count < 1 || $annotation_ids_count !== count($annotation_labels)) return Array();
    if(!($count <= 100 && $count > 0)) $count = 10;
    if($offset < 0) $offset = 0;

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
    $types = str_repeat("is", count($term_ids_vals)) . "isssii";
    $request_values = Array();
    foreach($term_ids_vals as $tiv) {
        $request_values[] = $tiv["id"];
        $request_values[] = $tiv["val"];
    }
    $request_values[] = count($term_ids_vals);
    $request_values[] = $search_term;
    $request_values[] = $search_term;
    $request_values[] = $type;
    $request_values[] = $offset;
    $request_values[] = $count;
    $table_name =
        "terms inner join (select distinct tid from term_annotations where(" .
        join(" or ", array_map(function($x) { return "(term_annotations.annotation_tid=? and term_annotations.value=?)"; }, $term_ids_vals)) .
        ") group by tid having count(*) = ?) a on terms.id=a.tid";

    /* get the terms with the matching annotation */
    $results = $cxn->select(
        $table_name,
        Array("terms.*"),
        $types,
        $request_values,
        "where (terms.label like ? or terms.ilx like ?) and terms.type=? limit ?,?"
    );
    $cxn->close();

    /* convert to objects */
    $dbobj = new DbObj();
    $terms = Array();
    foreach($results as $res) {
        $term = new Term($dbobj);
        $term->createFromRow($res);
        $terms[] = $term->arrayForm();
    }
    return $terms;
}

function normalSearch($search_term, $type, $count, $offset) {
    if(!$search_term) $search_term = "";
    $search_term = "%".$search_term."%";
    if(!$type) $type = "cde";
    if(!($count < 100 && $count > 0)) $count = 10;
    if($offset < 0) $offset = 0;

    $cxn = new Connection();
    $cxn->connect();
    $results = $cxn->select("terms", Array("*"), "sssii", Array($search_term, $search_term, $type, $offset, $count), "where (label like ? or ilx like ?) and type=? limit ?,?");
    $cxn->close();

    $dbobj = new DbObj();
    $terms = Array();
    foreach($results as $res) {
        $term = new Term($dbobj);
        $term->createFromRow($res);
        $terms[] = $term->arrayForm();
    }
    return $terms;
}

?>
