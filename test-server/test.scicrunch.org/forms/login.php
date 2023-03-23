<?php
include('../classes/classes.php');
\helper\scicrunch_session_start();
$join = $_GET['join'];
$cid = $_GET['cid'];
$mailchimp = $_GET['mailchimp'];

if (!$_SESSION['user']) {
    $email = filter_var($_POST['email'], FILTER_SANITIZE_STRING);
    $password = filter_var($_POST['password'], FILTER_SANITIZE_STRING);

    if(!Error::okayLoginFrequency(\helper\getIP($_SERVER), time())) {
        $error = Error::staticCreate(Array(
            "type" => "login-fail",
            "message" => "We have received too many requests from this location, please try again in a few minutes",
            'hiddenmessage' => \helper\getIP($_SERVER),
        ));
        goBackError($error);
    }
    $user = new User();
    $user->login($email, $password);
    // Manu -- commented below
    //if ($user->id && $user->verified == 0){
    if ($user->id && $user->verified == 2){
        $_SESSION['nonuser'] = $user;
        header("location:/verification?type=sent");
        exit;
    } elseif ((int) $user->banned === 1) {
        $error = Error::staticCreate(Array("type" => "login-fail", "message" => "This account has been disabled"));
        $previous_page = $_SERVER["HTTP_REFERER"];
        $get_delim = strpos($previous_page, "?") === false ? "?" : "&";
        header("location:" . $_SERVER["HTTP_REFERER"] . $get_delim . "errorID=" . $error->id);
        exit;
    } elseif ($user->id) {
        $_SESSION['user'] = $user;
        $_SESSION['user']->last_check = $_SESSION['user']->updateOnline();
        $_SESSION['user']->last_check = time();
        $_SESSION['user']->onlineUsers = $user->getOnlineUsers();

        $user_extra_data = UsersExtraData::getWebsiteByUser($user);
        if ($user_extra_data) {
            foreach($user_extra_data as $ued) {
                $web = $ued->value;
                $_SESSION['user']->website = $web['url'];
            }
        }
    } else {
        $error = Error::staticCreate(Array(
            'type' => 'login-fail',
            'message' => 'That email/password combination does not exist, please try again.',
            'hiddenmessage' => \helper\getIP($_SERVER),
        ));

        goBackError($error);
    }
}

// if have $cid, get the community object
if ($cid) {
    $community = new Community();
    $community->getByID($cid);
}    

if ($join && (!$_SESSION['user']->levels[$cid] || $_SESSION['user']->levels[$cid] == 0)) {
    if($community->id) {
        if($community->private) {
            if (isset($_SESSION['orcid'])) {
                $_SESSION["user"]->orcid_id = $_SESSION['orcid']['id'];
                $_SESSION["user"]->updateField("orcid_id", $_SESSION['orcid']['id']);
                $_SESSION["user"]->updateORCIDData();

                // get rid of orcid session data
                if (isset($_SESSION['orcid']))
                    unset($_SESSION['orcid']);
            }

            if (isset($_POST['organization']) && ($_POST['organization'] !== $_SESSION['user']->organization)) {
                $_SESSION["user"]->updateField("organization", $_POST['organization']);
            }

            if (isset($_POST['website']) && ($_POST['website'] !== $_SESSION['user']->website)) {
                UsersExtraData::createNewObj($_SESSION['user'], "lab-website", array("url"=>$_POST['website']));
                $_SESSION['user']->website = $_POST['website'];
            }

            $new_request = CommunityAccessRequest::createNewObj($_SESSION["user"], $community, "");
            header("location:/" . $community->portalName . "/about/join-request-confirm");
            exit;
        } else {
            // if level exists, then use updateUser rather than join
            if (isset($_SESSION['user']->levels[$cid]))
                $community->updateUser($_SESSION['user']->id, 1);
            else
                $community->join($_SESSION['user']->id, $_SESSION['user']->getFullName(), 1);

            $_SESSION['user']->levels[$community->id] = 1;
            $notification = new Notification();
            $notification->create(array(
                'sender' => 0,
                'receiver' => $_SESSION['user']->id,
                'view' => 0,
                'cid' => $community->id,
                'timed'=>0,
                'start'=>time(),
                'end'=>time(),
                'type' => 'join-community',
                'content' => 'Successfully joined the community: ' . $community->portalName
            ));
            $notification->insertDB();
            $_SESSION['user']->last_check = time();
        }

        // if $_POST['mailchimp'], then community has default mailchimp list, so add user to list
        // if $_GET['mailchimp'] , then community wants to auto opt in, so add user to list
        if (!is_null($_POST['mailchimp']) || $mailchimp) {
            $httpCode = \helper\mailchimpPut($community->mailchimp_api_key, $community->mailchimp_default_list, "subscribed", $_SESSION['user']->email, $_SESSION['user']->firstname, $_SESSION['user']->lastname);

            // 200 indicates successful action
            if ($httpCode == 200) {
                $notification = new Notification();
                $notification->create(array(
                    'sender' => 0,
                    'receiver' => $_SESSION['user']->id,
                    'view' => 0,
                    'cid' => $community->id,
                    'timed'=>0,
                    'start'=>time(),
                    'end'=>time(),
                    'type' => 'join-community-mailchimp',
                    'content' => 'Successfully added to the community mailing list: ' . $community->portalName
                ));
                $notification->insertDB();
                $_SESSION['user']->last_check = time();    
            }
        }
    }
}

// if member of ODC community, redirect to "dashboard"
// else proceed as usual ...
if ((isset($_GET['cid'])) && (in_array($_GET['cid'], $config["odc-communities"]))) {
    if ($community->isMember($_SESSION["user"])) {
        $redirect = 'location:' . $community->fullURL() . "/community-labs/dashboard";
 
        $main_lab = Lab::getUserMainLab($_SESSION["user"], $community->id);
        if ($main_lab)
            $redirect .= "?labid=" . $main_lab->id;

        header($redirect);
     } else
        header('location:' . $community->fullURL());
     
} else {
    if (isset($_SERVER["page_path"])) 
        $previousPage = \helper\getPreviousPage($_SERVER["page_path"]);
    else 
        $previousPage = $_SERVER['HTTP_REFERER'];

    header('location:' . $previousPage);
}

function goBackError($error) {
    $previousPage = $_SERVER['HTTP_REFERER'];
    $ques = explode('?',$previousPage);
    if(count($ques)>1){
        header('location:' . $previousPage.'&errorID='.$error->id);
    } else {
        header('location:' . $previousPage.'?errorID='.$error->id);
    }
    exit;
}
?>
