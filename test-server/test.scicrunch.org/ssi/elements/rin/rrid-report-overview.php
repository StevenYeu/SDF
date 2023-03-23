<?php

$user = $data["user"];
$community = $data["community"];
$error = $data["error"];

$report_html = \helper\htmlElement("rrid-report-overview", Array(
    "user" => $user,
    "community" => $community,
    "error" => $error,
));

$report_data = Array(
    "title" => "Report Dashboard",
    "breadcrumbs" => Array(
        Array("text" => "Home", "url" => $community->fullURL()),
        Array("text" => "Authentication Reports", "url" => $community->fullURL()."/rin/rrid-report/"),
        Array("text" => "Report Dashboard", "active" => true),
    ),
    "html-body" => $report_html,
);

echo \helper\htmlElement("rin-style-page", $report_data);
?>
