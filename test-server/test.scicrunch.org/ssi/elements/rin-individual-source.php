<?php

    $user = $data["user"];
    $community = $data["community"];
    $source_rrid = $data["source_rrid"];
    // $error = $data["error"];

    $search_manager = ElasticRRIDManager::managerByViewID("nlx_144509-1");
    if(is_null($search_manager)) return;

    $results = $search_manager->searchRRID($source_rrid);
    $result = $results->getByIndex(0);

    $report_html = \helper\htmlElement("rin/individual-source", Array(
         "user" => $user,
         "community" => $community,
         "search_manager" => $search_manager,
         "result" => $result,
         "rrid" => $source_rrid,
    //     "error" => $error,
     ));

    $report_data = Array(
        "title" => "RIN Source",
        "breadcrumbs" => Array(
            Array("text" => "Home", "url" => $community->fullURL()),
            Array("text" => "RIN Sources", "url" => $community->fullURL()."/rin/sources"),
            Array("text" => $result->getRRIDField("name"), "active" => true),
        ),
        "html-body" => $report_html,
    );

    echo \helper\htmlElement("rin-style-page", $report_data);
?>
