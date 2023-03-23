<?php

include('../classes/classes.php');
\helper\scicrunch_session_start();

$join = $_GET['join'];

foreach ($_POST as $key => $value) {
    if($key == "g-recaptcha-response") {
        $captcha = $value;
    } elseif($key == "captcha-alt") {
        $alt_captcha = $value;
    } else {
        $vars[$key] = filter_var($value, FILTER_SANITIZE_STRING);
    }
}
$vars = \helper\sanitizeHTMLString($vars);

$cid = $_GET['cid'];

$user_check = new User();

// handle captcha

/* Manu

if(!isset($captcha) && !isset($alt_captcha)) {
    header("location: " . $_SERVER["HTTP_REFERER"]);
    exit;
}

Manu */


/* Manu
if(isset($captcha)) {
    $file = \helper\sendGetRequest('https://www.google.com/recaptcha/api/siteverify?secret=' . CAPTCHA_SECRET_KEY . '&response=' . $captcha . '&remoteip=' . \helper\getIP($_SERVER));
    
    $json = json_decode($file);
    if($json->success == false) {
        header("location: " . $_SERVER["HTTP_REFERER"]);
        exit;
    }
} else { // alt_captcha
    require_once __DIR__ . "/../lib/other/securimage/securimage.php";
    $securimage = new Securimage();
    if($securimage->check($alt_captcha) == false) {
        header("location: " . $_SERVER["HTTP_REFERER"] . "&altcaptchaerror");
        exit;
    }
}
Manu */
// check referer
$referer = "";
if(isset($vars["referer"])) {
    $referer = $vars["referer"];
}


if ($vars['password'] == $vars['password2'] && strlen($vars['password']) >= 6 && !$user_check->emailExists($vars['email']) && strlen($vars["email"]) >= 5) {
    $user = new User();
    $user->create($vars);
    $user->insertIntoDB();

    if (isset($vars['website'])) {
        UsersExtraData::createNewObj($user, "lab-website", array("url"=>$vars['website']));
    }

    // get rid of orcid session data
    if (isset($_SESSION['orcid']))
        unset($_SESSION['orcid']);

    $_SESSION['nonuser'] = $user;

    $previousPage = $_SERVER['HTTP_REFERER'];
    if ($join) {
        $community = new Community();
        $community->getByID($cid);
        if ($community->id) {
            if($community->private) {
                $new_request = CommunityAccessRequest::createNewObj($_SESSION["nonuser"], $community, "");
            } else {
                $community->join($user->id, $user->getFullName(), 1);
                $user->levels[$community->id] = 1;
                $notification = new Notification();
                $notification->create(array(
                    'sender' => 0,
                    'receiver' => $user->id,
                    'view' => 0,
                    'cid' => $community->id,
                    'timed'=>0,
                    'start'=>time(),
                    'end'=>time(),
                    'type' => 'join-community',
                    'content' => 'Successfully joined the community: '.$community->portalName
                ));
                $notification->insertDB();
                // $_SESSION['user']->last_check = time();
            }
        }
    }

    // pass the mailchimp flag if it was in the register form.
    if (isset($vars['mailchimp']))
        $user->sendVerifyEmail($referer . "&mailchimp=" . $vars['mailchimp']);
    else
        $user->sendVerifyEmail($referer);
 /*
        // if user exists, no need for verify email, so just add to mailchimp ...
        if ($user->verified != 0) {
            $user->sendVerifyEmail($referer);

            $community = new Community();
            $community->getByID($vars['mailchimp']);

            $httpCode = \helper\mailchimpPut($community->mailchimp_api_key, $community->mailchimp_default_list, "subscribed", $user->email, $user->firstname, $user->lastname);

            if ($httpCode == 200) {
                $notification = new Notification();
                $notification->create(array(
                    'sender' => 0,
                    'receiver' => $user->id,
                    'view' => 0,
                    'cid' => $community->id,
                    'timed'=>0,
                    'start'=>time(),
                    'end'=>time(),
                    'type' => 'join-mailinglist',
                    'content' => 'Successfully joined the mailing list: '.$community->portalName
                ));
                $notification->insertDB();
            }
        } else
*/        
}

error_log("**************************************************************************************** $cid **** $join",0);
// Manu -- uncommented the below

if ($cid && $join)
    header('location:/' . $community->portalName);
else
    header('location:/');

//$redirect = "/verification?type=new";
// Manu : commented above and added below
$redirect = "/verification?type=new&cid=$cid";
if($referer != "") $redirect .= "&referer=" . $referer;
header('location:' . $redirect);


?>
