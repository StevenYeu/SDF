<?php

function updateResourceMentionSnippet($user, $api_key, $scrid, $mentionid, $snippet){
    if(!\APIPermissionActions\checkAction("update-resource-mention-snippet", $api_key, $user, $scrid)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $rid = \helper\getIDFromRID($scrid);
    try {
        $mention = new ResourceMention($rid, $mentionid);
        $mention->updateSnippet($snippet, $cuser->id);
    } catch(Exception $e) {
        $message = $e->getMessage();
        if($message === "no mentions found") return APIReturnData::quick400($message);
        else throw $e;
    }
    return APIReturnData::build(true, true);
}

?>
