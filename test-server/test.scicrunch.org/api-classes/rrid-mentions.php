<?php

function getRRIDMentions($user, $api_key, $query, $facets, $count, $offset) {
    if(!\APIPermissionActions\checkAction("rrid-mentions", $api_key, $user)) return APIReturnData::quick403();

    $mentions = RRIDMention::searchRRIDs($query, $facets, $count, $offset);

    return APIReturnData::build($mentions, true);
}

function getRRIDMentionsByRRID($user, $api_key, $rrid, $count, $offset) {
    if(!\APIPermissionActions\checkAction("rrid-mentions", $api_key, $user)) return APIReturnData::quick403();

    $mentions = RRIDMention::getByRRID($rrid, $count, $offset);
    $count = RRIDMention::getCountByRRID($rrid);
    $data = Array(
        "count" => $count,
        "rrid-mentions" => $mentions,
    );

    return APIReturnData::build($data, true);
}

?>
