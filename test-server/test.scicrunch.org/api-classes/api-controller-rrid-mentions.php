<?php

use Swagger\Annotations as SWG;
use Symfony\Component\HttpFoundation\Request;

$app->get($AP."/rrid-mentions", function(Request $request) use($app) {
    require_once __DIR__ . "/rrid-mentions.php";
    $count = $request->query->get("count");
    $offset = $request->query->get("offset");
    $query = $request->query->get("q");
    $facets = $request->query->get("facets");

    $fmt_facets = Array();
    foreach($facets as $facet) {
        $facet_split = explode("|", $facet);
        if(count($facet_split) != 2) continue;
        if(!isset($fmt_facets[$facet_split[0]])) $fmt_facets[$facet_split[0]] = Array();
        $fmt_facets[$facet_split[0]][] = $facet_split[1];
    }

    if($count < 1) $count = 1;
    if($count > 100) $count = 100;
    if($offset < 0) $offset = 0;

    $rrid_mentions_return = getRRIDMentions($app["config.user"],
        $app["config.api_key"], $query, $fmt_facets, $count, $offset);
    if($rrid_mentions_return->success) {
        $rrid_mentions = $rrid_mentions_return->data;
        $results = Array(
            "count" => $rrid_mentions["count"],
            "rrid-mentions" => Array(),
            "facets" => $rrid_mentions["facets"],
        );
        foreach($rrid_mentions["rrid-mentions"] as $rm) {
            $results["rrid-mentions"][] = $rm->arrayForm();
        }
        $return = APIReturnData::build($results, true);
    } else {
        $return = $rrid_mentions_return;
    }
    return appReturn($app, $return, false);
});

$app->get($AP."/rrid/mentions/{rrid}", function(Request $request, $rrid) use($app) {
    require_once __DIR__ . "/rrid-mentions.php";
    $count = $request->query->get("count");
    $offset = $request->query->get("offset");

    if($count > 100 || $count < 0) $count = 100;
    if($offset < 0) $offset = 0;

    $rrid_mentions_return = getRRIDMentionsByRRID($app["config.user"], $app["config.api_key"], $rrid, $count, $offset);
    if($rrid_mentions_return->success) {
        $rrid_mentions = $rrid_mentions_return->data;
        $results = Array(
            "count" => $rrid_mentions["count"],
            "rrid-mentions" => Array(),
        );
        foreach($rrid_mentions["rrid-mentions"] as $rm) {
            $results["rrid-mentions"][] = $rm->arrayForm();
        }
        $return = APIReturnData::build($results, true);
    } else {
        $return = $rrid_mentions_return;
    }
    return appReturn($app, $return, false);
});

?>
