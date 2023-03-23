<?php

function getCommunityTerms($user, $api_key, $cid){
    $dbObj = new DbObj();
    $tc = new TermCommunity($dbObj);
    // $tc->getCommunityTerms($cid);
    //return $tc->communityTerms;
    $terms = $tc->getCommunityTerms($cid);
    if (!$terms){
        return APIReturnData::quick400("could not find community terms");
    }
    return APIReturnData::build($terms, true);
}

function getCommunityTermsByName($user, $api_key, $portal_name) {
    $community = new Community();
    if(!$community->getByPortalName($portal_name)) {
        return APIReturnData::quick400("could not find community");
    }
    if(!\APIPermissionActions\checkAction("get-terms-by-community", $api_key, $user, $community)) {
        return APIReturnData::quick403();
    }
    return APIReturnData::build(getCommunityTerms($user, $api_key, $community->id), true);
}

?>
