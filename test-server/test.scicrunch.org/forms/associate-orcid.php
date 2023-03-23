<?php

require_once __DIR__ . "/../classes/classes.php";
\helper\scicrunch_session_start();

/* There are now 4 cases where this page will be used.
    1. no $cid; // original case where user is registered, but didn't come from community page
    2. $cid=97; // original case where user wants to associate id from account page
    3. $cid=97/join; // new case where guest is on a community's register page
    4. $cid=97/anything else // new case where user is logged in and clicks "Join Community"
*/

$valid = 0;
if (!isset($_GET['cid'])) {
    // case #1
    $valid = 1;
} elseif (is_numeric($_GET['cid']) && is_int($_GET['cid'] + 0)) {
    // case #2
    $valid = 2;
    $cid = $_GET['cid'];
}

// if here, then dealing with join community request, case 3 or 4
if (!$valid) {
    preg_match("/(\d*)\/(.*)/", $_GET['cid'], $matches);

    if (!(isset($matches[2]))) {
        // didn't have / after cid
        header("location:/");
    }
    elseif ($matches[2] == 'join') {
        // case #3
        $valid = 3;
        $cid = $matches[1];
    } else {
        // case #4
        $valid = 4;
        $cid = $matches[1];
    }
}


if ($valid) {
    // get orcid
    $orcid_token_url = "https://orcid.org/oauth/token";
    $orcid_token_data = Array(
        "client_id" => ORCID_CLIENT_ID,
        "client_secret" => ORCID_CLIENT_SECRET,
        "grant_type" => "authorization_code",
        "redirect_uri" => PROTOCOL . "://" . \helper\httpHost() . "/forms/associate-orcid.php",
        "code" => $_GET["code"],
    );
    if(isset($_GET["cid"])) $orcid_token_data["redirect_uri"] .= "?cid=" . $_GET['cid'];
    $orcid_token_header = Array(
        "Accept: application/json",
    );

    $json_response = \helper\sendPostRequest($orcid_token_url, $orcid_token_data, $orcid_token_header);
    $response = json_decode($json_response, true);

    if (in_array($valid, array(1, 2, 4))) {
        /* link an existing user with the orcid id */
        if(isset($response["orcid"]) && is_null($_SESSION["user"]->orcid_id)) {
            /* link an existing user with the orcid id */
            $orcid_id = $response["orcid"];
            $_SESSION["user"]->orcid_id = $orcid_id;
            $_SESSION["user"]->updateField("orcid_id", $orcid_id);
            $_SESSION["user"]->updateORCIDData();
        }
    }

    // first two cases were just to associate orcid, no need to get orcid json data
    if (in_array($valid, array(1, 2))) {
        if(!isset($_GET["cid"]) || $_GET["cid"] == 0) {
            header("location:/account");
        } else {
            $community = new Community();
            $community->getByID($_GET["cid"]);
            $portal_name = Community::getPortalName($community);
            header("location:/" . $portal_name . "/account");
        }
    } else {
        // redirected back to a form, so need to push data into $_SESSION
        $_SESSION['orcid']['id'] = $response["orcid"];

        // server cache should have access token or will get one ...
        $access_token = \helper\getOrcidOauthAccessToken();

        $API_url = 'https://pub.orcid.org/v3.0/' . $_SESSION['orcid']['id'];
        $end_point = '/record';
        $orcid_token_header = Array(
            "Accept: application/json",
            "Authorization type: Bearer",
            "Access token: " . $response['access_token']
        );

        // record endpoint gives us everything. parsing for name, org, works hopefully will be faster than 3 separate calls ...
        $json_response = \helper\sendGetRequest($API_url . $end_point, $response["access_token"], $orcid_token_header);
        $response = json_decode($json_response, true);

        $_SESSION['orcid']['firstname'] = $response['person']['name']['given-names']; // use ['given-names'] and ['family-name']
        $_SESSION['orcid']['lastname'] = $response['person']['name']['family-name']; // use ['given-names'] and ['family-name']

        $ag = $response['activities-summary']['employments']['affiliation-group'];
        foreach ($ag as $group) {
            foreach ($group['summaries'] as $gf) {
                if (is_null($gf['employment-summary']['end-date'])) {
                    $_SESSION['orcid']['organization'] = $gf['employment-summary']['organization']['name'];
                    break 2;
                }
            }
        }

        $ag = $response['activities-summary']['works']['group'];
        $_SESSION['orcid']['works_count'] = sizeof($ag);
    }    

    $community = new Community();
    $community->getByID($cid);
    $portal_name = Community::getPortalName($community);

    if (strpos($matches[2], "?") !== FALSE)
        header("location:/" . $portal_name . "/" . str_replace($portal_name, "", $matches[2]) . "&from=joinModal");
    else
        header("location:/" . $portal_name . "/" . str_replace($portal_name, "", $matches[2]) . "?from=joinModal");

    exit;
    header("location:/" . $portal_name . "/join");
}

?>
