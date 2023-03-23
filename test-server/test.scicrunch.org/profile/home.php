<?php

$cxn = new Connection();
$cxn->connect();
$conversation_count = $cxn->select("user_messages_conversations_users", Array("count(*)"), "i", Array($_SESSION["user"]->id), "where uid=? and new_flag=1");
$cxn->close();
$n_notifs_conversations = $conversation_count[0]["count(*)"];

$holder = new Saved();
$saved_searches = $holder->getUserSearches($_SESSION["user"]->id);

$resource_subscriptions = Subscription::loadArrayBy(Array("uid", "type"), Array($_SESSION["user"]->id, "resource-mention"));

$rrid_reports = RRIDReport::loadArrayBy(Array("uid"), Array($_SESSION["user"]->id), Array("limit" => 5));

$holder = new Collection();
$collections = $holder->getCollectionsByUser($_SESSION["user"]->id);

function getOrcidInfo($user) {
    $info = Array("id" => NULL, "user-lit" => Array(), "user-rrid" => Array());
    if(is_null($user->orcid_id)) {
        return $info;
    }
    $info["id"] = $user->orcid_id;
    
    // updated to getRRIDWorksByUser since we won't just have PMID. Will have DOI possibly.
    $user_extra_data = UsersExtraData::getRRIDWorksByUser($user);
    foreach($user_extra_data as $ued) {
        if($ued->name == "orcid-works") {
            $lit = $ued->value;
            if (isset($lit["doi"])) {
                $lit["rrid-mentions"] = RRIDMention::loadArrayBy(
                    Array("doi"),
                    Array("DOI:" . $lit["doi"])
                );
            } elseif (isset($lit["pmid"])) {
                $lit["rrid-mentions"] = RRIDMention::loadArrayBy(
                    Array("pmid"),
                    Array("PMID:" . $lit["pmid"])
                );
            }
            $info["user-lit"][] = $lit;
        } elseif($ued->name == "orcid-rrid") {
            $info["user-rrid"][] = $ued->value;
        }
    }

    return $info;
}

function rridMentionsGetQuery($mention) {
    $query_array = Array();
    foreach($mention as $k => $v) {
        $query_array[] = "facets=" . $k . "|" . $v;
    }
    return implode("&", $query_array);
}

function rridMentionsName($mention) {
    $name_array = Array();
    foreach($mention as $k => $v) {
        $name_array[] = $k . " - " . $v;
    }
    return "See mentions filtered by: " . implode(", ", $name_array);
}

?>

<style>
    .profile-name {
        font-size: 38px;
        color: #fff;
    }

    .profile .date-formats.blue {
        background: #3498db;
    }
</style>
<?php

    echo Connection::createBreadCrumbs('My Account',array('Home'),array($profileBase),'My Account');
?>

<div class="profile container content">
<div class="row">
<!--Left Sidebar-->
<?php include 'left-column.php'; ?>
<!--End Left Sidebar-->

<div class="col-md-9">
<!--Profile Body-->
<div class="profile-body">

<?php $roles = array('User', 'Curator', 'Administrator') ?>

<!--Service Block v3-->
<div class="row margin-bottom-10">
    <div class="col-sm-6 sm-margin-bottom-20">
        <div class="service-block-v3 service-block-u">
            <i class="icon-users"></i>
            <span class="profile-name"><?php echo $_SESSION['user']->getFullName() ?></span>

            <a href="/account/edit" class="btn-u btn-u-purple pull-right">Edit</a>

            <div class="clearfix margin-bottom-10"></div>

            <div class="row margin-bottom-20">
                <div class="col-xs-6 service-in">
                    <small>Email</small>
                    <h4><?php echo $_SESSION['user']->email ?></h4>
                </div>
                <div class="col-xs-6 text-right service-in">
                    <small>Role</small>
                    <h4><?php echo $roles[$_SESSION['user']->role] ?></h4>
                </div>
            </div>

            <div class="row margin-bottom-20">
                <div class="col-xs-6 service-in">
                    <small>ORCID iD</small>
                    <h4>
                        <?php if(!is_null($_SESSION["user"]->orcid_id)): ?>
                            <?php echo $_SESSION["user"]->orcid_id ?>
                        <?php else: ?>
                            <?php $cid = !!$community ? $community->id : 0; ?>
                            <a href="https://orcid.org/oauth/authorize?client_id=<?php echo ORCID_CLIENT_ID ?>&response_type=code&scope=/authenticate&redirect_uri=<?php echo PROTOCOL ?>://<?php echo \helper\httpHost() ?>/forms/associate-orcid.php?cid=<?php echo $cid ?>"><button class="btn btn-primary">Associate ORCID iD</button></a>
                        <?php endif ?>
                    </h4>
                </div>
            </div>

            <?php if(isset($GLOBALS["config"]["user-rrid-mentions"][$_SESSION["user"]->id]) && !$community->redirect_url): ?>
                <div class="row margin-bottom-20">
                    <div class="col-xs-6 service-in">
                        <small>RRID Mentions</small>
                        <ul>
                            <?php foreach($GLOBALS["config"]["user-rrid-mentions"][$_SESSION["user"]->id] as $mention): ?>
                                <li>
                                    <a style="color:white" href="/browse/rrid-mentions?<?php echo rridMentionsGetQuery($mention) ?>"><?php echo rridMentionsName($mention) ?></a>
                                </li>
                            <?php endforeach ?>
                        <ul>
                    </div>
                </div>
            <?php endif ?>

        </div>
    </div>

    <div class="col-sm-6">
        <div class="service-block-v3 service-block-blue">
            <i class="fa fa-bell-o"></i>
            <span class="service-heading">Total Actions</span>
            <a href="/account/messages" class="btn btn-danger pull-right"><?php if($n_notifs_conversations > 0): ?>NEW <?php endif ?>Messages</a>
            <span class="counter">
                <?php
                $holder = new Notification();
                echo $holder->getNotificationCount($_SESSION['user']->id, 0);
                ?>
            </span>


            <div class="clearfix margin-bottom-10"></div>

            <div class="row margin-bottom-20">
                <div class="col-xs-6 service-in">
                    <small>Last 7 Days</small>
                    <h4 class="counter">
                        <?php
                        echo $holder->getNotificationCount($_SESSION['user']->id, time() - 60 * 60 * 24 * 7);
                        ?>
                    </h4>
                </div>
                <div class="col-xs-6 text-right service-in">
                    <small>Last 30 Days</small>
                    <h4 class="counter">
                        <?php
                        echo $holder->getNotificationCount($_SESSION['user']->id, time() - 60 * 60 * 24 * 30);
                        ?>
                    </h4>
                </div>
            </div>

            <?php if($_SESSION["user"]->role > 0): ?>
                <div class="row margin-bottom-20">
                    <div class="col-xs-6 service-in">
                        <small>Current data services:</small>
                        <h4><?php echo Connection::environment() ?></h4>
                    </div>
                    <div class="col-xs-6 text-right service-in">
                        <?php if(isset($_SESSION["betaenvironment"]) && $_SESSION["betaenvironment"] === true): ?>
                            <h4><a href="/forms/switch-services.php?to=production"><button class="btn btn-primary">Use production</button></a></h4>
                        <?php else: ?>
                            <small>Switch to stage:</small>
                            <h4><a href="/forms/switch-services.php?to=stage"><button class="btn btn-primary">Use stage</button></a></h4>
                        <?php endif ?>
                    </div>
                </div>
            <?php endif ?>

        </div>
    </div>
</div>
<!--/end row-->
<!--End Service Block v3-->

<hr>

<?php
    $orcid_info = getOrcidInfo($_SESSION["user"]);
?>
<div class="row margin-bottom-20">
    <div class="col-sm-6 md-margin-bottom-20">
        <div class="panel panel-profile no-bg">
            <div class="panel-heading overflow-h">
                <h2 class="panel-title heading-sm pull-left">ORCID ID</h2>
            </div>
            <div class="panel-body contentHolder">
                <div class="profile-event">
                    <?php if(!is_null($orcid_info["id"])): ?>
                        <?php echo $orcid_info["id"] ?>
                        <?php if(!empty($orcid_info["user-lit"]) || !empty($orcid_info["user-rrid"])): ?>
                            <p>Works pulled from your ORCID public profile:</p>
                            <ul>
                                <?php foreach($orcid_info["user-lit"] as $ul): ?>
                                    <li><?php echo $ul['name']; ?>.
                                <?php if (isset($ul['doi'])): ?>
                                        <a target="_blank" href="https://doi.org/<?php echo $ul["doi"] . '">DOI: ' . $ul['doi'] . "</a>\n"; ?>
                                <?php endif; if (isset($ul['pmid'])): ?>
                                        <a target="_blank" href="https://scicrunch.org/<?php echo $ul["pmid"] . '">PMID: ' . $ul['pmid'] . "</a>\n"; ?>
                                <?php endif; ?>
                                    </li>
                                <?php endforeach ?>

                                <?php foreach($orcid_info["user-rrid"] as $ur): ?>
                                    <li>
                                        <a target="_blank" href="<?php echo Community::fullURLStatic($community) ?>/resolver/<?php echo $ur["rrid"] ?>">
                                            <?php echo $ur["name"] ?>
                                        </a>
                                    </li>
                                <?php endforeach ?>
                            </ul>
                        <?php endif ?>
                    <?php else: ?>
                        <?php $cid = !!$community ? $community->id : 0; ?>
                        <a href="https://orcid.org/oauth/authorize?client_id=<?php echo ORCID_CLIENT_ID ?>&response_type=code&scope=/authenticate&redirect_uri=<?php echo PROTOCOL ?>://<?php echo \helper\httpHost() ?>/forms/associate-orcid.php?cid=<?php echo $cid ?>">Associate ORCID ID</a>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-sm-6 md-margin-bottom-20">
        <div class="panel panel-profile no-bg">
            <div class="panel-heading overflow-h">
                <h2 class="panel-title heading-sm pull-left"><i class="fa fa-repeat"></i>Authentication Reports</h2>
                <a href="<?php echo $profileBase ?>account/rrid-report"><i class="fa fa-cog pull-right"></i></a>
            </div>
            <div id="scrollbar2" class="panel-body contentHolder">
                <?php foreach($rrid_reports as $rr): ?>
                    <div class="profile-event" style="min-height: 80px">
                        <h3 class="heading-xs">
                            <a target="_blank" href="<?php echo $profileBase ?>account/rrid-report/<?php echo $rr->id ?>">
                                <?php echo $rr->name ?>
                            </a>
                        </h3>
                        <p class="truncate-desc">
                            <?php echo $rr->name ?>
                        </p>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>

</div>

<div class="row margin-bottom-20">
    <!--Profile Post-->
    <div class="col-sm-6 md-margin-bottom-20">
        <div class="panel panel-profile no-bg">
            <div class="panel-heading overflow-h">
                <h2 class="panel-title heading-sm pull-left"><i class="fa fa-floppy-o"></i>Saved Searches</h2>
                <a href="<?php echo $profileBase ?>account/saved"><i class="fa fa-cog pull-right"></i></a>
            </div>
            <div id="scrollbar2" class="panel-body contentHolder">
                <?php foreach($saved_searches as $i => $svs): ?>
                    <?php if($i >= 5) break; ?>
                    <div class="profile-event" style="min-height: 80px">
                        <div class="overflow-h">
                            <h3 class="heading-xs">
                                <a target="_blank" href="<?php echo $svs->returnURL(NULL, $community) ?>">
                                    <?php echo $svs->name ?>
                                </a>
                            </h3>
                            <p>
                                Search for "<?php echo $svs->query ?>" in <?php echo $svs->category ?>.
                            </p>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>
    <!--End Profile Post-->

    <!--Profile Event-->
    <div class="col-sm-6 md-margin-bottom-20">
        <div class="panel panel-profile no-bg">
            <div class="panel-heading overflow-h">
                <h2 class="panel-title heading-sm pull-left"><i class="fa fa-resources"></i>Subscribed Resources</h2>
                <a href="<?php echo $profileBase ?>account/resources"><i class="fa fa-cog pull-right"></i></a>
            </div>
            <div id="scrollbar2" class="panel-body contentHolder">
                <?php foreach($resource_subscriptions as $rs): ?>
                    <?php
                        $resource = new Resource();
                        $resource->getByRID($rs->fid);
                        $resource->getColumns();
                        $mention_community = new Community();
                        $mention_community->getByID($rs->cid);
                    ?>
                    <div class="profile-event" style="min-height: 80px">
                        <div class="overflow-h">
                            <h3 class="heading-xs">
                                <a target="_blank" href="<?php echo Community::fullURLStatic($mention_community) ?>/Any/record/nlx_144509-1/<?php echo $resource->uuid ?>/search">
                                    <?php echo $resource->columns["Resource Name"] ?>
                                </a>
                            </h3>
                            <p class="truncate-desc">
                                <?php echo $resource->columns["Description"] ?>
                            </p>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>
    <!--End Profile Event-->
</div>
<!--/end row-->

<div class="row margin-bottom-20">

    <div class="col-sm-6 md-margin-bottom-20">
        <div class="panel panel-profile no-bg">
            <div class="panel-heading overflow-h">
                <h2 class="panel-title heading-sm pull-left"><i class="fa fa-folder-open"></i>Collections</h2>
                <a href="<?php echo $profileBase ?>account/collections"><i class="fa fa-cog pull-right"></i></a>
            </div>
            <div id="scrollbar2" class="panel-body contentHolder">
                <?php foreach($collections as $i => $coll): ?>
                    <?php if($i >= 5) break ?>
                    <div class="profile-event" style="min-height: 80px">
                        <div class="overflow-h">
                            <h3 class="heading-xs">
                                <a target="_blank" href="<?php echo $profileBase ?>account/collections/<?php echo $coll->id ?>">
                                    <?php echo $coll->name ?>
                                </a>
                            </h3>
                            <p class="truncate-desc">
                                <?php echo $coll->count ?> records
                            </p>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
        </div>
    </div>
</div>

<!--End Table Search v2-->
</div>
<!--End Profile Body-->
</div>
</div>
<!--/end row-->
</div>
<!--/container-->
<!--=== End Profile ===-->
<script>
    $(function() {
        $(".truncate-desc").truncate({max_length: 200});
    });
</script>
