<?php

function addTermVote($user, $api_key, $args){
    $dbObj = new DbObj();
    if(!\APIPermissionActions\getUser($api_key, $user)) return "not allowed";

    if(!isset($args['table']) || $args['table'] == ''
        || !isset($args['table_id']) || $args['table_id'] == ''
        || !isset($args['vote']) || $args['vote'] == '') {
        return array("errormsg"=>"Missing required field(s).");
    }

    $uid = $user->id != "" ? $user->id : 0;
    $return = updateTermVote($dbObj, $uid, $args);
    //print_r($return);
    return $return;
}

function updateTermVote($dbObj, $uid, $args){

    //check if user has not voted on this property already
    //add to term_vote_logs
    $tr = new TermRelationship($dbObj);
    $ta = new TermAnnotation($dbObj);
    $tv = new TermVoteLogs($dbObj);
    $tv->getUserVote($uid, $args['table'], $args['table_id']);

    $result = array("errormsg"=>"", "action"=>"", "upvote"=>"", "downvote"=>"");
    if ($tv->id > 0){
        //print_r($votes);
        if ($tv->vote == $args['vote']) {
            $result["errormsg"] = "You have already voted '" . $args['vote'] . "' on this property.";
        } else {
            //change vote:
            //variables involved: table (term_relationships vs term_annotations),
            //vote type (upvote vs downvote),
            //change places (property tables and term_vote_logs table)

            $result["action"] = 'Changed vote ';
            switch ($args['table']) {
                case "term_relationships":
                    if ($args['vote'] == 'upvote') {
                        //increment upvote in term_relationships
                        //decrement downvote in term_relationship
                        //changeVote in term_vote_logs to upvote
                        $tr->incrementVote($args['table_id'], 'upvote');
                        $tr->decrementVote($args['table_id'], 'downvote');
                        $tv->changeVote('upvote');
                        $result["action"] .= "from downvote to upvote.";
                    }
                    if ($args['vote'] == 'downvote') {
                        //decrement upvote in term_relationship
                        //increment downvote in term_relationship
                        //changeVote in term_vote_logs to downvote
                        $tr->decrementVote($args['table_id'], 'upvote');
                        $tr->incrementVote($args['table_id'], 'downvote');
                        $tv->changeVote('downvote');
                        $result["action"] .= "from upvote to downvote.";
                    }
                    $result["table"] = 'term_relationships';
                    $result["upvote"] = $tr->upvote;
                    $result["downvote"] = $tr->downvote;
                    break;
                case "term_annotations":
                    if ($args['vote'] == 'upvote') {
                        //increment upvote in term_annotations
                        //decrement downvote in term_annotations
                        //changeVote in term_vote_logs to upvote
                        $ta->incrementVote($args['table_id'], 'upvote');
                        $ta->decrementVote($args['table_id'], 'downvote');
                        $tv->changeVote('upvote');
                        $result["action"] .= "from downvote to upvote.";
                    }
                    if ($args['vote'] == 'downvote') {
                        //decrement upvote in term_annotations
                        //increment downvote in term_annotations
                        //changeVote in term_vote_logs to downvote
                        $ta->decrementVote($args['table_id'], 'upvote');
                        $ta->incrementVote($args['table_id'], 'downvote');
                        $tv->changeVote('downvote');
                        $result["action"] .= "from upvote to downvote.";
                    }
                    $result["table"] = 'term_annotations';
                    $result["upvote"] = $ta->upvote;
                    $result["downvote"] = $ta->downvote;
                    break;
            }
        }
    } else {
        //add vote:
        //add to term_vote_logs
        $tv->uid = $uid;
        $tv->prop_table = $args['table'];
        $tv->prop_table_id = $args['table_id'];
        $tv->vote = $args['vote'];
        $tv->insertDB();

        $result["action"] = 'Added ' . $args['vote'];
        switch ($args['table']) {
            case "term_relationships":
                $tr->incrementVote($args['table_id'], $args['vote']);
                $result["table"] = 'term_relationships';
                $result["upvote"] = $tr->upvote;
                $result["downvote"] = $tr->downvote;
                break;
            case "term_annotations":
                $ta->incrementVote($args['table_id'], $args['vote']);
                $result["table"] = 'term_annotations';
                $result["upvote"] = $ta->upvote;
                $result["downvote"] = $ta->downvote;
                break;
        }
    }

    return $result;
}

?>
