<?php

function ilxUpdate($user, $api_key, $ilx_fragment, $term=NULL, $defining_url=NULL, $note=NULL){
        if(!\APIPermissionActions\checkAction("ilx-update", $api_key, $user)) return APIReturnData::quick403();

        $ilx_id = IlxIdentifier::loadBy(Array("fragment"), Array($ilx_fragment));
        if(is_null($ilx_id)) return APIReturnData::build(NULL, false, 400, "bad fragment");

        if(!is_null($term)) $ilx_id->term = $term;
        if(!is_null($defining_url)) $ilx_id->defining_url = $defining_url;
        if(!is_null($note)) $ilx_id->note = $note;
        $ilx_id->updateDB();

        return APIReturnData::build($ilx_id, true);
}

?>
