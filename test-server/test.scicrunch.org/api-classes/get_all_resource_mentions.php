<?php

function getAllResourceMentions($user, $api_key, $scrid, $count=NULL, $offset=NULL, $orderby=NULL, $confidence=NULL){
    if(is_null($count)) $count = MAXINT;
    if(is_null($offset)) $offset = 0;
    if(is_null($confidence)) $confidence = "high";
    $cuser = \APIPermissionActions\getUser($api_key, $user);

    $rid = \helper\getIDFromRID($scrid);
    $resource = new Resource();
    $resource->getByID($rid);
    $is_authorized_owner = is_null($cuser) ? false : $resource->isAuthorizedOwner($cuser->id);
    if(!$rid) return APIReturnData::build(NULL, false, 400, "invalid resource id");
    $resource_mentions = ResourceMention::factoryByRID($rid, $offset, $count, $orderby, $confidence, $is_authorized_owner);
    $frmt_mentions = formatMentions($resource_mentions, $cuser);

    return APIReturnData::build($frmt_mentions, true);
}

function formatMentions($resource_mentions, $cuser){
    $mention_data = getMentionData($resource_mentions);

    $frmt_mentions = Array();
    foreach($resource_mentions as $mention){
        $mention_id = $mention->getMentionID();
        $this_mention = Array("mention" => $mention_id);
        $key_mention_id = strtolower($mention_id);
        if(isset($mention_data[$key_mention_id])){
            $this_mention = array_merge($this_mention, $mention_data[$key_mention_id]);
        }
        $this_mention['vote_good'] = $mention->getVotes("good");
        $this_mention['vote_bad'] = $mention->getVotes("bad");
        $this_mention['rating'] = $mention->getRating();
        $this_mention['confidence'] = $mention->getConfidence();
        $this_mention["snippet"] = $mention->getSnippet();
        if(!is_null($cuser)) $this_mention['user_vote'] = $mention->getUserVote($cuser);
        $frmt_mentions[] = $this_mention;
    }
    return $frmt_mentions;
}

function getMentionData($resource_mentions){
    $pmid_xml = getPMIDXML($resource_mentions);
    $jpmids = jsonifyPMID($pmid_xml);
    $doi_data = getDOIData($resource_mentions);
    $jdois = jsonifyDOI($doi_data);

    return array_merge($jpmids, $jdois);
}

function getPMIDXML($pmids){
    if(count($pmids) == 0) return NULL;
    $base_url = Connection::environment() . "/v1/literature/pmid";

    $url_args = Array();
    foreach($pmids as $pmid){
        $pmid_split = ResourceMention::splitMention($pmid->getMentionID());
        if($pmid_split[0] == "PMID"){
            array_push($url_args, "pmid=" . $pmid_split[1]);
        }
    }
    $url_string = $base_url . "?" . implode("&", $url_args);
    $xml = simplexml_load_file($url_string);
    return $xml;
}

function jsonifyPMID($pmid_xml){
    if(is_null($pmid_xml)) return Array();
    $jpmid = Array();
    foreach($pmid_xml->publication as $pub){
        $pub_array = Array();

        // pmid_xml data
        $attr = $pub->attributes();
        $pub_array['id'] = "PMID:".(string) $attr->pmid;
        $pub_array['authors'] = (Array) $pub->authors->author;
        $pub_array['journal'] = (string) $pub->journal;
        $pub_array['journalShort'] = (string) $pub->journalShort;
        $pub_array['day'] = (int) $pub->day;
        $pub_array['month'] = (int) $pub->month;
        $pub_array['year'] = (int) $pub->year;
        $pub_array['title'] = (string) $pub->title;
        $pub_array['abstract'] = (string) $pub->abstract;
        $pub_array['url'] = "/" . str_replace("PMID:", "", $pub_array["id"]);

        $key = $pub_array['id'];
        $jpmid[strtolower($key)] = $pub_array;
    }
    return $jpmid;
}

function getDOIData($resource_mentions){
    $doi_data = Array();
    if(count($resource_mentions) === 0) return $doi_data;
    foreach($resource_mentions as $rm){
        $mention_split = ResourceMention::splitMention($rm->getMentionID());
        if($mention_split[0] === "DOI"){
            $doi_datum = \helper\getDOIJSON($mention_split[1]);
            if(!is_null($doi_datum)) $doi_data[] = $doi_datum;
        }
    }
    return $doi_data;
}

function jsonifyDOI($doi_data){
    $jdoi = Array();
    if(empty($doi_data)) return $jdoi;
    foreach($doi_data as $dd){
        $doi = Array();
        $doi['id'] = "DOI:".$dd["DOI"];
        $doi["authors"] = Array($dd["author"][0]["family"] . " " . $dd["author"][0]["given"]);
        $doi["journal"] = $dd["container-title"];
        $doi["journalShort"] = $dd["container-title"];
        $doi["day"] = $dd["issued"]["date-parts"][0][2];
        $doi["month"] = $dd["issued"]["date-parts"][0][1];
        $doi["year"] = $dd["issued"]["date-parts"][0][0];
        $doi["title"] = $dd["title"];
        $doi["abstract"] = NULL;
        $doi["url"] = "http://dx.doi.org/" . $doi["id"];

        $key = $doi["id"];
        $jdoi[strtolower($key)] = $doi;
    }
    return $jdoi;
}

?>
