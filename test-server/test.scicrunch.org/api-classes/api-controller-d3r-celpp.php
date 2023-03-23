<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

$app->post($AP."/d3r/celpp/rmsd", function(Request $request) use($app) {
    require_once __DIR__."/d3r-celpp.php";
    $portal_name = $request->request->get("portal_name");
    $submission_folder = $request->request->get("submission_folder");
    $week = $request->request->get("week");
    $year = $request->request->get("year");
    $json = $request->request->get("json");
    $source = $request->request->get("source");
    $version = $request->request->get("version");
    $version_schrodinger = $request->request->get("version_schrodinger");
    $targets_user = $request->get("targets_user");

    return appReturn($app, createRMSD($app["config.user"], $app["config.api_key"], $portal_name, $submission_folder, $week, $year, $json, $source, $version, $version_schrodinger, $targets_user), true, true);
});

$app->get($AP."/d3r/celpp/rmsd/{week}/{year}", function(Request $request, $week, $year) use($app) {
    require_once __DIR__."/d3r-celpp.php";
    $portal_name =  $_GET['portal_name'];
    $submission_folder = $_GET['submission_folder'];

   // return appReturn($app, getRMSD($app["config.user"], $app["config.api_key"], $portal_name, $submission_folder, $week, $year), true, true);
    
    $results = getRMSD($app["config.user"], $app["config.api_key"], $portal_name, $submission_folder, $week, $year);
        return $app->json($results, 200);
});

$app->post($AP."/d3r/celpp/week", function(Request $request) use($app) {
    require_once __DIR__."/d3r-celpp.php";
    $portal_name = $request->request->get("portal_name");
    $week = $request->request->get("week");
    $year = $request->request->get("year");
    $targets = $request->request->get("targets");
    $source = $request->request->get("source");

    return appReturn($app, createWeek($app["config.user"], $app["config.api_key"], $portal_name, $week, $year, $targets, $source), true, true);
});


?>
