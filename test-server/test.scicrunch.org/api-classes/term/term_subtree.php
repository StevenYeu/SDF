<?php

function getTermFromLabels($labels, $types='all'){
    $cxn = new Connection();
    $cxn->connect();
    $labels = array_map('trim', explode(',', $labels));
    if ($types == 'all') {
        $sql = "SELECT * FROm terms t WHERE t.label IN ('" . implode("', '", $labels) . "');";
    } else {
        $types = array_map('trim', explode(',', $types));  // comma delimited strings to list
        $sql = "SELECT * FROM terms t WHERE t.label IN ('" . implode("', '", $labels) . "') " .
               "AND t.type IN ('" . implode("', '", $types) . "');";
    }
    $results = array();
    if ($result = $cxn->mysqli->query($sql)) {
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $results[] = $row;
        }
    }
    $cxn->close();
    return $results;
}

// TODO: Add annotation traversal?
// TODO: Keep superclass traversal?
function getTermSubtree($user, $api_key, $root, $edges, $types='all', $traverse_superclasses='false') {
    require_once "term_by_id.php";
    require_once "term_by_ilx.php";

    $cxn = new Connection();
    $cxn->connect();
    $help = ""; // TODO: add help return
    $results = array();
    $visited = array();  // prevents cyclic issues!

    // ROOT
    $queue = array(); // brainstem test for 3/4
    try {
        if (is_numeric($root)){
            $resp = getTermById($user, $api_key, $root);
        } else {
            $resp = getTermByIlx($user, $api_key, $root);
        }
        if (is_null($resp['id'])){
            $resp = getTermFromLabels($root)[0];  // You assume your label is unique!
        }
        $results['hierarchy'][] = $resp;
        $queue[] = $resp['id'];
        $visited[$resp['id']] = TRUE;
    } catch (Exception $e) {
        return "root: [$root] does not exist.";
    }
    $results['root_query'] = $root;
    $results['root_found'] = $resp;

    // RELATIONSHIPS
    $relationship_terms = getTermFromLabels($edges, $types);
    $relationship_tids = [];
    foreach ($relationship_terms as $relationship_term)
        array_push($relationship_tids, $relationship_term['id']);
    if (empty($relationship_tids))
        return "edges: [$edges] do not exist in InterLex.";
    $results['relationship_query'] = $edges;
    $results['relationships_found'] = $relationship_terms;

    // BFS TRAVERSAL
    while (sizeof($queue)) {
        $term1_id = array_pop($queue);
        $sql = "SELECT * FROM term_relationships tr WHERE tr.term1_id = '$term1_id' " .
               "AND tr.relationship_tid IN ('" . implode("', '", $relationship_tids) . "');";
        // return $sql;
        if ($result = $cxn->mysqli->query($sql)) {
            while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                if ($visited[$row['term2_id']] == TRUE)
                    continue;
                $visited[$row['term2_id']] = TRUE;
                $queue[] = $row['term2_id'];
                $results['hierarchy'][] = getTermById($user, $api_key, $row['term2_id']);
            }
        }
        // TODO: Experimental; most likely will blow up the response too much.
        if (strtolower($traverse_superclasses) == 'true') {
            $sql = "SELECT * FROM term_superclasses ts WHERE ts.superclass_tid = '$term1_id';";
            if ($result = $cxn->mysqli->query($sql)) {
                while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
                    if ($visited[$row['tid']] == TRUE)
                        continue;
                    $visited[$row['tid']] = TRUE;
                    $queue[] = $row['tid'];
                    $results['hierarchy'][] = getTermById($user, $api_key, $row['tid']);
                }
            }
        }
    }

    $cxn->close();
    return $results;
}
?>