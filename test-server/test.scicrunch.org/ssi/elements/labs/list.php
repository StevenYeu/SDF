<?php

$community = $data["community"];
$user = $data["user"];

if($community->id && $user->id && $user->levels[$community->id] > 1) {
    $moderator = true;
} else {
    $moderator = false;
}

$community_labs = Lab::loadArrayBy(Array("cid", "curated"), Array($community->id, Lab::CURATED_STATUS_APPROVED));
$user_labs = Array();
$nonuser_labs = Array();

foreach($community_labs as $cl) {
    if($cl->isMember($user)) {
        $user_labs[] = $cl;
    } else {
        $nonuser_labs[] = $cl;
    }
}

$access_requests_array = LabMembership::loadArrayBy(Array("uid", "level"), Array($user->id, 0));
$access_requests = Array();
foreach($access_requests_array as $ar) {
//    $access_requests[$ar->labid] = true;
    $access_requests[$ar->labid]["true_false"] = true;
    $access_requests[$ar->labid]["request_date"] = $ar->timestamp;
}

function labCountText($lab) {
    $nmembers = $lab->numberOfMembers();
    $ndatasets = $lab->numberOfDatasets();
    $member_text = $nmembers == 1 ? "member" : "members";
    $dataset_text = $ndatasets == 1 ? "dataset" : "datasets";
    return $nmembers . " " . $member_text . " | " . $ndatasets . " " . $dataset_text;
}

$create_lab_page = "create";
if (isset($_GET['labid']))
    $create_lab_page = "create?labid=" . $_GET['labid'];

?>

<script type="text/javascript">
  $(document).ready(function() {
    if (window.location.href.indexOf("message=sent") > -1) {
      alert("Your request has been sent.");
    }
  });
</script>
<div>
    <?php if(empty($user_labs)): ?>
        <div class="row">
            <h4>
                In order to share data you must be affiliated with a registered laboratory.
                Can't find your lab?  Send a notification to your PI or manager to register your lab.
                If you're an investigator or lab manager, please register your lab.
            </h4>
            <a class="lab-link" href="<?php echo Community::fullURLStatic($community) ?>/community-labs/<?php echo $create_lab_page; ?>"><button class="btn btn-primary">Register a new lab</button></a>
        </div>
        <hr class="hr-small"/>
    <?php endif ?>
    <div class="row">
        <?php if(empty($community_labs)): ?>
            <h4>This community doesn't have any labs yet</h4>
        <?php else: ?>
            <div class="col-md-6">
                <div style="margin-right:20px">
                    <?php if(empty($user_labs)): ?>
                        <h2>Join a lab by selecting one on the right</h2>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-md-6">
                                <h2>Your Labs</h2>
                            </div>
                            <div class="col-md-6">
                                <div class="pull-right">
                                    <a href="<?php echo $community->fullURL() ?>/community-labs/<?php echo $create_lab_page; ?>">
                                        <span class="lab-button lab-small-button">
                                            <i class="fa fa-plus"></i>
                                            Register a new lab
                                        </span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php foreach($user_labs as $lab): ?>
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <div class="col-md-8">
                                        <?php if ($lab->isModerator($user)): ?>
                                                <a class="lab-link" href="<?php echo $community->fullURL() ?>/lab/admin?labid=<?php echo $lab->id ?>">
                                        <?php else: ?>
                                                <a class="lab-link" href="<?php echo $community->fullURL() ?>/lab?labid=<?php echo $lab->id ?>">
                                        <?php endif; ?>
                                            <h4><?php echo $lab->name ?></h4></a>
                                            <p><?php echo $lab->public_description ?></p>
                                            <p><?php echo labCountText($lab) ?></p>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    <?php endif ?>
                </div>
            </div>
            <div class="col-md-6">
                <div class="margin-left:20px">
                    <?php if(empty($nonuser_labs)): ?>
                        <h2>There are no other labs to join.</h2>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-md-12">
                                <h2><?php echo strtoupper($community->portalName); ?> Labs</h2>
                            </div>
                        </div>
                        <?php foreach($nonuser_labs as $lab): ?>
                            <div class="panel panel-default">
                                <div class="panel-body">
                                    <div class="col-md-8">
                                        <a class="lab-link" href="<?php echo $community->fullURL() ?>/lab?labid=<?php echo $lab->id ?>">
                                            <h4><?php echo $lab->name ?></h4></a>
                                            <p><?php echo $lab->public_description ?></p>
                                            <p><?php echo labCountText($lab) ?></p>
                                        </a>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="pull-right">
                                            <?php if($access_requests[$lab->id]): ?>
                                                <p>Request has been sent.</p>
                                                <?php if ($access_requests[$lab->id]["request_date"] + (33 * 24 * 60 * 60) >= time()): ?>
                                                    <a href="/forms/community-forms/lab-user-join.php?request=<?php echo $lab->id; if (isset($_GET['labid'])) echo "&labid=" . $_GET['labid']; ?>">
                                                        <span class="lab-button lab-small-button">Resend request to Join</span>
                                                    </a>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <a href="/forms/community-forms/lab-user-join.php?request=<?php echo $lab->id; if (isset($_GET['labid'])) echo "&labid=" . $_GET['labid']; ?>">
                                                    <span class="lab-button lab-small-button">Request to Join</span>
                                                </a>
                                            <?php endif ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach ?>
                    <?php endif ?>
                </div>
            </div>
        <?php endif ?>
    </div>
</div>
