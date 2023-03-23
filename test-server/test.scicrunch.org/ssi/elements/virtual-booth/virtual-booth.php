<?php
    $community = $data["community"];

    $report_html = \helper\htmlElement("virtual-booth/main", Array(
         "community" => $community,
    //     "error" => $error,
     ));

    $report_data = Array(
        "title" => "<b>Welcome to dkNET Virtual Booth</b>",
        "breadcrumbs" => Array(
            Array("text" => "Home", "url" => $community->fullURL()),
            Array("text" => "Virtual Booth", "active" => true),
        ),
        "html-body" => $report_html,
    );

    echo \helper\htmlElement("rin-style-page", $report_data);
?>
