<?php

    return function($api_key=NULL, $user=NULL, $api_user=NULL, $data=NULL){
        if(is_null($data)) return false;
        if(!is_null($api_key)){
            if($api_key->hasPermission("curator")) return true;
        }

        $check_user = is_null($api_user) ? $user : $api_user;
        if(is_null($check_user)) return false;
        $resource = $data;
        if($user->role > 0) return true;
        if(!$resource->isAuthorizedOwner($user->id)) return false;
        $resource->getColumns();
        $last_curated_version = $resource->getLastCuratedVersionNum();
        if($last_curated_version !== 0) {
            $previous_resource = new Resource();
            $previous_resource->getByID($resource->id);
            $previous_resource->getVersionColumns($last_curated_version);
        }
        $temp = new Resource_Fields();
        $resource_fields = $temp->getByType($resource->typeID, $resource->cid);
        $check_fields = Array();
        foreach($resource_fields as $rf) {
            if($rf->display === "owner-text") {
                if($last_curated_version !== 0) $previous_col = isset($previous_resource->columns[$rf->name]) ? $previous_resource->columns[$rf->name] : "";
                else $previous_col = "";
                $new_col = isset($resource->columns[$rf->name]) ? $resource->columns[$rf->name] : "";
                if($new_col !== $previous_col) return false;
            }
        }

        return true;
    }

?>
