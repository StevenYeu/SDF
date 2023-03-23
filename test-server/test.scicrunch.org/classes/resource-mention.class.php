<?php

class ResourceMention extends Connection{
    static $GOOD_BAD_URL = "https://lucene2.ucsd.edu/rdw/service/resource/annot/save";
    static $NEW_PMID_URL = "https://lucene2.ucsd.edu/rdw/service/resource/paper/add";
    static $ALLOWED_MENTION_TYPES = Array("PMID", "PMCID", "DOI");
    const SOURCE_USER = "user";
    const SOURCE_RDW = "rdw";
    const SOURCE_RDW_RRID = "rdw_rrid";
    const SOURCE_RRID = "rrid";
    const RATING_GOOD = "good";
    const RATING_BAD = "bad";
    const RATING_NONE = "none";
    static $ALLOWED_INPUT_SOURCES = Array(self::SOURCE_USER, self::SOURCE_RDW, self::SOURCE_RDW_RRID, self::SOURCE_RRID);
    static $ALLOWED_RATINGS = Array(self::RATING_GOOD, self::RATING_BAD, self::RATING_NONE);

    private $id;
    private $rid;
    private $uid;
    private $mentionid;
    private $rating;
    private $timestamp;
    private $input_source;
    private $confidence;
    private $vote_sum;
    private $snippet;

    public function getMentionID(){ return $this->mentionid; }
    public function getRID(){ return $this->rid; }
    public function getID(){ return $this->id; }
    public function getTimestamp(){ return $this->timestamp; }
    public function getRating(){ return $this->rating; }
    public function getUID(){ return $this->uid; }
    public function getConfidence(){ return $this->confidence; }
    public function getVoteSum(){ return $this->vote_sum; }
    public function getSnippet(){ return $this->snippet; }

    public function __construct($rid, $mentionid){
        $this->rid = $rid;
        $this->mentionid = $mentionid;

        $this->connect();
        $id = $this->select("resource_mentions", array('id'), 'is', array($rid, $mentionid), 'where rid=? and mentionid=?');
        $this->close();

        if(count($id) != 1) throw new Exception("no mentions found");
        $this->getByID($id[0]['id']);
    }

    public function updateSnippet($snippet, $uid) {
        if($this->snippet === $snippet && $this->uid === $uid) return;
        $this->timestamp = time();
        $this->snippet = $snippet;
        $this->uid = $uid;
        $this->updateDBRating();
    }

    public function updateSource($new_source, $uid) {
        if($new_source == $this->input_source && $this->uid == $uid) return;
        if(!in_array($new_source, self::$ALLOWED_INPUT_SOURCES)) return;
        $this->timestamp = time();
        $this->input_source = $new_source;
        $this->uid = $uid;
        $this->updateDBRating();
    }

    public function updateRating($new_rating, $uid){
        if($this->rating == $new_rating && $this->uid == $uid) return;    // no need to update
        if(!in_array($new_rating, self::$ALLOWED_RATINGS)) throw new Exception("invalid rating");
        $this->timestamp = time();

        $old_rating = $this->rating;
        $this->rating = $new_rating;
        $this->uid = $uid;

        $this->notifyGoodBad($old_rating);
        $this->updateDBRating();
    }

    private function updateDBRating(){
        $this->connect();

        $old_val = $this->select("resource_mentions", Array("*"), "is", Array($this->rid, $this->mentionid), "where rid=? and mentionid=?");
        $old_val = implode("|", $old_val[0]);
        $this->insert("resource_mentions_history", "iisi", Array(NULL, $this->id, $old_val, $this->timestamp));
        $this->update("resource_mentions", "isissi", Array("uid", "rating", "timestamp", "snippet", "input_source"), Array($this->uid, $this->rating, $this->timestamp, $this->snippet, $this->input_source, $this->id), "where id=?");

        $this->close();
    }

    public function vote($vote, $uid){
        if(!in_array($vote, self::$ALLOWED_RATINGS)) throw new Exception("invalid vote");
        $this->connect();

        $timestamp = time();
        $existing = $this->select("resource_mention_voting", Array("id", "rating"), "ii", Array($this->id, $uid), "where rmid=? and uid=?");
        if(count($existing) > 0){
            $existing_id = $existing[0]['id'];
            $this->update("resource_mention_voting", "sii", Array("rating", "timestamp"), Array($vote, $timestamp, $existing_id), "where id=?");
        }else{
            $this->insert("resource_mention_voting", "iiisi", Array(NULL, $uid, $this->id, $vote, $timestamp));
        }
        $this->close();

        $this->addVoteSum($vote, $existing);

    }

    public function getVotes($vote){
        $this->connect();
        $result = $this->select("resource_mention_voting", Array("count(*)"), 'is', Array($this->id, $vote), "where rmid=? and rating=?");
        $this->close();

        $count = $result[0]['count(*)'];
        return $count;
    }

    static function newUserResourceMention($uid, $rid, $mentionid_raw, $rating, $input_source, $confidence, $snippet = NULL){
        $cxn = new Connection();
        $cxn->connect();

        #$input_source = $rdw ? self::SOURCE_RDW : self::SOURCE_USER;
        if(!in_array($input_source, self::$ALLOWED_INPUT_SOURCES)) {
            $input_source = self::SOURCE_USER;
        }
        $mentionid = self::convertToPMID($mentionid_raw);
        $mention_split = self::splitMention($mentionid);
        $mention_type = $mention_split[0];
        if(!in_array($mention_type, ResourceMention::$ALLOWED_MENTION_TYPES) || count($mention_split) < 2) throw new Exception("invalid mention type");
        $mention_suffix = $mention_split[1];

        $results = $cxn->select("resource_mentions", array("*"), "is", Array($rid, $mentionid), "where rid=? and mentionid=?");
        if(count($results) > 0) throw new Exception("resource mention already exists");

        $timestamp = time();
        $resource_mention = ResourceMention::newDBResourceMention($uid, $rid, $mentionid, $rating, $timestamp, $input_source, $confidence, $snippet);

        $cxn->close();

        return $resource_mention;
    }

    static function notifyNewPMID($original_id, $pmid){
        \helper\sendPostRequest(ResourceMention::$NEW_PMID_URL, Array("nifId" => $original_id, "pmid" => $pmid));
        return true;
    }

    private function notifyGoodBad($old_label){
        $mentionid_split = explode(":", $this->mentionid);
        if($mentionid_split[0] != "PMID" || count($mentionid_split) != 2) return;   // nothing to notify
        $pmid = $mentionid_split[1];
        $resource = new Resource();
        $resource->getByID($this->rid);
        $label = $this->rating;

        if($label != self::RATING_GOOD && $label != self::RATING_BAD) throw new Exception("invalid label");
        if($this->input_source === self::SOURCE_RDW || $old_label !== self::RATING_NONE){
            \helper\sendPostRequest(ResourceMention::$GOOD_BAD_URL, Array("nifId" => $resource->original_id, "pmid" => $pmid, "label" => $label));
        }else{
            ResourceMention::notifyNewPMID($resource->original_id, $pmid);
        }
    }

    static function newDBResourceMention($uid, $rid, $mentionid, $rating, $timestamp, $input_source, $confidence, $snippet){
        $cxn = new Connection();
        $cxn->connect();

        if(!in_array($rating, self::$ALLOWED_RATINGS)) throw new Exception("invalid rating");
        $old_val = $cxn->select("resource_mentions", Array("*"), "is", Array($rid, $mentionid), "where rid=? and mentionid=?");
        if(count($old_val) > 0) throw new Exception("resource already exists");
        $matches = Array();
        preg_match("/([0-9]+)/", $mentionid, $matches);
        if(isset($matches[0])) $mentionid_int = (int) $matches[0];
        else throw new Exception("poorly formatted PMID");
        if(!in_array($input_source, self::$ALLOWED_INPUT_SOURCES)) throw new Exception("unexpected input source");
        $rm_id = $cxn->insert("resource_mentions", "iiissiisdis", Array(NULL, $uid, $rid, $mentionid, $rating, $timestamp, $mentionid_int, $input_source, $confidence, 0, $snippet));
        if(!$rm_id) throw new Exception("could not add new resource mention");
        $resource_mention = new ResourceMention($rid, $mentionid);

        $cxn->close();

        return $resource_mention;
    }

    private function getByID($id){
        $this->connect();

        $matches = $this->select("resource_mentions", array("*"), "i", array($id), "where id=?");
        if(count($matches) != 1) throw new Exception("unexpected number of matches in getByID for resource_mentions");
        $this->id = $matches[0]['id'];
        $this->rid = $matches[0]['rid'];
        $this->mentionid = $matches[0]['mentionid'];
        $this->rating = $matches[0]['rating'];
        $this->timestamp = $matches[0]['timestamp'];
        $this->uid = $matches[0]['uid'];
        $this->input_source = $matches[0]['input_source'];
        $this->confidence = $matches[0]['confidence'];
        $this->vote_sum = $matches[0]['vote_sum'];
        $this->snippet = $matches[0]['snippet'];

        $this->close();
    }

    static function factoryByRID($rid, $offset=0, $count=MAXINT, $orderby=NULL, $confidence="all", $is_authorized_owner = false){
        $cxn = new Connection();
        $cxn->connect();
        if($orderby === "added_date") $sql_orderby = "timestamp";
        else $sql_orderby = "mentionid_int";
        if(!$is_authorized_owner) $no_bad_stmt = " and rating != 'bad'";
        else $no_bad_stmt = "";

        switch($confidence) {
        case "all":
            $where_string = "where rid=?" . $no_bad_stmt . " order by " . $sql_orderby . " desc limit ?, ?";
            break;
        case "low":
            $where_string = "where rid=?" . $no_bad_stmt . " and (((confidence <= 0.5 or vote_sum < 0) and rating != 'good') or rating = 'bad') order by " . $sql_orderby . " desc limit ?, ?";
            break;
        case "high":
            $where_string = "where rid=?" . $no_bad_stmt . " and (((confidence > 0.5 or vote_sum > 0) and rating != 'bad') or rating = 'good') order by " . $sql_orderby . " desc limit ?, ?";
            break;
        case "name":
            $where_string = "where rid=?" . $no_bad_stmt . " and confidence = 0.8 order by " . $sql_orderby . " desc limit ?, ?";
            break;
        case "url":
            $where_string = "where rid=?" . $no_bad_stmt . " and confidence = 1.0 and input_source='rdw' order by " . $sql_orderby . " desc limit ?, ?";
            break;
        case "verified":
            $where_string = "where rid=?" . $no_bad_stmt . " and rating = 'good' order by " . $sql_orderby . " desc limit ?, ?";
            break;
        case "rrid":
            $where_string = "where rid=? and input_source = '" . self::SOURCE_RRID . "' order by " . $sql_orderby . " desc limit ?, ?";
            break;
        default:
            throw new Exception("invalid confidence value");
        }

        $results = $cxn->select("resource_mentions", array("*"), "iii", array($rid, $offset, $count), $where_string);
        $cxn->close();

        $all_mentions = Array();
        foreach($results as $res){
            array_push($all_mentions, new ResourceMention($rid, $res['mentionid']));
        }
        return $all_mentions;
    }

    static function factoryByMentionID($mentionid, $rating = NULL, $not_rating = NULL, $offset=0, $count=MAXINT){
        $types = "s";
        $where = "where mentionid=?";
        $where_array = Array($mentionid);
        if($not_rating){
            $types .= "s";
            $where .= " and rating!=?";
            $where_array[] = $not_rating;
        }
        if($rating){
            $types .= "s";
            $where .= " and rating=?";
            $where_array[] = $rating;
        }
        array_push($where_array, $offset, $count);
        $types .= "ii";

        $cxn = new Connection();
        $cxn->connect();
        $results = $cxn->select("resource_mentions", array("*"), $types, $where_array, $where . " order by id desc limit ?, ?");
        $cxn->close();

        $all_mentions = Array();
        foreach($results as $res){
            $all_mentions[] = new ResourceMention($res['rid'], $mentionid);
        }
        return $all_mentions;
    }

    static function getCountByRID($rid, $confidence="low", $is_authorized_owner=false){
        if(!$is_authorized_owner) $no_bad_stmt = " and rating != 'bad'";
        else $no_bad_stmt = "";

        switch($confidence) {
        case "all":
            $where_string = "where rid=?" . $no_bad_stmt;
            break;
        case "low":
            $where_string = "where rid=?" . $no_bad_stmt . " and (((confidence <= 0.5 or vote_sum < 0) and rating != 'good') or rating = 'bad')";
            break;
        case "high":
            $where_string = "where rid=?" . $no_bad_stmt . " and (((confidence > 0.5 or vote_sum > 0) and rating != 'bad') or rating = 'good')";
            break;
        case "name":
            $where_string = "where rid=?" . $no_bad_stmt . " and confidence = 0.8";
            break;
        case "url":
            $where_string = "where rid=?" . $no_bad_stmt . " and confidence = 1.0 and input_source='rdw'";
            break;
        case "verified":
            $where_string = "where rid=?" . $no_bad_stmt . " and rating = 'good'";
            break;
        case "rrid":
            $where_string = "where rid=? and input_source = '" . self::SOURCE_RRID . "'";
            break;
        default:
            throw new Exception("invalid confidence value");
        }

        $cxn = new Connection();
        $cxn->connect();
        $results = $cxn->select("resource_mentions", Array("count(*)"), "i", Array($rid), $where_string);
        $cxn->close();

        return $results[0]['count(*)'];
    }

    public function getUserVote($user){
        $this->connect();
        $result = $this->select("resource_mention_voting", Array("rating"), 'ii', Array($user->id, $this->id), "where uid=? and rmid=?");
        $this->close();
        if(count($result) > 0) return $result[0]["rating"];
        return "";
    }

    public function arrayForm(){
        $resource = new Resource();
        $resource->getByID($this->rid);
        return Array(
            "rid" => $resource->rid,
            "original_id" => $resource->original_id,
            "mentionid" => $this->mentionid,
            "rating" => $this->rating,
            "confidence" => $this->confidence,
            "timestamp" => $this->timestamp,
            "snippet" => $this->snippet,
            "input_source" => $this->input_source,
        );
    }

    static public function convertToPMID($mentionid){
        $mention_split = ResourceMention::splitMention($mentionid);
        $prefix = $mention_split[0];
        if($prefix === "PMID") return $mentionid;

        $query_id = $mention_split[1];
        if($prefix === "PMCID") $query_id = "PMC".$query_id;
        $json_results = \helper\sendGetRequest("http://www.ncbi.nlm.nih.gov/pmc/utils/idconv/v1.0", Array(
            "tool" => "SciCrunch",
            "email" => urlencode("info@scicrunch.org"),
            "ids" => urlencode($query_id),
            "format" => "json"
        ));
        $results = json_decode($json_results, true);
        if(isset($results["records"]) && isset($results["records"][0]) && isset($results["records"][0]["pmid"])){
            return "PMID:".$results["records"][0]["pmid"];
        }else{
            return $mentionid;
        }
    }

    static public function splitMention($mentionid){
        $mentionid = str_replace(" ", "", $mentionid);
        $mention_split = explode(":", $mentionid);
        return $mention_split;
    }

    private function addVoteSum($vote, $existing){
        $addition = 0;
        if(!empty($existing)){
            if($existing[0]["rating"] === self::RATING_GOOD) $addition -= 1;
            elseif($existing[0]["rating"] === self::RATING_BAD) $addition += 1;
        }
        if($vote === self::RATING_GOOD) $addition += 1;
        elseif($vote === self::RATING_BAD) $addition -= 1;

        $this->connect();
        $this->update("resource_mentions", "ii", Array("vote_sum"), Array($this->vote_sum + $addition, $this->id), "where id = ?");
        $this->close();
    }

    public static function getRDWResources($pmid, $used_resources) {
        $full_pmid = "PMID:" . $pmid;
        $cxn = new Connection();
        $cxn->connect();
        $mentions = $cxn->select("resource_mentions", Array("rid"), "s", Array($full_pmid), "where mentionid = ? and (rating = 'good' or (rating != 'bad' and confidence > 0.5)) limit 50");
        $cxn->close();
        $fmt_mentions = Array();
        foreach($mentions as $mention) {
            $rrid_mention = "RRID:SCR_" . str_pad((string) $mention["rid"], 6, "0", STR_PAD_LEFT);
            if(!in_array($used_resources["rrids"])) {
                $fmt_mentions[] = $rrid_mention;
            }
        }
        return $fmt_mentions;
    }

}

?>
