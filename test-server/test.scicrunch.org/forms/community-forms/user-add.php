<?php

include '../../classes/classes.php';
\helper\scicrunch_session_start();

$level = filter_var($_POST['level'],FILTER_SANITIZE_NUMBER_INT);
$uid = filter_var($_POST['id'],FILTER_SANITIZE_NUMBER_INT);
$cid = filter_var($_GET['cid'],FILTER_SANITIZE_NUMBER_INT);
$name = filter_var($_POST['name'],FILTER_SANITIZE_STRING);
$email = filter_var($_POST['email'],FILTER_SANITIZE_EMAIL);
$previousPage = $_SERVER['HTTP_REFERER'];

$community = new Community();
$community->getByID($cid);
if($name) {
    $splits = explode(' ', $name);
    if (count($splits) != 2 && !$uid) {
        $notification = new Notification();
        $notification->create(array(
            'sender' => 0,
            'receiver' => $_SESSION['user']->id,
            'view' => 0,
            'cid' => $community->id,
            'timed' => 0,
            'start' => time(),
            'end' => time(),
            'type' => 'user-add-fail',
            'content' => $name . ' is not a proper username.'
        ));
        $notification->insertDB();

        $_SESSION['user']->last_check = time();

        header('location:' . $previousPage);
        exit();
    }

    if (!$uid) {
        $user = new User();
        $user->getByName($splits);
        if (!$user->id) {
            $notification = new Notification();
            $notification->create(array(
                'sender' => 0,
                'receiver' => $_SESSION['user']->id,
                'view' => 0,
                'cid' => $community->id,
                'timed' => 0,
                'start' => time(),
                'end' => time(),
                'type' => 'user-add-fail',
                'content' => "No user found by the name:$name"
            ));
            $notification->insertDB();

            $_SESSION['user']->last_check = time();

            header('location:' . $previousPage);
            exit();
        }
        $uid = $user->id;
    }

    if (!isset($_SESSION['user']) || ($_SESSION['user']->levels[$cid] < $level && $_SESSION['role'] < 1)) {
        header('location:/');
        exit();
    }

    $community->join($uid, $name, $level);

    $levels[] = array('', 'User', 'Moderator', 'Administrator', 'Owner');

    $notification = new Notification();
    $notification->create(array(
        'sender' => 0,
        'receiver' => $_SESSION['user']->id,
        'view' => 0,
        'cid' => $community->id,
        'timed' => 0,
        'start' => time(),
        'end' => time(),
        'type' => 'user-add',
        'content' => 'Successfully updated ' . $name . ' to ' . $levels[$level]
    ));
    $notification->insertDB();

    $notification = new Notification();
    $notification->create(array(
        'sender' => $_SESSION['user']->id,
        'receiver' => $uid,
        'view' => 0,
        'cid' => $community->id,
        'timed' => 0,
        'start' => time(),
        'end' => time(),
        'type' => 'added-to-community',
        'content' => 'You were added to ' . $community->shortName . ' as a ' . $levels[$level]
    ));
    $notification->insertDB();

    $_SESSION['user']->last_check = time();

} elseif($email) {

    $to = $email;
    $subject = 'Invite to join '.$community->name;

    $message = Array(
        "You have been invited to join the ".$community->name." community within SciCrunch by ".$_SESSION['user']->getFullName(),
        "To accept this invitation go to: <a href='".$community->fullURL()."/join'>Join ".$community->shortName."</a> and create an account.",
    );

    $text_message = "You have been invited to join the " . $community->name . " community within SciCrunch by " . $_SESSION["user"]->getFullName() . ".  To accept this invitation go to: " . $community->fullURL() . "/join and create an account.";

    mail($to, $subject, $message, $headers);
    \helper\sendEmail($to, \helper\buildEmailMessage($message, 1, $community), $text_message, $subject, NULL);


    $notification = new Notification();
    $notification->create(array(
        'sender' => 0,
        'receiver' => $_SESSION['user']->id,
        'view' => 0,
        'cid' => $community->id,
        'timed' => 0,
        'start' => time(),
        'end' => time(),
        'type' => 'user-add',
        'content' => 'Successfully updated ' . $name . ' to ' . $levels[$level]
    ));
    $notification->insertDB();

    $_SESSION['user']->last_check = time();
}

$previousPage = $_SERVER['HTTP_REFERER'];
header('location:' . $previousPage);
?>
