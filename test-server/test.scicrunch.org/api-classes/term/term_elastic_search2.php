<?php

    include 'process-elastic-search.php';

    function getTermESResults($user, $api_key, $keywords) {
        $search_manager = ElasticInterLexManager::managerByViewID("interlex");
        if(is_null($search_manager)) {
            return;
        }
        $keywords = str_replace("/", " ", $keywords);
        $keywords = formatKeywords($keywords);

        $results = $search_manager->search($keywords, 10, 1, Array());
        $es_results = Array();
        $cxn = new Connection();
        $cxn->connect();
        foreach ($results as $result) {
            $ilx = $result->getField("ID");
            $t_result = $cxn->select("terms", Array("id, version"), "s", Array($ilx), "where ilx=?")[0];
            $es_results[] = Array(
                "name" => $result->getField("Name"),
                "preferredID" => $result->getField("Preferred ID"),
                "description" => $result->getField("Description"),
                "ilx" => $ilx,
                "concept_id" => str_replace("ilx_", "ilx:", $ilx),
                "type" => $result->getField("Type"),
                "synonyms" => $result->getField("Synonyms"),
                "version" => $t_result["version"],
                "tid" => $t_result["id"],
            );
        }
        $cxn->close();

        return $es_results;
    }
?>
