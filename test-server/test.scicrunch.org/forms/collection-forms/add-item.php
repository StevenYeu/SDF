<?php

include '../../classes/classes.php';
\helper\scicrunch_session_start();

$vars['view'] = filter_var($_GET['view'],FILTER_SANITIZE_STRING);
$vars['uuid'] = filter_var($_GET['uuid'],FILTER_SANITIZE_STRING);
$vars['collection'] = filter_var($_GET['collection'],FILTER_SANITIZE_NUMBER_INT);
$vars['community'] = filter_var($_GET['community'],FILTER_SANITIZE_NUMBER_INT);

$community = new Community();
$community->getByID($vars["community"]);
if($community->rinStyle()) {
    $rrid_search_manager = ElasticRRIDManager::managerByViewID($vars["view"]);
}

$item = new Item();

if (!isset($_SESSION['user'])||!$_SESSION['user']->collections[$vars['collection']]||$item->checkExistence($_SESSION['user']->id,$vars['collection'],$vars['uuid'])) {
    exit();
}

$collection = $_SESSION['user']->collections[$vars['collection']];

if($vars["view"] == "literature") {
    $url = Connection::environment() . "/v1/literature/pmid?pmid=" . $vars["uuid"];
    $xml = simplexml_load_file($url);
    if($xml) {
        foreach($xml->publication as $pub) {
            $vars["snippet"] = $pub->asXML();
        }
    }
} elseif($vars["view"] == "view") {
    $holder = new Sources();
    $sources = $holder->getAllSources();
    if(!isset($sources[$vars["uuid"]])) exit;
    $source = $sources[$vars["uuid"]];
    $vars["snippet"] = "<view>";
    $vars["snippet"] .= "<title>" . $source->getTitle() . "</title>";
    $vars["snippet"] .= "<nif>" . $source->nif . "</nif>";
    $vars["snippet"] .= "<description>" . strip_tags($source->description) . "</description>";
    $vars["snippet"] .= "</view>";
} else {
    if($rrid_search_manager) {
        /* using the new rin elastic search services */
        $results = $rrid_search_manager->searchUUID($vars["uuid"]);
        if($results->hitCount() == 0) {
            exit;
        }
        $record = $results->getByIndex(0);
        $snippet_full = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><result><title>" . htmlentities($record->getRRIDField("name")) . "</title><nif>" . $vars["view"] . "</nif><description>" . htmlentities($record->snippet()) . "</description><citation>" . htmlentities($record->getRRIDField("proper-citation")) . "</citation><url>" . htmlentities($record->getRRIDField("url")) . "</url></result>";
        $vars["snippet"] = $snippet_full;
    } else {
        /* using old solr search services */
        $snippet = new Snippet();
        $snippet->getSnippetByView($vars['community'],$vars['view']);
        $snippet->resetter();

        $url = Connection::environment().'/v1/federation/data/'.$vars['view'].'.xml?exportType=all&filter=v_uuid:'.$vars['uuid'];
        $xml = simplexml_load_file($url);
        if($snippet->raw) {
            if($xml){
                foreach($xml->result->results->row->data as $data){
                    $snippet->replace((string)$data->name,(string)$data->value);
                }
            }
            $vars['snippet'] = $snippet->using;
        } else {
            $snippet_title = "";
            $snippet_description = "";
            if($xml) {
                $i = 0;
                foreach($xml->result->results->row->data as $data) {
                    if($i == 0) {
                        $snippet_title = (string) $data->value;
                    }
                    if($i != 0) $snippet_description .= "<br/>";
                    $snippet_description .= (string) $data->name . ": " . (string) $data->value;
                    $i++;
                }
            }
            $snippet_full = "<?xml version=\"1.0\" encoding=\"UTF-8\"?><result><title>" . htmlentities($snippet_title) . "</title><nif>" . $vars["view"] . "</nif><description>" . htmlentities($snippet_description) . "</description></result>";
            $vars["snippet"] = $snippet_full;
        }
    }
}


$vars['uid'] = $_SESSION['user']->id;

$item->create($vars);
$item->insertDB();

$_SESSION['user']->collections[$collection->id]->count++;

$notification = new Notification();
$notification->create(array(
    'sender' => 0,
    'receiver' => $_SESSION['user']->id,
    'view' => 0,
    'cid' => $vars['community'],
    'timed'=>0,
    'start'=>time(),
    'end'=>time(),
    'type' => 'collection-add',
    'content' => 'Successfully added the record to: ' . $collection->name
));
$notification->insertDB();
$_SESSION['user']->last_check = time();

echo json_encode(array('item'=>$item->id,'num'=>$collection->count));

?>
