<?php

    function getTermAndMappings($user, $api_key, $tmid){
        $dbObj = new DbObj();

        $term = new Term($dbObj);
        $term->getMappings($tmid);
        if($term->ilx != "") {
            $term->getByIlx($term->ilx);
            $term->getExistingIds();
            $term->getSynonyms();
        }

        unset($term->dbObj);

        return $term;
    }

    function getTermMappingMatches($user, $api_key, $matchedValue, $origValue) {
        $cxn = new Connection();
        $cxn->connect();
        $mapping_results = [];
        $search_manager = ElasticInterLexManager::managerByViewID("interlex");

        if($matchedValue != "" && $origValue != "") $tm_results = $cxn->select("term_mappings", Array("*"), "ss", Array($matchedValue, $origValue), "where (matched_value=? or value=?) and curation_status='approved' and view_id='foundry'");
        else if($origValue != "") $tm_results = $cxn->select("term_mappings", Array("*"), "s", Array($origValue), "where value=? and curation_status='approved' and view_id='foundry'");
        else if($matchedValue != "") $tm_results = $cxn->select("term_mappings", Array("*"), "s", Array($matchedValue), "where matched_value=? and curation_status='approved' and view_id='foundry'");

        foreach ($tm_results as $tm_result) {
            if($tm_result["tid"] != null) {
                $t_result = $cxn->select("terms", Array("id, definition, ilx, type, version"), "i", Array($tm_result["tid"]), "where id=?")[0];
                $es_result = $search_manager->searchInterLex($t_result["ilx"]);
                foreach ($es_result as $res) {
                    $preferredID = $res->getField("Preferred ID");
                    $synonyms = $res->getField("Synonyms");
                }
                $info = [];
                if($tm_result["matched_value"] != "") $info[] = "Source Value: ".$result["matched_value"].";";
                if($tm_result["value"] != "") $info[] = "Original Value: ".$result["value"].";";
                if($t_result["definition"] != "") $info[] = "Description: ".$result["definition"];

                $mapping_results[] = Array(
                    "name" => $tm_result["concept"],
                    "preferredID" => $preferredID,
                    "description" => $t_result["definition"],
                    "ilx" => $t_result["ilx"],
                    "concept_id" => $tm_result["concept_id"],
                    "type" => $t_result["type"],
                    "synonyms" => $synonyms,
                    "version" => $t_result["version"],
                    "source" => $tm_result["source"],
                    "matched_value" => $tm_result["matched_value"],
                    "value" => $tm_result["value"],
                    "info" => join(" ", $info),
                    "tid" => $t_result["id"],
                );
            }
        }
        $cxn->close();
        return $mapping_results;
    }

    function getTermMappingsOrder($user, $api_key, $tmid){
        $cxn = new Connection();
        $cxn->connect();
        $tm_results = $cxn->select("term_mappings", Array("id"), "", Array(), "where curation_status!='approved' and curation_status!='rejected' and view_id='Foundry' order by column_name, view_name, id desc");
        $cxn->close();

        if(count($tm_results) == 0) {
            $next_id = 0;
            $previous_id = 0;
        } else {
            $mappings_order = array_column($tm_results, "id");
            if(in_array($tmid, $mappings_order)) {
                $current_idx = array_search($tmid, $mappings_order);
                if($current_idx == count($mappings_order)-1) $next_id = $mappings_order[0];
                else $next_id = $mappings_order[$current_idx+1];
                if($current_idx == 0) $previous_id = $mappings_order[count($mappings_order)-1];
                else $previous_id = $mappings_order[$current_idx-1];
            } else {
                foreach($mappings_order as $idx => $mapping_id) {
                    if($mapping_id < $tmid) {
                        $next_id = $mapping_id;
                        $previous_id = $mappings_order[$idx-1];
                        break;
                    }
                }

                if($next_id == null ) $next_id = $mappings_order[0];
                if($previous_id == null) $previous_id = $mappings_order[count($mappings_order)-1];
            }
        }

        return Array("previous_id" => $previous_id, "next_id" => $next_id);
    }

?>
