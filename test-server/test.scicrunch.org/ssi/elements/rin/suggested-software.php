<?php

$user = $data["user"];
$community = $data["community"];
// $error = $data["error"];

$report_html = \helper\htmlElement("suggested-software-items", Array(
     "user" => $user,
     "community" => $community,
//     "error" => $error,
 ));

$report_data = Array(
    "title" => "Suggested software",
    "breadcrumbs" => Array(
        Array("text" => "Home", "url" => $community->fullURL()),
        Array("text" => "Suggested software", "active" => true),
    ),
    "html-body" => $report_html,
);

echo \helper\htmlElement("rin-style-page", $report_data);
?>
