<?php
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/classes.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/term.class.php";
    require_once $_SERVER['DOCUMENT_ROOT'] . "/classes/connection.class.php";

    $cxn = new Connection();
    $cxn->connect();
    $notification_count = $cxn->select("term_notifications", Array("id"), "si", Array($_POST["term_ilx"], $_POST["uid"]), "WHERE ilx=? and uid=? and status=1");
    if(count($notification_count))
        echo "<a class='btn btn-success' onclick='fetchNotification(".$notification_count[0]['id'].");'><i class='fa fa-cog'></i> Update Notifications</a>&nbsp;&nbsp;<a class='btn btn-primary' href data-target='#delete_notification_Modal' data-toggle='modal'><i class='fa fa fa-bell-slash-o'></i> Unsubscribe</a>";
    else
        echo "<a class='btn btn-success' href data-target='#add_notification_Modal' data-toggle='modal'><i class='fa fa-bell-o'></i> Subscribe Notification</a>";
    $cxn->close();
?>
