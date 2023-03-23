<?php

$user = $data["user"];
$community = $data["community"];

if(!$user || !$community) {
    return;
}

$main_lab = Lab::getUserMainLab($user,$community->id);

?>

<div>
    <div class="col-md-9">
        <div class="margin-bottom-50">
            <h2>Join or Create a Lab</h2>
            <h4>
                All datasets within the <?php echo $community->portalName ?> portal are managed in the context of a lab.
                If you are a PI you can register your lab.
                If you are a member of a lab, send a request to join an existing lab.
            </h4>
        </div>
        <?php if(!is_null($main_lab)): ?>
            <div class="col-md-3">
                <div class="text-center">
                    <a href="<?php echo $community->fullURL() ?>/lab?labid=<?php echo $main_lab->id ?>">
                        <div class="lab-button lab-big-button">
                            <span>Go to your lab</span>
                        </div>
                    </a>
                    <h4 class="margin-top-20">
                        You are already a member of <?php echo $main_lab->name ?>.
                        Click above to go to your lab.
                    </h4>
                </div>
            </div>
            <div class="col-md-1"></div>
        <?php endif ?>
        <div class="col-md-3">
            <div class="text-center">
                <a href="<?php echo $community->fullURL() ?>/community-labs/list">
                    <div class=" lab-button lab-big-button">
                        <span>Join lab</span>
                    </div>
                </a>
                <h4 class="margin-top-20">
                    Join an existing lab as a member.
                    This will allow you to upload data and view lab datasets.
                </h4>
            </div>
        </div>
        <div class="col-md-1"></div>
        <div class="col-md-3">
            <div class="text-center">
                <a href="<?php echo $community->fullURL() ?>/community-labs/create">
                    <div class="lab-button lab-big-button">
                        <span>Register lab</span>
                    </div>
                </a>
                <h4 class="margin-top-20">
                    As a PI, create your lab.
                    This will allow you to manage lab members and datasets within your lab.
                </h4>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="jumbotron jumbotron-assistance-information">
            <h2><i class="fa fa-info-circle"></i>Map information to be completed</h2>
            <hr class="jumbo-info">
        </div>
    </div>
</div>
