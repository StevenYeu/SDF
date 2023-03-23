<?php

function updateSubscription($user, $api_key, $action, $type, $identifier, $cid=0){
    if(!\APIPermissionActions\checkAction("subscribe_user", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    if($action === "subscribe"){
        $sub = Subscription::createNewObj($cuser->id, $type, $identifier, 1, 1, NULL, $cid);
        if(is_null($sub)) return APIReturnData::build(NULL, false, 400, "could not create subscription");
        return APIReturnData::build($sub, true, 201);
    }else{
        $sub = Subscription::loadBy(Array("uid", "type", "fid"), Array($cuser->id, $type, $identifier));
        if(is_null($sub)) return APIReturnData::build(NULL, false, 400, "could not find subscription");
        if($action === "unsubscribe"){
            Subscription::deleteObj($sub);
            return APIReturnData::build(true, true);
        }else{
            if($action === "subscribe-scicrunch"){
                $sub->scicrunch_notify = 1;
            }elseif($action === "unsubscribe-scicrunch"){
                $sub->scicrunch_notify = 0;
            }elseif($action === "subscribe-email"){
                $sub->email_notify = 1;
            }elseif($action === "unsubscribe-email"){
                $sub->email_notify = 0;
            }
            $sub->updateDB();
            return APIReturnData::build($sub, true);
        }
    }
}

?>
