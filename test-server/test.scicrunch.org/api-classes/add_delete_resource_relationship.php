<?php

/**
 * addDeleteResourceRelationship
 * @param User $user
 * @param ApiKey $api_key
 * @param String $add_del either "add" or "del"
 * @param String $scrid the scicrunch id of one of the ids
 * @param String $id1 the scicruch id of the left relationship
 * @param String $id2 the scicruch id of the right relationship
 * @param String $type the type of the id that is not the resource $scrid
 * @param String $relationship_string the string between the two ids
 */
function addDeleteResourceRelationship($user, $api_key, $add_del, $main_id, $id1, $id2, $type, $relationship_string){
    if(!\APIPermissionActions\checkAction("resource-edit-relationship", $api_key, $user)) return APIReturnData::quick403();
    $cuser = \APIPermissionActions\getUser($api_key, $user);
    $cuserid = is_null($cuser->id) ? 0 : $cuser->id;

    $rel_args = matchCanonID($main_id, $id1, $id2, $type);
    if(is_null($rel_args)) return APIReturnData::build(NULL, false, 400, "invalid ids");
    $relationship_type = ResourceRelationshipString::loadByStringAndTypes($relationship_string, $rel_args["type1"], $rel_args["type2"]);
    if(is_null($relationship_type)) {
        return APIReturnData::quick400("couldn't find relationship type");
    }
    if($relationship_string != $relationship_type->forward){ // using reverse string, so reverse the ids, types, canon
        \helper\swap($id1, $id2);
        $rel_args['canon_id'] = $rel_args['canon_id'] == 0 ? 1 : 0;
    }

    if($add_del == "add"){
        $rel = ResourceRelationship::createNewObj($cuser, $id1, $id2, $relationship_type, $rel_args["canon_id"]);
        if(is_null($rel)) {
            return APIReturnData::quick400("could not create resource relationship");
        }
        return APIReturnData::build($rel, true);
    }elseif($add_del = "del"){
        $rel = ResourceRelationship::loadBy(Array("id1", "id2", "reltype_id", "canon_id"), Array($id1, $id2, $relationship_type->id, $rel_args["canon_id"]));
        if(is_null($rel)) {
            return APIReturnData::quick400("could not find resource relationship");
        }
        ResourceRelationship::deleteObj($rel, $cuser);
        return APIReturnData::build(true, true);
    }else{
        return APIReturnData::build("invalid action");
    }
}

function matchCanonID($this_rid, $id1, $id2, $type){
    if($id1 == $this_rid && $id2 == $this_rid) return NULL;
    $res = Array();
    if($id1 == $this_rid){
        $res['type1'] = "res";
        $res['type2'] = $type;
        $res['canon_id'] = 0;
    }else{
        $res['type1'] = $type;
        $res['type2'] = "res";
        $res['canon_id'] = 1;
    }
    return $res;
}

?>
