<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/term.class.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/connection.class.php";

    \helper\scicrunch_session_start();

    $cxn = new Connection();
    $cxn->connect();

    if($_POST["term_notification"] == 1) {
        switch ($_POST["term_update"]) {
            case "daily":
                $next_notification_time = strtotime("+1 day");
                break;

            case "weekly":
                $next_notification_time = strtotime("+5 day");
                break;

            case "monthly":
                $next_notification_time = strtotime("+28 day");
                break;
        }
    } else $next_notification_time = strtotime("+5 day");
    $cxn->insert("term_notifications", "iissssisiiiiii",
                Array(NULL, $_POST["user_id"], $_POST["term_ilx"], $_POST["term_name"], $_POST["term_des"], "InterLex", $_POST["term_notification"],
                $_POST["term_update"], $_POST["term_follow_children"], 1, time(), time(), time(), $next_notification_time));

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
        'type' => 'add-term-notification',
        'content' => 'Successfully added "' . $_POST['term_name'] . '" notification'
    ));
    $notification->insertDB();
    $_SESSION['user']->last_check = time();
?>
