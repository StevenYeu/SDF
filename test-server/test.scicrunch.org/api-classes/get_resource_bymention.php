<?php

function getResourceByMention($user, $api_key, $mentionid, $rating, $not_rating){
    $mentions = ResourceMention::factoryByMentionID($mentionid, $rating, $not_rating);
    $name_map = Resource::nameMap();
    $results = Array();
    if(!empty($mentions)){
        foreach($mentions as $men){
            $resource_name = $name_map[$men->getRID()];
            $resource_id = $men->getRID();
            $rating = $men->getRating();
            $resource = new Resource();
            $resource->getByID($resource_id);
            $results[] = Array(
                "resource_name" => $resource_name,
                "rid" => $resource->rid,
                "rating" => $rating,
                "uuid" => $resource->uuid
            );
        }
    }
    return APIReturnData::build($results, true);
}

?>
