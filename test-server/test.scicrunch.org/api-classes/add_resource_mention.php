<?php

function addResourceMention($user, $api_key, $scrid, $mentionid, $input_source, $confidence=1.0, $snippet=NULL){
    $permission_data = Array("input_source" => $input_source, "scrid" => $scrid);
    if(!\APIPermissionActions\checkAction("pmid-add", $api_key, $user, $permission_data)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $rid = \helper\getIDFromRid($scrid);
    $resource = new Resource();
    $resource->getByID($rid);
    $rating = ResourceMention::RATING_NONE;

    if(!$resource->id) return APIReturnData::quick400("could not find resource");
    if(!\helper\mentionIDFormat($mentionid)) return APIReturnData::build(NULL, false, 400, "bad mention ID format");


    try{
        $rm = ResourceMention::newUserResourceMention($cuser->id, $rid, $mentionid, $rating, $input_source, $confidence, $snippet);
    }catch(Exception $e){
        $message = $e->getMessage();
        if($message === "resource mention already exists"){
            $rm = new ResourceMention($rid, $mentionid);
            return APIReturnData::build($rm, true, 200, $message);
        }
        else throw $e;
    }

    $subscription_data = Array("rid" => $rid, "mentionid" => $mentionid);
    Subscription::newData("resource-mention", $subscription_data);
    return APIReturnData::build($rm, true, 201);
}

?>
