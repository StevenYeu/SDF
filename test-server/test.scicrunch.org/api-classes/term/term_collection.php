<?php

    function getTermCollection($user, $api_key, $ilx) {
        $search_manager = ElasticInterLexManager::managerByViewID("interlex");
        if(is_null($search_manager))
            return;
        $results = $search_manager->searchRelationships($ilx)["hits"]["hits"];
        $children = Array();
        foreach ($results as $result) {
            $result = $result["_source"];
            $relationship = Array();
            $children_count = 0;
            if($result["type"] == "term") $relationship['has_children'] = false;
            else if($result["type"] == "TermSet") {
                $relationship['has_children'] = true;
                foreach ($result["relationships"] as $value) {
                    if($value["term2_ilx"] != $result["ilx"])
                        $children_count++;
                }
                if($children_count == 0)
                    $relationship['has_children'] = false;
                $relationship["children_count"] = $children_count;
            }
            $relationship["type"] = $result["type"];
            $relationship["label"] = $result["label"];
            $relationship["ilx"] = $result["ilx"];
            $relationship["definition"] = $result["definition"];
            $existing_ids = $result["existing_ids"];
            foreach ($existing_ids as $existing_id) {
                if($existing_id["preferred"] == 1) {
                    $relationship["preferred_id"] = $existing_id["curie"];
                    break;
                }
            }
            $children[] = $relationship;
        }
        usort($children, function($a, $b) {return strcmp(strtolower($a["label"]), strtolower($b["label"]));});
        return $children;
    }
?>
