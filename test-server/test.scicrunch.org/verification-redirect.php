<?php

if($_SESSION['user']){
    header("Location:/");
    exit;
}

$vars = array();
foreach($_GET as $key => $value){
    $vars[$key] = filter_var($value, FILTER_SANITIZE_STRING);
}

// get the referer and make sure the domain is a scicrunch owned domain
$referer = NULL;
if(isset($_GET["referer"])) $referer = \helper\checkReferer($_GET["referer"]);

if($vars['verstring']){
    $user = new User();
    $user->verifyUser($vars['verstring']);

    if($user->id) {
        if (isset($_GET['mailchimp'])) {
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
        }

        $_SESSION["user"] = $user;
        $print_message = "Thank you for registering.  You have verified your account.";
        if(!is_null($referer)) $print_message .= '<br/><br/>Click <a href="' . $referer . '">here</a> to return to the page you were working on.';
    } else {
        $print_message = "Thank you for registering.  You have verified your account and can now log in.";	// does not doublecheck for success, don't give extra information
    }
}elseif(!isset($_SESSION['nonuser'])){
    $print_message = \helper\loginForm("If you need the verification email resent, please enter your credentials");
}elseif($vars['type'] == "new"){
    // Manu: commented //$print_message = 'A verification email has been sent.  If you did not receive the email please check your spam folder or click <a href="/verification?type=resend">here</a> to resend it.';
    $print_message = 'A verification email will be sent shortly.';
    //if(!is_null($referer)) $print_message .= '<br/><br/>Click <a href="' . $referer . '">here</a> to return to the page you were working on.';
}elseif($vars['type'] == "sent"){
    $print_message = 'We cannot log you in until your email has been verified.  Click <a href="/verification?type=resend">here</a> to resend the verification email.</a>';
}elseif($vars['type'] == "resend"){
    $_SESSION['nonuser']->sendVerifyEmail();
    $print_message = "A verification email has been sent.  Please check your inbox (or spam folder).";
}


$tab = 0; $hl_sub = 0; $ol_sub = -1;

$holder = new Component();
$components = $holder->getByCommunity(56);  // Manu - changed from 0 to 56

$community = new Community();
$community->id = 56;    // Manu - changed from 0 to 56

$vars['editmode'] = filter_var($_GET['editmode'],FILTER_SANITIZE_STRING);
if($vars['editmode']){
    if(!isset($_SESSION['user']) || $_SESSION['user']->role<1)
        $vars['editmode'] = false;
}

$vars['errorID'] = filter_var($_GET['errorID'],FILTER_SANITIZE_NUMBER_INT);
if($vars['errorID']){
    $errorID = new Error();                                                                                                                                                                                 
    $errorID->getByID($vars['errorID']);
    if(!$errorID->id){
        $errorID = false;
    }
}

$locationPage = '/';

include $_SERVER['DOCUMENT_ROOT'] . '/verification/verification.php';

?>
