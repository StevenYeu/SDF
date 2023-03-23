<?php
include_once('../classes/mailer.class.php'); // Added by Steven - to send emails

function checkPendingOwner($user, $api_key, $rid){
    $cuser = \APIPermissionActions\getUser($api_key, $user);
    $resource_id = \helper\getIDFromRID($rid);
    if(is_null($resource_id)) return APIReturnData::quick400("could not find resource id");
    $rel = ResourceUserRelationship::loadBy(Array("uid", "rid", "type"), Array($cuser->id, $resource_id, ResourceUserRelationship::TYPE_PENDING_OWNER));
    $pending = !is_null($rel);

    return APIReturnData::build($pending, true);
}

function addPendingOwner($user, $api_key, $rid, $text){
    $cuser = \APIPermissionActions\getUser($api_key, $user);
    $resource_id = \helper\getIDFromRID($rid);
    $resource = new Resource();
    $resource->getByID($resource_id);
    $resource->getColumns();
    if(is_null($resource_id)) return APIReturnData::quick400("could not find resource id");
    $owner = ResourceUserRelationship::loadBy(Array("uid", "rid"), Array($cuser->id, $resource_id));
    if(!is_null($owner)) return APIReturnData::quick400("user is already owner or pending owner of resource");
    $owner = ResourceUserRelationship::createNewObj($resource_id, $cuser->id, "pending-owner", $text);
    $email_message = emailMessage($cuser, $text, $resource);
    $text_message = textMessage($cuser, $text, $resource);
    sendEmailUCSD(array('syeu@sdsc.edu'), $email_message, $text_message, "New pending owner request");
    return APIReturnData::build($owner, true, 201);
}

// Added by Steven - Replace original email function
function sendEmailUCSD($to, $html_message, $text_message, $subject, $reply_to = NULL, $from_name = "SciCrunch"){
    try {
        $mail = new PHPMailer(true);
        $mail->SMTPDebug = SMTP::DEBUG_SERVER;            
        $mail->isSMTP();                                           
        $mail->Host       = 'outbound.ucsd.edu';                     
        $mail->SMTPAuth   = false;                                                                
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;            
        $mail->Port       = 465;                                   
    
        $mail->setFrom('syeu@ucsd.edu', 'SDF');
        foreach($to as $email)
        {
           $mail->AddAddress($email);
        }
        $mail->isHTML(true);                               
        $mail->Subject = $subject;
        $mail->Body    =  $html_message;
        $mail->AltBody = $text_message;
    
        $mail->send();
    } catch (Exception $e) {
        error_log("****PHPMailer Message could not be sent. Mailer Error");
    }
}

function reviewPendingOwner($user, $api_key, $rid, $uid, $status){
    if(!\APIPermissionActions\checkAction("pending-owners-review", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);
    $resource_id = \helper\getIDFromRID($rid);
    if(is_null($resource_id)) return APIReturnData::quick400("could not find resource id");
    $pending_owner = ResourceUserRelationship::loadBy(Array("uid", "rid", "type"), Array($uid, $resource_id, ResourceUserRelationship::TYPE_PENDING_OWNER));
    if(is_null($pending_owner)) return APIReturnData::quick400("could not find pending owner");
    $rur = ResourceUserRelationship::reviewPending($pending_owner, $status, $cuser->id);
    if($status === "accept" && !is_null($rur)) {
        require_once __DIR__."/update_subscription.php";
        $owner_user = new User();
        $owner_user->getByID($rur->uid);
        if(!$owner_user->id) return $rur;
        updateSubscription($owner_user, NULL, "subscribe", "resource-mention", $rid);
    }

    return APIReturnData::build($rur, true);
}

function getAllPendingOwners($user, $api_key, $count, $offset){
    if(!\APIPermissionActions\checkAction("pending-owners-get-all", $api_key, $user)) return APIReturnData::quick403();
    $pending_owners = ResourceUserRelationship::loadArrayBy(Array("type"), Array(ResourceUserRelationship::TYPE_PENDING_OWNER), false, $count, false, $offset);

    return APIReturnData::build($pending_owners, true);
}

function getAllPendingOwnersCount($user, $api_key){
    if(!\APIPermissionActions\checkAction("pending-owners-get-all", $api_key, $user)) return APIReturnData::quick403();
    $cxn = new Connection();
    $cxn->connect();
    $result = $cxn->select("resource_user_relationships", Array("count(*)"), "", Array(), "where type='pending-owner'");
    $count = $result[0]["count(*)"];
    $cxn->close();

    return APIReturnData::build($count, true);
}
 
// Changed by Steven - Updated to link SDF Site
function emailMessage($user, $text, $resource) {
    $message = Array(
        $user->getFullName() . " (" . $user->email . ") has requested ownership of the resource <a href='https://sdf.sdsc.edu/Software-Discovery-Portal/Resources/record/sdf/". $resource->rid . "/resolver" . "'>". $resource->columns["Resource Name"] ."</a>.",
        "The requester has given the following message:",
        $text,
    );
    return \helper\buildEmailMessage($message);
}

function textMessage($user, $text, $resource) {
    $message = $user->getFullName() . " (" . $user->email . ") has requested ownership of the resource " . $resource->columns["Resource Name"] . " (" . $resource->rid . ").  The requester has given the following message: " . $text;
    return $message;
}

?>
