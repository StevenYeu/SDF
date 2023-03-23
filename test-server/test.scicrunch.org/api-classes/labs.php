<?php

function createLab($user, $api_key, $portal_name, $name, $private_description, $public_description) {
    $community = new Community();
    $community->getByPortalName($portal_name);
    if(is_null($community->id)) return APIReturnData::quick400("could not find community");
    if(!$community->labEnabled()) return APIReturnData::quick400("data sharing not available for this community");

    if(!\APIPermissionActions\checkAction("community-member", $api_key, $user, Array("community" => $community))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $lab = Lab::createNewObj($community, $cuser, $name, $private_description, $public_description);
    if(is_null($lab)) return APIReturnData::quick400("could not create lab");
    if(\APIPermissionActions\checkAction("community-moderator", $api_key, $user, Array("community" => $community))) $lab->approveLab(Lab::CURATED_STATUS_APPROVED);

    /* send emails to community moderators */
    if($lab->curated == Lab::CURATED_STATUS_PENDING) {
        $action_key = ActionKey::createNewObj("lab-create", $lab->id, strtotime("+7 day"), NULL);
        $moderator_emails = $community->getModeratorEmails();
        $subject = "New lab in " . $community->portalName . " community";
        $approve_link = PROTOCOL . '://' . FQDN . '/forms/keyaction.php?key=' . $action_key->key_val . '&approve=true';
        $reject_link = PROTOCOL . '://' . FQDN . '/forms/keyaction.php?key=' . $action_key->key_val . '&approve=false';
        $email_html = Array(
            $cuser->getFullName() . ' in the ' . $community->portalName . ' community has created the lab: <b>' . $lab->name . '</b>',
            'This lab will have the ability publish data to your community',
            'Click <a href="' . $approve_link . '">here</a> to approve the lab',
            'Click <a href="' . $reject_link . '">here</a> to reject the lab',
            'These links will expire in seven days.  You will still be able to manage labs through the community management interface.'
        );
        $email_text = $cuser->getFullName() . ' in the ' . $community->portalName . ' community has created the lab: ' . $lab->name . '.  This lab will have the ability publish data to your community.  Follow this link ' . $approve_link . ' to approve the lab.  Follow this link ' . $reject_link . ' to reject the lab.  These links will expire in seven days.  You will still be able to manage labs through the community management interface.';
        foreach($moderator_emails as $email) {
            \helper\sendEmail($email, \helper\buildEmailMessage($email_html, 1, $community), $email_text, $subject, NULL);
        }
    }

    return APIReturnData::build($lab, true);
}

function reviewLab($user, $api_key, $labid, $review) {
    $lab = Lab::loadBy(Array("id"), Array($labid));
    if(is_null($lab)) return APIReturnData::quick400("could not find lab");

    $community = new Community();
    $community->getByID($lab->cid);

    if(!\APIPermissionActions\checkAction("community-moderator", $api_key, $user, Array("community" => $community))) return APIReturnData::quick403();

    $lab->approveLab($review);

    return APIReturnData::build(true, true);
}

function joinLab($user, $api_key, $labid) {
    $lab = Lab::loadBy(Array("id"), Array($labid));
    if(is_null($lab)) return APIReturnData::quick400("could not find lab");

    $community = new Community();
    $community->getByID($lab->cid);

    if(!\APIPermissionActions\checkAction("community-visible", $api_key, $user, $community)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);
    if(is_null($cuser)) return APIReturnData::quick403();

    $lab_membership = LabMembership::createNewObj($cuser, $lab, 0);

    if(is_null($lab_membership)) return APIReturnData::quick400("could not request to join lab");

    /* build approval email */
    $manager_emails = $lab->managerEmails();
    $subject = "Request to join " . $lab->name . " from " . $cuser->getFullName();
    
    $admin_link = PROTOCOL . "://" . FQDN . "/" . $community->portalName . "/lab/admin?labid=" . $labid;

    $userblock = "<b>User</b>: " . $cuser->getFullName() . "<br />\n";
    $userblock.= "<b>Email</b>: " . $cuser->email . "\n";
    if (!empty($cuser->organization))
        $userblock.= "<br /><b>Organization</b>: " . $cuser->organization . "\n";
    if (!empty($user->orcid_id))
        $userblock.= "<br /><b>ORCID</b>: <a href='https://orcid.org/" . $cuser->orcid_id . "'>" . $user->orcid_id . "</a>\n";
    if (!empty($cuser->website))
        $userblock.= "<br /><b>Lab website</b>: " . $cuser->website . "\n";

    $message = Array($userblock);
    $message[] = "<h2>Lab Join Request</h2>\n";
    $message[] = '<p><b>' . $cuser->getFullName() . '</b> has requested to join "' . $lab->name. '".</p>' . "\n" . '<p>Please go to the <a href="' . $admin_link . '">Lab Management page</a> to approve/reject the request.</p>';

    $cuserblock = "User: " . $cuser->getFullName() . "\n";
    $cuserblock.= "Email: " . $cuser->email . "\n";
    if (!empty($cuser->organization))
        $cuserblock.= "Organization: " . $cuser->organization . "\n";
    if (!empty($cuser->orcid_id))
        $cuserblock.= "ORCID: https://orcid.org/" . $cuser->orcid_id . "\n";
    if (!empty($cuser->website))
        $cuserblock.= "Lab website: " . $cuser->website . "\n";
    $cuserblock .= "\nLab Join Request\n\n";

    $email_text = $cuserblock . $cuser->getFullName() . ' has requested to join ' . $lab->name . ".\n\nPlease go to the Lab Management page: " . $admin_link . " to approve/reject the request.";

    foreach($manager_emails as $email) {
        \helper\sendEmail($email, \helper\buildEmailMessage($message, 1, $community), $email_text, $subject, NULL);
    }

    return APIReturnData::build(true, true);
}

function reviewUserLab($user, $api_key, $labid, $uid, $review) {
    $lab = Lab::loadBy(Array("id"), Array($labid));
    if(is_null($lab)) return APIReturnData::quick400("could not find lab");

    $lab_membership = LabMembership::loadBy(Array("uid", "labid"), Array($uid, $lab->id));
    if(is_null($lab_membership)) return APIReturnData::quick400("could not find lab membership");
    if(!\APIPermissionActions\checkAction("lab-change-level", $api_key, $user, Array("lab" => $lab, "lab_membership" => $lab_membership, "level" => $review))) return APIReturnData::quick403();

    $review_i = (int) $review;
    if($review_i != $review) {
        return APIReturnData::quick400("could not change user's level");
    }

    if($lab_membership->level == 0) {
        if($review_i === 0) {
            $lab_membership->reject();
        } else {
            $lab_membership->approve();
        }
    } else {
        if($review_i === 0) {
            LabMembership::deleteObj($lab_membership);
            return APIReturnData::build(true, true);
        } else {
            $lab_membership->level = $review_i;
            $lab_membership->updateDB();
        }
    }

    if($lab_membership->level === $review_i) {
        return APIReturnData::build(true, true);
    }
    return APIReturnData::quick400("could not change user's level");
}

function editLabInfo($user, $api_key, $labid, $name, $private_description, $public_description, $broadcast_message) {
    $lab = Lab::loadBy(Array("id"), Array($labid));
    if(is_null($lab)) return APIReturnData::quick400("could not find lab");

    if(!\APIPermissionActions\checkAction("lab-moderator", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();

    if($name) {
        $lab->name = $name;
    }

    if($private_description) {
        $lab->private_description = $private_description;
    }

    if($public_description) {
        $lab->public_description = $public_description;
    }

    $lab->broadcast_message = $broadcast_message;

    $lab->updateDB();

    return APIReturnData::build($lab, true);
}

function getLabUsers($user, $api_key, $labid) {
    $lab = Lab::loadBy(Array("id"), Array($labid));
    if(is_null($lab)) return APIReturnData::quick400("could not find lab");

    if(!\APIPermissionActions\checkAction("lab-moderator", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();

    $memberships = LabMembership::loadArrayBy(Array("labid"), Array($lab->id));

    return APIReturnData::build($memberships, true);
}

function getLabID($user, $api_key, $labname, $portal_name) {
    $community = new Community();
    $community->getByPortalName($portal_name);
    if(is_null($community->id)) return APIReturnData::quick400("could not find community");

    if(!\APIPermissionActions\checkAction("get-lab-id", $api_key, $user)) return APIReturnData::quick403();

    $lab = Lab::loadBy(Array("cid", "name"), Array($community->id, $labname));
    if(is_null($lab)) return APIReturnData::quick400("could not find lab");

    return APIReturnData::build($lab->id, true);
}

function getLabDatasets($user, $api_key, $labid) {
    $lab = Lab::loadBy(Array("id"), Array($labid));
    if(is_null($lab)) return APIReturnData::quick400("could not find lab");

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $datasets = $lab->datasets();
    $return_datasets = Array();

    foreach($datasets as $dataset) {
        if($dataset->isVisible($cuser)) {
            $dataset->canEditSave($cuser);
            $return_datasets[] = $dataset;
        }
    }

    return APIReturnData::build($return_datasets, true);
}

function getLabInfo($user, $api_key, $labid) {
    $lab = Lab::loadBy(Array("id"), Array($labid));
    if(is_null($lab)) return APIReturnData::quick400("could not find lab");

    if(!\APIPermissionActions\checkAction("lab-member", $api_key, $user, Array("lab" => $lab))) return APIReturnData::quick403();

    return APIReturnData::build($lab, true);
}

?>
