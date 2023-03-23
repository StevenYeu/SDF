<?php

function createRMSD($user, $api_key, $portal_name, $submission_folder, $week, $year, $json, $source=null, $version, $version_schrodinger=null, $targets_user=null, $box_file_id=null) {
    $community = new Community();
    $community->getByPortalName($portal_name);
    if(is_null($community->id)) return APIReturnData::quick400("could not find community");

    // want to save this json data to the database.
    $vars['submission_folder'] = $submission_folder;
    $vars['week'] = $week;
    $vars['year'] = $year;
    $vars['json'] = json_encode($json);
    $vars['add_date'] = time();
    $vars['box_file_id'] = $box_file_id;
    $vars['source'] = $source;
    $vars['version'] = $version;
    $vars['version_schrodinger'] = $version_schrodinger;
    $vars['targets_user'] = $targets_user;

    $challenge = new D3RCelpp;
    $challenge->createFromRow($vars);
    $challenge->insertCELPPjson();

//    $celpp = Challenge::insertCELPPjson($community, $cuser, $name, $private_description, $public_description);
    if(is_null($challenge)) return APIReturnData::quick400("could not create rmsd entry");
//    if(\APIPermissionActions\checkAction("community-moderator", $api_key, $user, Array("community" => $community))) $lab->approveLab(Lab::CURATED_STATUS_APPROVED);

    return APIReturnData::build($challenge, true);
}

//    return appReturn($app, createWeek($app["config.user"], $app["config.api_key"], $portal_name, $week, $year, $targets), true);

/*
function getCommunityDatasets($user, $api_key, $portal_name) {
    $community = new Community();
    $community->getByPortalName($portal_name);
    if(is_null($community->id)) return APIReturnData::quick400("could not find community");

    if(!\APIPermissionActions\checkAction("community-visible", $api_key, $user, $community)) return APIReturnData::quick403();

    $datasets = Dataset::loadArrayBy(Array("cid"), Array($community->id));
    return APIReturnData::build($datasets, true);
}
*/
function getRMSD($user, $api_key, $portal_name, $submission_folder, $week, $year) {
    $community = new Community();
    $community->getByPortalName($portal_name);
    if(is_null($community->id)) return APIReturnData::quick400("could not find community");

    $challenge = new D3RCelpp;
    $json =  $challenge->getCELPPjsonByWeekYear($submission_folder, $week, $year);


    if(is_null($challenge)) return APIReturnData::quick400("could not create week");

    return APIReturnData::build($json, true);

}

function createWeek($user, $api_key, $portal_name, $week, $year, $targets, $source=null, $box_folder=null) {
    $community = new Community();
    $community->getByPortalName($portal_name);
    if(is_null($community->id)) return APIReturnData::quick400("could not find community");

    // want to save this json data to the database.
    $vars['week'] = $week;
    $vars['year'] = $year;
    $vars['targets'] = $targets;
    $vars['box_folder'] = $box_folder;
    $vars['source'] = $source;

    $challenge = new D3RCelpp;
    $challenge->createFromRow($vars);
    $challenge->insertCELPPweek();

    if(is_null($challenge)) return APIReturnData::quick400("could not create week");

    return APIReturnData::build($challenge, true);
}

?>
