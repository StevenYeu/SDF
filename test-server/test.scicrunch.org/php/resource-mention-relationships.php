<?php
require_once $_SERVER["DOCUMENT_ROOT"] . "/classes/classes.php";

class PRel{ // PRel - PsuedoRelationship
    private $id1;
    private $id2;
    private $edge;
    private $direction; // 0 - bi or no direction, -1 - id2 to id1, 1 - id1 to id2
    public function getID1(){ return $this->id1; }
    public function getID2(){ return $this->id2; }
    public function getEdge(){ return $this->edge; }
    public function getDirection(){ return $this->direction; }

    public function __construct($id1, $id2, $edge, $direction = 0){
        $this->id1 = $id1;
        $this->id2 = $id2;
        $this->edge = $edge;
        $this->direction = $direction;
    }
}

class ResourceMentionRelationships{
    private $checked_resources;
    private $unchecked_resources;
    private $checked_mentions;
    private $unchecked_mentions;

    public function __construct($resource_id){
        if(is_array($resource_id)) $this->unchecked_resources = $resource_id;
        else $this->unchecked_resources = Array($resource_id => 1);
    }

    public function searchLevels($lvls){
        for($i = 0; $i < $lvls; $i++){
            $this->searchUncheckedResources();
            $this->searchUncheckedMentions();
        }
    }

    private function searchUncheckedResources(){
        foreach(array_keys($this->unchecked_resources) as $ur){
            $mentions = ResourceMention::factoryByRID($ur);
            foreach($mentions as $men){
                $menid = $men->getMentionID();
                if(!isset($this->checked_mentions[$menid])){
                    $this->unchecked_mentions[$menid] = 1;
                }
            }
            $this->checked_resources[$ur] = 1;
        }
        $this->unchecked_resources = Array();
    }

    private function searchUncheckedMentions(){
        foreach(array_keys($this->unchecked_mentions) as $um){
            $mentions = ResourceMention::factoryByMentionID($um);
            foreach($mentions as $men){
                $rid = $men->getRID();
                if(!isset($this->checked_resources[$rid])){
                    $this->unchecked_resources[$rid] = 1;
                }
            }
            $this->checked_mentions[$um] = $mentions;
        }
        $this->unchecked_mentions = Array();
    }

    public function getMentions(){
        return $this->checked_mentions;
    }
}

/******************************************************************************************************************************************************************************************************/

$rid = filter_var($_GET['rid'], FILTER_SANITIZE_STRING);    // scr_id
$resource_id = \helper\getIDFromRID($rid);  // primary key of resource
$nlevels = 1;

$rmr = new ResourceMentionRelationships($resource_id);
$rmr->searchLevels($nlevels);
$resource_mentions = $rmr->getMentions();
$mention_relationships = relateResourceMentions($resource_mentions);

$resource_relationships = getAllResourceRelationships($resource_id, 1);

$all_relationships = reshapeRelationships(array_merge($mention_relationships, $resource_relationships));
header("Content-Type: application/json");
echo json_encode($all_relationships);
exit;

/******************************************************************************************************************************************************************************************************/

function relateResourceMentions($resource_mentions){
    $relationships = Array();
    foreach($resource_mentions as $menid => $rma){  // rma = $resource_mention_array, menid = mentionid
        if(count($rma) < 2) continue;
        for($i = 0; $i < count($rma) - 1; $i++){
            for($j = $i+1; $j < count($rma); $j++){
                $resource = new Resource();
                $resource->getByID($rma[$i]->getRID());
                $rid1 = $resource->rid;
                $resource->getByID($rma[$j]->getRID());
                $rid2 = $resource->rid;

                $relationships[] = new PRel($rid1, $rid2, $menid, 0);
            }
        }
    }
    return $relationships;
}

function getAllResourceRelationships($resource_id, $level){
    $relationships = Array();
    $raw_relationships = ResourceRelationship::factoryResourceByID($resource_id);
    foreach($raw_relationships as $rr){
        $rel_strings = ResourceRelationship::lookUpRelationshipString($rr->getRelTypeID());
        $id1 = ($rr->getCanonID() == 1) ? $rr->getID2() : $rr->getID1();
        $id2 = ($rr->getCanonID() == 1) ? $rr->getID1() : $rr->getID2();
        $rel = ($rr->getCanonID() == 1) ? $rel_strings['reverse'] : $rel_strings['forward'];
        $relationships[] = new PRel($id1, $id2, $rel, 1);
        if($level > 0){
            $next_id = \helper\getIDFromRID($id2);
            $relationships = array_merge($relationships, getAllResourceRelationships($next_id, $id2, $level - 1));
        }
    }
    return $relationships;
}

function reshapeRelationships($rels){
    $relationships = Array();
    foreach($rels as $rel){
        $relationships[] = Array(
            "id1" => $rel->getID1(),
            "id2" => $rel->getID2(),
            "edge" => $rel->getEdge(),
            "direction" => $rel->getDirection()
        );
    }
    return $relationships;
}

?>
