<?php

class APIStratUserResourceMentionSubscription{
    private $request;

    public function __construct($request){
        $this->request = $request;
    }

    public function execute(){
        $allowed_actions = Array("subscribe", "unsubscribe", "subscribed");
        $rel_type = "mention_subscriber";

        $user = $this->request['user'];
        if(is_null($user)) throw new Exception("must be logged in");
        $rid_raw = $this->request['uri_args'][3];
        $rid = \helper\getIDFromRID($rid_raw);
        $resource = new Resource();
        $resource->getByID($rid);
        if(!$resource->id) throw new Exception("invalid resource ID");

        $method = $this->request['method'];
        $action = $this->request['uri_args'][2];
        if(!in_array($action, $allowed_actions)) throw new Exception("invalid action");
        $exists = ResourceUserRelationship::exists($resource->id, $user->id, $rel_type);

        if($method == "GET"){
            return $exists;
        }elseif($method == "POST"){
            if($action == "subscribe"){
                if($exists) throw new Exception("user is already subscribed");
                ResourceUserRelationship::createNewObj($resource->id, $user->id, $rel_type);
            }elseif($action == "unsubscribe"){
                if(!$exists) throw new Exception("user is already not subscribed");
                $rur = ResourceUserRelationship::loadBy(Array("rid", "uid", "type"), Array($resource->id, $user->id, $rel_type));
                if(is_null($rur)) throw new Exception("could not load subscription");
                ResourceUserRelationship::deleteObj($rur, $user->id);
            }
        }
    }
}

?>
