<?php

function getRRIDAlternateIDs($user, $api_key, $rrid, $count, $offset){
    if($count > 100 || $count < 0) $count = 100;
    if($offset < 0) $offset = 0;
    if(!is_null($rrid)) $rrid_maps = RRIDMap::loadArrayBy(Array("replace_by"), Array($rrid), false, $count, false, $offset);
    else $rrid_maps = RRIDMap::loadArrayBy(Array(), Array(), false, $count, false, $offset);
    return APIReturnData::build($rrid_maps, true);
}

?>
