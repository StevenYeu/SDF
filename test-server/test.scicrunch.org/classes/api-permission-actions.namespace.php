<?php

namespace APIPermissionActions;

function allowedActions(){
    static $allowed_actions = Array(
        "ilx" => "ilx.php",
        "api-moderator" => "api-moderator.php",
        "subscribe_user" => "user.php",
        "search-for-users" => "search-for-users.php",
        "user-info" => "user-info.php",
        "api-user" => "api-user.php",
        "resource-owner" => "resource-owner.php",
        "resource-edit" => "resource-edit.php",
        "rrid" => "rrid.php",
        "mention-vote" => "user.php",
        "pmid-add" => "pmid-add.php",
        "entitymapping" => "user.php",
        "entitymapping-curate" => "curator.php",
        "term" => "term.php",
        "pending-owners" => "curator.php",
        "curate-resource" => "curate-resource.php",
        "community-visible" => "community-visible.php",
        "conversation-manage" => "curator.php",
        "conversation-create-curator" => "conversation-create-curator.php",
        "conversation-user" => "user.php",
        "add-resource" => "resource-adder.php",
        "conversation-member" => "conversation-member.php",
        "update-system-message" => "update-system-message.php",
        "dataset-manager" => "community-moderator.php",
        "dataset-user" => "dataset-owner.php",
        "dataset-owner" => "dataset-owner.php",
        "dataset-visible" => "dataset-visible.php",
        "get-resource-types" => "any.php",
        "change-resource-type" => "curator.php",
        "lab-member" => "lab-member.php",
        "lab-moderator" => "lab-moderator.php",
        "lab-change-level" => "lab-change-level.php",
        "submit-dataset" => "submit-dataset.php",
        "community-moderator" => "community-moderator.php",
        "community-member" => "community-member.php",
        "rrid-report-owner" => "rrid-report-owner.php",
        "dataservices-wrapper" => "dataservices-wrapper.php",
        "curator" => "curator.php",
        "resource-edit-relationship" => "resource-edit.php",
        "scigraph-wrapper" => "dataservices-wrapper.php",
        "update-resource-mention-snippet" => "resource-owner.php",
        "update-resource-mention-source" => "resource-mention-update.php",
        "api-key-add" => "api-moderator.php",
        "api-key-enable-disable" => "api-moderator.php",
        "api-key-update" => "api-user.php",
        "api-key-get" => "api-moderator.php",
        "rrid-update-alt" => "rrid.php",
        "entitymapping-add" => "user.php",
        "entitymapping-update" => "user.php",
        "ilx-add" => "ilx.php",
        "api-permission-update" => "api-moderator.php",
        "conversation-create" => "curator.php",
        "conversation-message" => "user.php",
        "conversation-add-remove-user" => "curator.php",
        "conversation-leave" => "user.php",
        "conversation-get-messages" => "user.php",
        "conversation-get-conversations" => "user.php",
        "conversation-check-existing" => "curator.php",
        "conversation-get-users" => "conversation-member.php",
        "pending-owners-review" => "curator.php",
        "pending-owners-get-all" => "curator.php",
        "rrid-report-add-item" => "rrid-report-owner.php",
        "rrid-report-delete-item" => "rrid-report-owner.php",
        "rrid-report-get-items" => "rrid-report-owner.php",
        "rrid-report-update" => "rrid-report-owner.php",
        "rrid-report-create-snapshot" => "rrid-report-owner.php",
        "rrid-report-new" => "user.php",
        "get-resource-owners" => "resource-owner.php",
        "mention-mark" => "resource-owner.php",
        "ilx-update" => "ilx.php",
        "get-community-snippet" => "community-visible.php",
        "get-scicrunch-data" => "moderator.php",
        "rrid-mentions" => "any.php",
        "community-datasets-required-fields-update" => "community-moderator.php",
        "community-datasets-required-fields-get" => "community-visible.php",
        "add-term-mapping" => "user.php",
        "get-term-mappings" => "any.php",
        "get-terms-by-community" => "community-visible.php",
        "term-curator" => "term-curator.php",
        "get-lab-id" => "user.php",
        "search-elastic" => "user.php",
    );

    return $allowed_actions;
}

function checkAction($action, $api=NULL, $user=NULL, $data=NULL, $log_data=NULL){
    $allowed_actions = allowedActions();
    $actionsroot = $GLOBALS["DOCUMENT_ROOT"] . "/classes/api-permission-actions/";
    $function_name = "\permissionCheck";
    if(!isset($allowed_actions[$action])) throw new \Exception("unrecognized action");
    $action_file = $allowed_actions[$action];

    $function = include $actionsroot . $action_file;
    $api_user = NULL;
    if(!is_null($api) && !$api->checkActive()) $api = NULL;
    $api_user = getUser($api, NULL);
    $action_allowed = $function($api, $user, $api_user, $data);
    unset($function);

    \APIKeyLog::createNewObj($api, $user, \helper\getIP($_SERVER), $action, $action_allowed);

    return $action_allowed;
}

// function used when just need user class, prefers API user over normal user argument
function getUser($api=NULL, $user=NULL){
    if(is_null($api) || !$api->checkActive()) return $user;
    $user_perm = $api->permissions(Array("user"));
    if(!is_null($user_perm["user"]) && $user_perm["user"]->active === 1){
        $api_user = new \User();
        $api_user->getByID($api->uid);
        if(!$api_user->id) return $user;
        return $api_user;
    }
    return $user;
}


?>
