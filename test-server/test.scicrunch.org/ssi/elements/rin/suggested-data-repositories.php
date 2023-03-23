<?php

$user = $data["user"];
$community = $data["community"];
// $error = $data["error"];

if($community->rinStyle())
    $report_html = \helper\htmlElement("suggested-data-repositories-dknet", Array(
         "user" => $user,
         "community" => $community,
    //     "error" => $error,
     ));
else
    $report_html = \helper\htmlElement("suggested-data-repositories-niddk", Array(
         "user" => $user,
         "community" => $community,
    //     "error" => $error,
     ));

$report_data = Array(
    "title" => "Suggested data repositories",
    "breadcrumbs" => Array(
        Array("text" => "Home", "url" => $community->fullURL()),
        Array("text" => "Suggested data repositories", "active" => true),
    ),
    "html-body" => $report_html,
);

echo \helper\htmlElement("rin-style-page", $report_data);
?>
