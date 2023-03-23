<?php

$community = $data["community"];
$user = $data["user"];

$lab_memberships = LabMembership::loadArrayBy(Array("uid"), Array($user->id));
$moderator = false;
if($community->id && $user->id && $user->levels[$community->id] > 1) {
    $moderator = true;
}

$community_labs = Lab::loadArrayBy(Array("cid"), Array($community->id));

?>
<div class="row">
    <h5>
        In order to share data you must be affiliated with a registered laboratory.
        Can't find your lab?  Send a notification to your PI or manager to register your lab.
        If you're an investigator or lab manager please register your lab <a href="<?php echo Community::fullURLStatic($community) ?>/lab/create">here</a>.
    </h5>
    <h5>
        <?php if($moderator): ?>
            Manage all the labs in the <?php echo Community::getPortalName($community) ?> community: 
            <a href="<?php echo Community::fullURLStatic($community) ?>/account/communities/<?php echo Community::getPortalName($community) ?>#labs">here</a>.
        <?php endif ?>
    </h5>
    <hr/>
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title">Your labs</h3></div>
        <div class="panel-body">
            <?php if(!empty($lab_memberships)): ?>
                <table class="table table-hover">
                    <tbody>
                        <?php foreach($lab_memberships as $lm): ?>
                            <?php
                                $lab = $lm->lab();
                                $lab_com = $lab->community();
                            ?>
                            <tr>
                                <td>
                                    <a href="<?php echo $lab_com->fullURL() ?>/lab?labid=<?php echo $lab->id ?>">
                                        <h4><?php echo $lab->name ?></h4>
                                        <p><?php echo $lab_com->portalName ?> community</p>
                                    </a>
                                </td>
                                <td>
                                    <a href="<?php echo $lab_com->fullURL() ?>/lab?labid=<?php echo $lab->id ?>">
                                        <p><?php echo $lab->public_description ?></p>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>
                    You are not a member of any labs.
                </p>
            <?php endif ?>
        </div>
    </div>
    <hr/>
    <div class="panel panel-default">
        <div class="panel-heading"><h3 class="panel-title"><?php echo Community::getPortalName($community) ?> community labs</h3></div>
        <div class="panel-body">
            <?php if(!empty($community_labs)): ?>
                <table class="table table-hover">
                    <?php foreach($community_labs as $lab): ?>
                        <?php
                            $lab_com = $lab->community();
                        ?>
                        <tr>
                            <td>
                                <a href="<?php echo $lab_com->fullURL() ?>/lab?labid=<?php echo $lab->id ?>">
                                    <h4><?php echo $lab->name ?></h4>
                                </a>
                            </td>
                            <td>
                                <a href="<?php echo $lab_com->fullURL() ?>/lab?labid=<?php echo $lab->id ?>">
                                    <p><?php echo $lab->public_description ?></p>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach ?>
                </table>
            <?php else: ?>
                <p>
                    This community does not have any labs yet.
                </p>
            <?php endif ?>
        </div>
    </div>
</div>
