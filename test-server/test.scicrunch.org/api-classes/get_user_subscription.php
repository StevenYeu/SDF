<?php

function getUserSubscriptions($user, $api_key){
    $cuser = \APIPermissionActions\getUser($api_key, $user);
    if(is_null($cuser)) return APIReturnData::build(NULL, false, 400, "no user given");
    $subscriptions = Subscription::loadArrayBy(Array("uid"), Array($cuser->id));
    return APIReturnData::build($subscriptions, true);
}

function getSubscriptionData($user, $api_key, $id, $source) {
    if($source !== "web" && $source !== "email") return APIReturnData::quick400("invalid source, must be email or web");
    // $cuser = \APIPermissionActions\getUser($api_key, $user);    // disable checking because does not need to be private
    // if(is_null($cuser)) return APIReturnData::quick400("no user given"); // disable checking because does not need to be private
    $subscription = Subscription::loadBy(Array("id"), Array($id));
    // if($subscription->uid !== (int) $cuser->id) return APIReturnData::quick403();    // disable checking because does not need to be private

    if($source === "email") return APIReturnData::build($subscription->getNewDataEmail(), true);
    else return APIReturnData::build($subscription->getNewDataScicrunch(), true);
}

?>
