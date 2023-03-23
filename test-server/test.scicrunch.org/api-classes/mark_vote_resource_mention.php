<?php

function markVoteResourceMention($user, $api_key, $action, $scrid, $mentionid, $rating){
    if($action === "vote"){
        if(!\APIPermissionActions\checkAction("mention-vote", $api_key, $user)) return APIReturnData::quick403();
    }elseif($action === "mark"){
        if(!\APIPermissionActions\checkAction("mention-mark", $api_key, $user, $scrid)) return APIReturnData::quick403();
    }else{
        return APIReturnData::build(NULL, false, 400, "invalid action");
    }

    $rid = \helper\getIDFromRID($scrid);
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    try{
        $mention = new ResourceMention($rid, $mentionid);
    }catch(Exception $e){
        $message = $e->getMessage();
        if($message === "no mentions found") return APIReturnData::quick400($message);
        else throw $e;
    }

    try{
        if($action === "mark") $mention->updateRating($rating, $cuser->id);
        elseif($action === "vote") $mention->vote($rating, $cuser->id);
    }catch(Exception $e){
        $message = $e->getMessage();
        if($message === "invalid rating" || $message === "invalid vote") return APIReturnData::quick400($message);
        else throw $e;
    }
    return APIReturnData::build(true, true);
}

?>
