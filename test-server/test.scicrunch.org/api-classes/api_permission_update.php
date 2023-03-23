<?php

function APIPermissionUpdate($user, $api_key, $key_val, $action, $permission_type, $permission_data=NULL){
        $key_obj = APIKey::loadBy(Array("key_val"), Array($key_val));
        if(is_null($key_obj)) return APIReturnData::build(NULL, false, 400, "bad key");

        if(!\APIPermissionActions\checkAction("api-permission-update", $api_key, $user)) return APIReturnData::quick403();

        if($action === "add"){
            try{
                $key_obj->addPermission($permission_type, $permission_data);
            }catch(Exception $e){
                if($e->getMessage() !== "permission already exists for this key") throw $e;
            }
        }elseif($action === "enable" || $action == "disable"){
            $key_obj->enableDisablePermission($permission_type, $action);
        }elseif($action === "dataupdate"){
            $key_obj->updatePermissionData($permission_type, $permission_data);
        }

        return APIReturnData::build($key_obj, true);
}

?>
