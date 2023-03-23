<?php

    $user = $data["user"];
    $community = $data["community"];
    // $error = $data["error"];

    $report_html = \helper\htmlElement("rin/sources", Array(
         "user" => $user,
         "community" => $community,
         // "search_manager" => $search_manager,
         // "result" => $result,
    //     "error" => $error,
     ));

    $report_data = Array(
        "title" => "RIN Sources",
        "breadcrumbs" => Array(
            Array("text" => "Home", "url" => $community->fullURL()),
            Array("text" => "RIN Sources", "active" => true),
        ),
        "html-body" => $report_html,
    );

    echo \helper\htmlElement("rin-style-page", $report_data);
?>
