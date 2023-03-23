<?php

    return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
        if(!is_null($api_key)){
            $perm = $api_key->permissions(Array("term"));
            if(!is_null($perm["term"]) && $perm["term"]->active === 1) return true;
        }

        $check_user = is_null($api_user) ? $user : $api_user;
        if(is_null($check_user)) return false;
        //$status = DbObj::verifyUserCommunity($data["dbObj"], $check_user->id, $data["cid"]);
        //if(!($status["user_ok"] && $status["community_ok"] && $status["level_ok"])) return false;
        return true;
    }

?>
