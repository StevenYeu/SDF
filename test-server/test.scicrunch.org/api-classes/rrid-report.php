<?php

function addReportItem($user, $api_key, $rrid_report_id, $type, $subtype, $rrid, $uuid, $uid) {
    $rrid_report = RRIDReport::loadBy(Array("id"), Array($rrid_report_id));
    if(is_null($rrid_report)) return APIReturnData::quick400("could not find rrid report");
    if(!\APIPermissionActions\checkAction("rrid-report-add-item", $api_key, $user, Array("rrid-report" => $rrid_report))) return APIReturnData::quick403();

    if(!$subtype) $subtype = NULL;
    $rrid_item = RRIDReportItem::loadBy(Array("rrid_report_id", "type", "rrid"), Array($rrid_report->id, $type, $rrid));
    if(is_null($rrid_item)) {
        $rrid_item = RRIDReportItem::createNewObj($rrid_report, $type, $subtype, $rrid, $uuid, $uid); ## input uuid value -- Vicky-2019-2-15
    } else {
        $rrid_item->subtypeCreate($subtype);
    }

    if(is_null($rrid_item)) return APIReturnData::quick400("unable to add item to report");
    return APIReturnData::build($rrid_item, true);
}

function deleteReportItem($user, $api_key, $rrid_report_id, $uuid, $type, $subtype, $full_delete=false) {
    $rrid_report = RRIDReport::loadBy(Array("id"), Array($rrid_report_id));
    if(is_null($rrid_report)) return APIReturnData::quick400("could not find rrid report");
    if(!\APIPermissionActions\checkAction("rrid-report-delete-item", $api_key, $user, Array("rrid-report" => $rrid_report))) return APIReturnData::quick403();
    $report_item = RRIDReportItem::loadBy(Array("uuid", "rrid_report_id", "type"), Array($uuid, $rrid_report->id, $type));
    if(is_null($report_item)) return APIReturnData::quick400("could not find report item");
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    if($full_delete) {
        RRIDReportItem::deleteObj($report_item);
    } else {
        $report_item->subtypeDelete($subtype, true);
    }

    $in_coll = Collection::checkInCollection($uuid, $cuser);

    return APIReturnData::build(Array("inColl" => $in_coll), true);
}

function getReportItemsByUUID($user, $api_key, $rrid_report_id, $uuid) {
    $rrid_report = RRIDReport::loadBy(Array("id"), Array($rrid_report_id));
    if(is_null($rrid_report)) return APIReturnData::quick400("could not find rrid report");
    if(!\APIPermissionActions\checkAction("rrid-report-get-items", $api_key, $user, Array("rrid-report" => $rrid_report))) return APIReturnData::quick403();
    $cuser = !\APIPermissionActions\getUser($api_key, $user);

    $items = RRIDReportItem::loadBy(Array("rrid_report_id", "uuid"), Array($rrid_report->id, $uuid));

    return APIReturnData::build($items, true);
}

function updateReportItemSubtypeUserData($user, $api_key, $report_item_id, $user_data) {
    $rrid_report_item = RRIDReportItem::loadBy(Array("id"), Array($report_item_id));
    if(is_null($rrid_report_item)) return APIReturnData::quick400("could not find rrid report item");
    $rrid_report = $rrid_report_item->report();
    if(!\APIPermissionActions\checkAction("rrid-report-update", $api_key, $user, Array("rrid-report" => $rrid_report))) return APIReturnData::quick403();

    /* expected structure: subtype-id => , name => , val =>, */
    foreach($user_data as $ud) {
        $subtype = RRIDReportItemSubtype::loadBy(Array("id"), Array($ud["subtype-id"]));
        if($subtype->rrid_report_item_id !== $rrid_report_item->id) continue;
        $subtype->setUserData($ud["name"], $ud["val"]);
    }

    return APIReturnData::build(true, true);
}

function updateReportItemUserData($user, $api_key, $report_item_id, $user_data) {
    $rrid_report_item = RRIDReportItem::loadBy(Array("id"), Array($report_item_id));
    if(is_null($rrid_report_item)) return APIReturnData::quick400("could not find rrid report item");
    $rrid_report = $rrid_report_item->report();
    if(!\APIPermissionActions\checkAction("rrid-report-update", $api_key, $user, Array("rrid-report" => $rrid_report))) return APIReturnData::quick403();

    foreach($user_data as $ud) {
        $rrid_report_item->setUserData($ud["name"], $ud["val"]);
    }

    return APIReturnData::build(true, true);
}

function getReportItems($user, $api_key, $rrid_report_id) {
    $rrid_report = RRIDReport::loadBy(Array("id"), Array($rrid_report_id));
    if(is_null($rrid_report)) return APIReturnData::quick400("could not find rrid report");
    if(!\APIPermissionActions\checkAction("rrid-report-get-items", $api_key, $user, Array("rrid-report" => $rrid_report))) return APIReturnData::quick403();

    $items = $rrid_report->items();

    return APIReturnData::build($items, true);
}

function createSnapshot($user, $api_key, $rrid_report_id) {
    $rrid_report = RRIDReport::loadBy(Array("id"), Array($rrid_report_id));
    if(is_null($rrid_report)) return APIReturnData::quick400("could not find rrid report");
    if(!\APIPermissionActions\checkAction("rrid-report-create-snapshot", $api_key, $user, Array("rrid-report" => $rrid_report))) return APIReturnData::quick403();

    $snapshot = RRIDReportFreeze::createNewObj($rrid_report);
    if(is_null($snapshot)) return APIReturnData::quick400("failed to create snapshot");

    return APIReturnData::build($snapshot, true);
}

function updateReportNameDescription($user, $api_key, $report_id, $name, $description) {
    $rrid_report = RRIDReport::loadBy(Array("id"), Array($report_id));
    if(is_null($rrid_report)) return APIReturnData::quick400("could not find rrid report");
    if(!\APIPermissionActions\checkAction("rrid-report-update", $api_key, $user, Array("rrid-report" => $rrid_report))) return APIReturnData::quick403();

    if($name) {
        $rrid_report->name = $name;
    }
    if($description !== null) {
        $rrid_report->description = $description;
    }

    $rrid_report->updateDB();

    return APIReturnData::build($rrid_report, true);
}

function newReport($user, $api_key, $name, $description) {
    if(!\APIPermissionActions\checkAction("rrid-report-new", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);
    $report = RRIDReport::createNewObj($name, $description, $cuser, NULL);
    if(is_null($report)) {
        return APIReturnData::quick400("could not create report");
    }
    return APIReturnData::build($report, true);
}

?>
