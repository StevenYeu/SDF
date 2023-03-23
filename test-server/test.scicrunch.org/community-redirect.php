<?php

foreach ($_GET as $key => $value) {
    if ($key == 'filter' || $key == 'facet') {
        $checked_values = Array();
        foreach($value as $v) {
            $v_split = explode(":", $v);
            if(count($v_split) < 2 || $v_split[0] == "") continue; // dont allow empty filters
            $checked_values[] = $v;
        }
        $vars[$key] = filter_var_array($checked_values);
    } else {
        $vars[$key] = filter_var(rawurldecode($value), FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
    }
}

if ($vars['q'] == 'undefined') {
   $vars['q'] = $vars['l'];

   if (strpos($vars['q'],'{')) {
       $bracketStartPos = strpos($vars['q'],'{') + 1;
       $bracketEndPos = strpos($vars['q'],'}') - 1;

       $vars['q'] = substr($vars['q'], $bracketStartPos, ($bracketEndPos - $bracketStartPos + 1));
   }

}

if (isset($vars['errorID'])) {
    $errorID = new Error();
    $errorID->getByID($vars['errorID']);
}

if (!$vars['type'])
    $vars['type'] = 'home';
if ((!isset($vars['page']) || !$vars['page']) && $vars['type'] != 'account')
    $vars['page'] = 1;
if (!isset($vars['q']) || !$vars['q'])
    $vars['q'] = '*';

if (!isset($_SESSION['communities']) || !isset($_SESSION['communities'][$vars['portalName']])) {
    $community = new Community();
    $community->getByPortalName($vars['portalName']);

    if($community->isArchived()) {
        \helper\errorPage("archived", $vars["portalName"]);
    }

    if (!$community->id && $community->id !== 0) {
        $cxn = new Connection();
        $cxn->connect();
        $alt_portal = $cxn->select("communities", Array("*"), "s", Array($vars["portalName"]), "where altPortalName=?");
        $cxn->close();
        if(empty($alt_portal)) {
            \helper\errorPage("404", $vars["portalName"]);
        } else {
            header("location:/" . $alt_portal[0]["portalName"]);
            exit;
        }
    }

    $community->getCategories();
    $holder = new Component();
    $components = $holder->getByCommunity($community->id);

    $community->components = $components;
    $_SESSION['communities'][$vars['portalName']] = $community;
} else {
    $community = $_SESSION['communities'][$vars['portalName']];
}

// check if community should redirect
if($community->shouldHttpHostRedirect($_SERVER["HTTP_X_FORWARDED_HOST"])){  // always returns false, disabled for now
    header("location: " . $community->httpHostRedirectURL($_SERVER["REQUEST_URI"]), true, 301);
    exit;
}

if ($vars['editmode']) {
    if (!isset($_SESSION['user']) || $_SESSION['user']->levels[$community->id] < 2)
        $vars['editmode'] = false;
}

/* make sure page is visible */
if(!$community->isPageVisible($_SESSION["user"], $vars["type"], $vars["title"])) \helper\errorPage("private", $vars["portalName"]);

// parse current and referer url
if ($urlParts = parse_url($_SERVER['HTTP_REFERER'])) {
    $baseurl_referer = $urlParts["host"];
    $url_referer = $_SERVER['HTTP_REFERER'];    //referer full url -- Vicky-2019-1-14
}

// get the requested host name
if (isset($_SERVER['HTTP_X_FORWARDED_HOST']))
    $baseurl_curr = $_SERVER['HTTP_X_FORWARDED_HOST'];
else
    $baseurl_curr = $_SERVER['HTTP_HOST'];

// make array of those four domains and if in the array and doesnt equal then pop up
$domain_found = in_array($baseurl_referer, array("dknet.org", "neuinfo.org", "scicrunch.org", "drugdesigndata.org"));


// if the user is being directed to a new community show modal
$c_pop_up_flag =  !$domain_found || strpos($baseurl_curr, $baseurl_referer) === 0 || (isset($_COOKIE['c_pop_up_warning']));

## no pop up when switched 'dknet' and 'dknet beta' -- Vicky-2019-1-14
## no pop between dknet and dknet legacy after switch
if ($baseurl_referer == "dknet.org" && $vars["portalName"] == "legacy-niddk") $c_pop_up_flag = true;
else if (strpos($url_referer, "legacy-niddk") !== false && $vars["portalName"] == "dknet") $c_pop_up_flag = true;

if ($baseurl_referer == "scicrunch.org" && $vars["portalName"] == "dknet") $c_pop_up_flag = true;
else if (strpos($url_referer, "dknet") !== false && $vars["portalName"] == "dknet") $c_pop_up_flag = true;

$c_pop_up_array_t = Array("text" => "true", "name" => ($community->name), "curr" => $baseurl_curr, "referer" => $baseurl_referer);
$c_pop_up_array_f = Array("text" => "false", "name" => ($community->name), "curr" => $baseurl_curr, "referer" => $baseurl_referer);

if ($vars['type'] == 'home')
    include 'communities/home.php';
elseif ($vars['type'] == 'account')
    include 'communities/profile.php';
elseif ($vars['type'] == 'search')
    include 'communities/sdf-search.php';
elseif ($vars['type'] == 'datasets' ||
        $vars['type'] == 'dataset' ||
        $vars['type'] == 'lab' ||
        $vars["type"] == "community-labs" ||
        $vars["type"] == "data" ||
        $vars["type"] == "rrid-report" ||
        $vars["type"] == "rin"
    )
    include 'communities/other-pages.php';
elseif ($vars['type'] == 'interlex')
    include 'communities/interlex.php';
elseif ($vars['type'] == 'resource-watch')
    include 'communities/resource-watch.php';
elseif ($vars['type'] == 'about') {
    if($vars["title"] == "resource" ||
       $vars["title"] == "resourcementionupload" ||
       $vars["title"] == "resourcesedit"
    ) {
        include 'communities/resource.php';
    } elseif($vars["title"] == "faq") {
        include 'communities/faqs.php';
    } elseif($vars["title"] == "sources" ||
             $vars["title"] == "keyaction"
    ) {
        include 'communities/pages.php';
    } elseif($vars["title"] == "registry") {
        include 'communities/registry.php';
    } elseif($vars["title"] == "search") {
        include 'communities/content-search.php';
    } elseif($vars["title"] == "join-request-confirm" ||
             $vars["title"] == "join-request-response" ||
             $vars["title"] == "join-request-response-expired"
    ) {
        include "communities/message-page.php";
    } else {
        include 'communities/component-data.php';
    }
} elseif ($vars['type'] == 'join') {
    include 'communities/join.php';
} elseif ($vars['type'] == 'virtual-booth') {
    include "communities/ssi/virtual-booth.php";
}


?>
