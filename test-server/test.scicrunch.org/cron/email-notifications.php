<?php

$docroot = "..";
require_once $docroot . "/classes/classes.php";
$pending_uids = Subscription::uidsPendingEmails();

foreach($pending_uids as $uid){
    $user = new User();
    $user->getByID($uid);
    if(!$user->id || $user->subscribe_email !== 1) continue;

    $html_rows = Array();
    $text_rows = Array();
    $subs_mentions = Subscription::loadArrayBy(Array("uid", "type", "email_notify", "new_data_email"), Array($uid, "resource-mention", 1, 1));
    $subs_saved_searches_data = Subscription::loadArrayBy(Array("uid", "type", "email_notify", "new_data_email"), Array($uid, "saved-search-data", 1, 1));
    $subs_saved_searches_summary = Subscription::loadArrayBy(Array("uid", "type", "email_notify", "new_data_email"), Array($uid, "saved-search-summary", 1, 1));
    $subs_saved_searches_literature = Subscription::loadArrayBy(Array("uid", "type", "email_notify", "new_data_email"), Array($uid, "saved-search-literature", 1, 1));
    $subs_saved_searches = array_merge($subs_saved_searches_data, $subs_saved_searches_summary, $subs_saved_searches_literature);

    if(!empty($subs_mentions)){
        $message = '<h1>New resource mentions in the literature:</h1>';
        $html_rows[] = $message;
        $text_rows[] = $message;
        foreach($subs_mentions as $sub){
            $message_data = getMentionHTML($sub);
            $html_rows[] = $message_data["html"];
            $text_rows[] = $message_data["text"];
            $sub->resetNewDataEmail();
        }
    }

    if(!empty($subs_saved_searches)){
        $message = '<h1>New results in your saved searches:</h1>';
        $html_rows[] = $message;
        $text_rows[] = $message;
        $saved_searches_single_row_html = Array();
        $saved_searches_single_row_text = Array();
        foreach($subs_saved_searches as $sub){
            $message_data = getSavedSearchHTML($sub);
            $saved_searches_single_row_html[] = $message_data["html"];
            $saved_searches_single_row_text[] = $message_data["text"];
            $sub->resetNewDataEmail();
        }
        $html_rows[] = implode("<br/>", $saved_searches_single_row_html);
        $text_rows[] = implode(", ", $saved_searches_single_row_text);
    }

    $primary_community = $user->getPrimaryCommunity();

    if(!empty($html_rows)) {
        $html_rows[] = 'To unsubscribe from updates, visit your account page at <a href="https://scicrunch.org/' . $primary_community->portalName . '/account/edit">https://scicrunch.org/account/edit</a>';
        $text_rows[] = "To unsubscribe from updates, visit your account page at https://scicrunch.org/" . $primary_community->portalName . "/account/edit";
        $text_message = implode("\n", $text_rows);
        if(!$primary_community->id) $html_message = \helper\buildEmailMessage($html_rows);
        else $html_message = \helper\buildEmailMessage($html_rows, 1, $primary_community);
        \helper\sendEmail($user->email, $html_message, $text_message, $primary_community->name . " updates");
    }
}

function getMentionHTML($sub){
    $new_mentions = json_decode($sub->data_email, true);
    $new_mentions = $new_mentions["new_mentions"];
    $count_new_mentions = count($new_mentions);
    $view_mentions = array_slice($new_mentions, 0, 5);
    $html_mentions = Array();
    $resource = new Resource();
    $resource->getByRID($sub->fid);
    $resource->getColumns();
    $community = new Community();
    $community->getByID($sub->cid);
    $portal_name = $community->id ? $community->portalName : "scicrunch";
    $resource_link = "https://scicrunch.org/" . $portal_name . "/Any/record/nlx_144509-1/" . $resource->uuid . "/search?notif=" . $sub->id . "&notif_email";
    foreach($view_mentions as $vm){
        $single_mention_html = getSingleMentionHTML($vm, $resource->id, $resource_link);
        if(!is_null($single_mention_html)) $html_mentions[] = $single_mention_html;
    }

    $resource_name = $resource->columns["Resource Name"];
    $plural = $count_new_mentions > 1 ? "s" : "";

    $html = '<a href="' . $resource_link . '"><h2>' . $resource_name . '</h2></a> has ' . number_format($count_new_mentions) . ' new mention' . $plural;
    $html .= "<ul>";
    foreach($html_mentions as $hm){
        $html .= "<li>" . $hm . "</li>";
    }
    $text = $resource_name . '(' . $resource_link . ') has ' . number_format($count_new_mentions) . ' new mention' . $plural;

    return Array("html" => $html, "text" => $text);
}

function getSavedSearchHTML($sub){
    $data = json_decode($sub->data_email, true);
    $saved_search = new Saved();
    $saved_search->getByID($sub->fid);
    $saved_search_link = $saved_search->returnURL($sub->id);
    $comm = new Community();
    $comm->getByID($saved_search->cid);

    $html = '<h3>Your saved search <a href="' . $saved_search_link . '">' . $saved_search->name . '</a> for the query "' . $saved_search->query . '"';
    if($comm->id) $html .= ' in the community <a href="https://scicrunch.org/' . $comm->portalName . '">' . $comm->name . '</a>';
    else $html .= ' in <a href="https://scicrunch.org">SciCrunch</a>';
    $html .= " has new results</h3>";
    $text = $saved_search->name . '(' . $saved_search_link . ')';

    return Array("html" => $html, "text" => $text);
}

function getSingleMentionHTML($mention, $rid, $resource_link){
    $pmid = str_replace("PMID:", "", $mention);
    $data = \helper\sendGetRequest(Connection::environment() . "/v1/literature/pmid", Array("pmid" => $pmid));
    if(!$data) return NULL;
    $xml = simplexml_load_string($data);
    if(!$xml) return NULL;
    $pub_xml = $xml->publication;
    $title = (string) $pub_xml->title;
    $mention_info = $pub_xml->authors->author[0] . " | " . $pub_xml->journal . " | " . $pub_xml->year . " | <a href='" . $resource_link . "'>Help us verify this mention</a>";
    try {
        $mention_obj = new ResourceMention($rid, $mention);
        $snippet = $mention_obj->getSnippet();
    } catch(Exception $e) {
        $snippet = "";
    }
    return "<h3>" . $title . "</h3>" . $mention_info . "<br/>" . $snippet;
}

?>
