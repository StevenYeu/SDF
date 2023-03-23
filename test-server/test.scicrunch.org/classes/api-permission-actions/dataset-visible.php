<?php

return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
    $check_user = is_null($api_user) ? $user : $api_user;

    $dataset = $data["dataset"]; if(is_null($dataset)) return false;

    ///* check if dataset in a community associated with the user */
    //$submissions = CommunityDataset::loadArrayBy(Array("datasetid"), Array($dataset->id));
    //foreach($submissions as $sub) {
    //    if($sub->curated === CommunityDataset::CURATED_STATUS_APPROVED) {
    //        $community = new Community();
    //        $community->getByID($sub->cid);
    //        if($community->isVisible($check_user)) return true;
    //    } elseif(!is_null($check_user)) {
    //        if($check_user->levels[$sub->cid] > 1) return true;
    //    }
    //}

    if(is_null($check_user)) return false;

    /* check if user is dataset owner */
    if($check_user->id === $dataset->uid) return true;

    /* if dataset if public */
    if($dataset->lab_status === Dataset::LAB_STATUS_APPROVED) {
        return true;
    }

    /* if dataset approved by community */
    if($dataset->lab_status === Dataset::LAB_STATUS_APPROVEDCOMMUNITY && $dataset->lab()->community()->isMember($check_user)) {
        return true;
    }

    $lab = $dataset->lab();
    $level = LabMembership::getLevel($check_user, $lab);
    if($level === false) return false;

    if($level > 1) {
        /* check if user is moderator in lab */
        return true;
    } elseif($level === 1 && ($dataset->lab_status === Dataset::LAB_STATUS_APPROVEDINTERNAL)) {
        /* check if user is member and dataset is visible */
        return true;
    }

    return false;
}

?>
