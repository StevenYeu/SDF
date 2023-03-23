<?php

function updateResourceMentionSource($user, $api_key, $scrid, $mentionid, $snippet) {
    if(!\APIPermissionActions\checkAction("update-resource-mention-source", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $rid = \helper\getIDFromRID($scrid);
    try {
        $mention = new ResourceMention($rid, $mentionid);
        $mention->updateSource($snippet, $cuser->id);
    } catch(Exception $e) {
        $message = $e->getMessage();
        if($message === "no mentions found") return APIReturnData::quick400($message);
        else throw $e;
    }
    return APIReturnData::build(true, true);
}

?>
