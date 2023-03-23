<?php

function getAdditionalResourceTypes($user, $api_key) {
    return APIReturnData::build(Resource::allowedResourceTypes(), true);
}

?>
