<?php

$docroot = "..";
require_once "../classes/classes.php";
$cxn = new Connection();
$cxn->connect();

$cxn->clearResourceMentionRelationships();

$all_resources = $cxn->select("resources", Array("id"), "", Array(), "");   // get all the resources
$resource_array = Array();
foreach($all_resources as $sr){
    $srid = $sr['id'];
    $mentions = ResourceMention::factoryByRID($srid);
    $other_resources = Array();
    foreach($mentions as $men){
        if($men->getRating() == "bad") continue;
        $other_resource_mentions = ResourceMention::factoryByMentionID($men->getMentionID(), NULL, "bad");
        foreach($other_resource_mentions as $orm){
            $ormrid = $orm->getRID();
            if($ormrid == $srid) continue;               // relationship with self
            if(isset($resource_array[$ormrid])) continue;    // recipricol relationship should already exist
            if(isset($other_resources[$ormrid])){
                $other_resources[$ormrid][] = $orm->getMentionID();
            }else{
                $other_resources[$ormrid] = Array($orm->getMentionID());
            }
        }
    }
    foreach($other_resources as $orid => $or){
        $cxn->insert("resource_mention_relationships", "iiiis", Array(NULL, $srid, $orid, count($or), implode(",", $or)));
    }
    $resource_array[$srid] = 1;
}

$cxn->close();

?>
