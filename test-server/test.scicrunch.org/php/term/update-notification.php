<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/term.class.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/connection.class.php";

    \helper\scicrunch_session_start();

    $cxn = new Connection();
    $cxn->connect();

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
    $cxn->update("term_notifications", "isiii",
                  Array("send_notification", "update_type", "follow_children", "next_notification_time"),
                  Array($_POST["term_notification"], $_POST["term_update"], $_POST["term_follow_children"], $next_notification_time, $_POST["term_id"]),
                  "where id=?"
                );

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
        'type' => 'update-term-notification',
        'content' => 'Successfully updated "' . $_POST['term_name'] . '" notification'
    ));
    $notification->insertDB();
    $_SESSION['user']->last_check = time();
?>
