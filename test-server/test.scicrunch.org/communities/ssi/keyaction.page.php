<?php

$actiontype = $vars["actiontype"];

switch($actiontype) {
    case "email-unsubscribe":
        $message = "You have been unsubscribed from email notifications.";
        break;
    case "lab-create":
        $message = "The lab status has been updated.";
        break;
    case "lab-join":
        $message = "The lab user status has been updated.";
        break;
    default:
        $message = "We were unable to complete the action.";
        break;
}

?>

<div class="profile">
    <div class="container">
        <h4><?php echo $message ?></h4>
    </div>
</div>
