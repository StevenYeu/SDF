<?php

function getAPIPermissionTypes(){
    return APIReturnData::build(APIKeyPermission::$allowed_permission_types, true);
}

?>
