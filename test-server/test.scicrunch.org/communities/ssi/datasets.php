<?php

$page = isset($_GET["page"]) ? $_GET["page"] : 1;
$per_page = 20;
$offset = ($page - 1) * $per_page;
$status = isset($_GET["status"]) ? $_GET["status"] : NULL;

$is_community_moderator = isset($_SESSION["user"]) && $_SESSION["user"]->levels[$community->id] >= 2;

if(isset($_SESSION["user"])) {
    $user_lab_memberships = LabMembership::loadArrayBy(Array("uid"), Array($_SESSION["user"]->id));
} else {
    $user_lab_memberships = Array();
}

?>

<div class="container s-results" style="margin-top: 20px; margin-bottom: 20px">
    <div class="col-md-2">
        <div class="row">
            <p>
                Users can register and join their lab.
                Lab members can submit datasets to their lab and communities.
            </p>
            <p>
                <a href="<?php echo Community::fullURLStatic($community) ?>/labs">
                    <button class="btn btn-primary">See all <?php echo Community::getPortalName($community) ?> labs</button>
                </a>
            </p>
            <p>
                <?php if(!empty($user_lab_memberships)): ?>
                    Labs you're a member of: <?php echo implode(", ", array_map(function($lm) {
                        $lab = $lm->lab();
                        return '<a href="' . Community::fullURLStatic($lab->community()) . '/lab?id=' . $lab->id . '">' . $lab->name . '</a>';
                    }, $user_lab_memberships)); ?>
                <?php endif ?>
            </p>
        </div>
    </div>
    <div class="col-md-9 col-md-offset-1">
        <?php echo \helper\htmlElement("community-datasets", Array(
            "per_page"=>$per_page,
            "page"=>$page,
            "is_community_moderator"=>$is_community_moderator,
            "offset"=>$offset,
            "status"=>$status,
            "community"=>$community,
            "show_pagination"=>true
        )) ?>
    </div>
</div>
