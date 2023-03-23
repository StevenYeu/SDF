<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/term.class.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/connection.class.php";

    \helper\scicrunch_session_start();

    $cxn = new Connection();
    $cxn->connect();

    $cxn->update("term_notifications", "iis",Array("status"),Array(0, $_POST["user_id"], $_POST["term_ilx"]),"where uid=? and ilx=? and status=1");

    $cxn->close();

    $notification = new Notification();
    $notification->create(array(
        'sender' => 0,
        'receiver' => $_SESSION['user']->id,
        'view' => 0,
        'cid' => 0,
        'timed'=>0,
        'start'=>time(),
        'end'=>time(),
        'type' => 'delete-term-notification',
        'content' => 'Successfully unsubscribed "' . $_POST['term_name'] . '" notification'
    ));
    $notification->insertDB();
    $_SESSION['user']->last_check = time();
?>
