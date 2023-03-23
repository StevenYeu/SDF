<?php

function getCommunitySnippet($user, $api_key, $portalname, $nifid){
    $community = new Community();
    $community->getByPortalName($portalname);
    if(!$community->id && $community->id !== 0) return APIReturnData::quick400("invalid portalname");
    if(!\APIPermissionActions\checkAction("get-community-snippet", $api_key, $user, $community)) return APIReturnData::quick403();

    $snippet = new Snippet();
    $snippet->getSnippetByView($community->id, $nifid);
    $raw_snippet = $snippet->raw;

    return APIReturnData::build($raw_snippet, true);
}

?>
