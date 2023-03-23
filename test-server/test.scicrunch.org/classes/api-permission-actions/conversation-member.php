<?php

    return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
        $check_user = is_null($api_user) ? $user : $api_user;
        if(is_null($check_user)) return false;

        $conversation_id = $data["conversation_id"];
        $conversation_user = UserMessageConversationUser::loadBy(Array("uid", "conversation_id"), Array($check_user->id, $conversation_id));
        if(!is_null($conversation_user)) return true;

        return false;
    }

?>
