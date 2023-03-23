<?php
    $community = $data["community"];

    $report_html = \helper\htmlElement("virtual-booth/resources", Array());

    $report_data = Array(
        "title" => "Resources/Brochure",
        "breadcrumbs" => Array(
            Array("text" => "Home", "url" => $community->fullURL()),
            Array("text" => "Virtual Booth", "url" => $community->fullURL()."/virtual-booth"),
            Array("text" => "Resources", "active" => true),
        ),
        "html-body" => $report_html,
    );

    echo \helper\htmlElement("rin-style-page", $report_data);
?>
