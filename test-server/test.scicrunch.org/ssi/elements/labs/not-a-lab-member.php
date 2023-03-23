<?php

$user = $data["user"];
$lab = $data["lab"];

if(!$user || !$lab) {
    return;
}

$lab_membership = LabMembership::loadBy(Array("uid", "labid"), Array($user->id, $lab->id));

?>

<div>
    <h2>
        <?php echo $lab->name ?>
    </h2>
    <?php if($lab_membership): ?>
        <h4>
            You have requested membership to this lab.  Please wait for a response from the lab PI or lab manager.  An email will be sent after the lab PI or manager reviews your request.
        </h4>
    <?php else: ?>
        <h4>
            You are not a member of this lab.
            Would you like to
            <a class="lab-link" href="/forms/community-forms/lab-user-join.php?request=<?php echo $lab->id ?>&labid=<?php echo $lab->id ?>">
                request access
            </a>
            ?
        </h4>
    <?php endif ?>
</div>
