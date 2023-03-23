<?php

require_once __DIR__."/get_all_resource_mentions.php";

function getSingleResourceMention($user, $api_key, $scrid, $mentionid){
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $rid = \helper\getIDFromRID($scrid);
    if(!$rid) return APIReturnData::build(NULL, false, 400, "invalid resource id");
    try{
        $mention = new ResourceMention($rid, $mentionid);
        $frmt_mentions = formatMentions(Array($mention), $cuser);
        $result = $frmt_mentions[0];

        return APIReturnData::build($result, true);
    }catch(Exception $e){
        return APIReturnData::build(NULL, false, 400, "could not find resource mention");
    }
}

?>
