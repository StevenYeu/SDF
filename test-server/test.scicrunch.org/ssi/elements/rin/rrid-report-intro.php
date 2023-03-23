<?php

$user = $data["user"];
$community = $data["community"];
$error = $data["error"];

$report_html = \helper\htmlElement("rrid-report-intro", Array(
    "user" => $user,
    "community" => $community,
    "error" => $error,
));

$report_data = Array(
    "title" => "Authentication Reports",
    "breadcrumbs" => Array(
        Array("text" => "Home", "url" => $community->fullURL()),
        Array("text" => "Authentication Reports", "active" => true),
    ),
    "html-body" => $report_html,
);

echo \helper\htmlElement("rin-style-page", $report_data);
?>
