<?php
require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";

class Rel{
    public $r1;
    public $r2;
    public $count;
    public $comentions;
    public function __construct($r1, $r2, $count, $comentions){
        if($r1 < $r2){
            $this->r1 = $r1;
            $this->r2 = $r2;
        }else{
            $this->r1 = $r2;
            $this->r2 = $r1;
        }
        $this->count = $count;
        $this->comentions = $comentions;
    }
    public function __toString(){
        return $this->r1 . '-' . $this->r2;
    }
}


$cxn = new Connection();
$cxn->connect();

$rid = filter_var($_GET["rid"], FILTER_SANITIZE_STRING);
$count = filter_var($_GET["count"], FILTER_SANITIZE_NUMBER_INT);
$hc = isset($_GET["hc"]);
if($count > 20) $count = 20;
$id = \helper\getIDFromRID($rid);
$single_layer = isset($_GET["sl"]);

if($hc) $comentions = $cxn->select("resource_mention_relationships", Array("r1", "r2", "count_hc", "comentions_hc"), "iii", Array($id, $id, $count), "where count_hc > 0 and (r1=? or r2=?) order by count_hc desc limit ?");
else $comentions = $cxn->select("resource_mention_relationships", Array("r1", "r2", "count", "comentions"), "iii", Array($id, $id, $count), "where r1=? or r2=? order by count desc limit ?");
$comentionids = Array($id => cmidResource($id));
$relationships = Array();
foreach($comentions as $cm){
    if($cm['r1'] == $id) $cmid = $cm['r2'];
    else $cmid = $cm['r1'];
    $comentionids[$cmid] = cmidResource($cmid);
    if($hc) $relationships[] = new Rel($cm['r1'], $cm['r2'], $cm['count_hc'], $cm['comentions_hc']);
    else $relationships[] = new Rel($cm['r1'], $cm['r2'], $cm['count'], $cm['comentions']);
}
if(count($comentionids) > 1 && !$single_layer){
    $ak = array_keys($comentionids);
    $ids = Array();
    $arg_str_array = Array();
    $arg_types = "";
    for($i = 1; $i < count($comentionids); $i++){
        $ids[] = $ak[$i];
        $arg_str_array[] = "?";
        $arg_types .= "i";
    }
    $arg_str = "(" . implode(",", $arg_str_array) . ")";
    $ids = array_merge($ids, $ids);
    $arg_types = $arg_types . $arg_types;
    if($hc) $cms = $cxn->select("resource_mention_relationships", Array("r1", "r2", "count_hc", "comentions_hc"), $arg_types, $ids, "where count_hc > 0 and r1 in " . $arg_str . " and r2 in " . $arg_str);
    else $cms = $cxn->select("resource_mention_relationships", Array("r1", "r2", "count", "comentions"), $arg_types, $ids, "where r1 in " . $arg_str . " and r2 in " . $arg_str);

    foreach($cms as $cm){
        if($hc) $relationships[] = new Rel($cm['r1'], $cm['r2'], $cm['count_hc'], $cm['comentions_hc']);
        else $relationships[] = new Rel($cm['r1'], $cm['r2'], $cm['count'], $cm['comentions']);
    }
}
$cxn->close();

$reshaped_relationships = Array();
foreach($relationships as $rel){
    $reshaped_relationships[] = Array(
        "rid1" => $comentionids[$rel->r1]->rid,
        "rid2" => $comentionids[$rel->r2]->rid,
        "name1" => $comentionids[$rel->r1]->columns['Resource Name'],
        "name2" => $comentionids[$rel->r2]->columns['Resource Name'],
        "description1" => $comentionids[$rel->r1]->columns['Description'],
        "description2" => $comentionids[$rel->r2]->columns['Description'],
        "uuid1" => $comentionids[$rel->r1]->uuid,
        "uuid2" => $comentionids[$rel->r2]->uuid,
        "count" => $rel->count,
        "comentions" => $rel->comentions
    );
}

header("Content-Type: application/json");
echo json_encode($reshaped_relationships);
exit;

function cmidResource($id){
    $resource = new Resource();
    $resource->getByID($id);
    $resource->getColumns();
    return $resource;
}

?>
