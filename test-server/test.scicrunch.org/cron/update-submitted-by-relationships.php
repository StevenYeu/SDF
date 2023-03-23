<?php
/* This script is for updating submitted by relationships, including onces that 'accidentally' get deleted.  Should probably be run every Thursday before push */

// load classes
$docroot = "..";
require_once $docroot . "/classes/classes.php";

$cxn = new Connection();
$cxn->connect();

// get resources submitted by a community
$resource_results = $cxn->select("resources", Array("id", "cid"), "", Array(), "where cid != 0");

// get communities with scrid
$community_results = $cxn->select("communities", Array("id"), "", Array(), "where rid != '' and rid is not NULL");

$cxn->close();

// make communities objects
$communities = Array();
foreach($community_results as $cr) {
    $id = $cr["id"];
    $community = new Community();
    $community->getByID($id);
    $communities[$id] = $community;
}

// make the resource objects
$resources = Array();
foreach($resource_results as $rr) {
    // make sure community is set
    if(!isset($communities[$rr["cid"]])) continue;

    // get the resource
    $resource = new Resource();
    $resource->getByID($rr["id"]);

    // make sure at least one version was curated
    if($resource->getLastCuratedVersionNum() == 0) continue;

    // add the submitted by relationship
    try {
        $communities[$resource->cid]->addSubmittedBy($resource, NULL, NULL);
    } catch (Exception $e) {
        // resource probably already exists
        print("Exception: " . $e->getMessage() . "\n");
        continue;
    }
}
?>
