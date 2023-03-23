<?php

$lab = $data["lab"];
$community = $data["community"];
$user = $data["user"];
$page = $data["page"] ?: 1;

if (!$lab) {
    echo '<h4>To explore non-published data, you must be a member of a verified lab. To become a member, you can create or join a lab.</h4><a href="/' . $community->portalName . '/community-labs/list"><span class="lab-button lab-small-button">Join/Register</span></a>';
}

if(!$lab || !$community || !$user) {
    return;
}

$lab_datasets = Dataset::loadByCommunityAndUser($community, $user, 200, 0);

?>
<style>
div.text-container {
    margin: 0 auto;
}

.hideContent {
    overflow: hidden;
    line-height: 1.5em;
/*    height: 42px !important; */
}

.addHeight {
    height: 42px !important;
}
.showContent {
    line-height: 1.5em;
    height: auto;
}

p {
    padding: 10px 0;
}
.show-more {
    padding: 10px 0;
    text-align: center;
}
</style>
<div>
    <div class="row">
        <div class="col-md-6">
                <!--<h1><a class="lab-link" href="<?php echo $community->fullURL() ?>/lab?labid=<?php echo $lab->id ?>"><?php echo $lab->name ?></a> datasets</h1>-->
                <h2>Open Data Commons Datasets</h2>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12 text-container" >
            <?php if(!empty($lab_datasets)): ?>
                <?php 
                $counter = 0;
                foreach($lab_datasets as $dataset): ?>
                    <div class="row">
                        <div class="col-md-8">
                            <h4>
                                <?php if ($dataset->uid == $_SESSION['user']->id)
                                        $link = "/lab/dataset?labid=" . $dataset->lab()->id . "&datasetid=" . $dataset->id;
                                    else
                                        $link = "/community-labs/dataset?datasetid=" . $dataset->id;
                                ?>
                                <a class="lab-link" href="<?php echo $community->fullURL() . $link . '">' . $dataset->name . "</a>";?>
                            </h4>
                            <div class="bigger">
                            <div class="content hideContent" style="padding-top: 0px; padding-bottom: 10px;"><?php echo $dataset->description; ?></div>
                            <span class="show-more" id="counter<?php echo $counter; ?>"><a href="#block<?php echo $counter++; ?>" class="lab-link">Show more</a></span>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <dl class="dl-horizontal">
                                <dt>Submitted by</dt>
                                <dd><?php echo $dataset->user()->getFullName() ?></dd>
                                <dt>Lab</dt>
                                <dd><?php echo $dataset->lab()->name ?></dd>
                                <dt>Status</dt>
                                <dd><span style="color:<?php echo $dataset->labStatusColor(); ?>"><?php echo $dataset->labStatusPretty(); ?></span></dd>
                                <dt>Size</dt>
                                <dd>
                                    <?php echo $dataset->record_count ?> records
                                    /
                                    <?php echo $dataset->template()->nfields() ?> fields
                                </dd>
                            </dl>
                        </div>
                    </div>
                    <hr/>
                <?php endforeach ?>
            <?php else: ?>
                <h4>
                    No datasets have been made available to the Open Data Commons yet.
                </h4>
            <?php endif ?>
        </div>
    </div>
</div>

 <script>
$(".show-more a").on("click", function() {
    var $this = $(this); 
    var $content = $this.parent().prev("div.content");
    var linkText = $this.text().toUpperCase();    

    if(linkText === "SHOW MORE"){
        linkText = "Show less";
        $content.switchClass("hideContent", "showContent", 100);
        $content.removeClass("addHeight");
    } else {
        linkText = "Show more";
        $content.switchClass("showContent", "hideContent", 100);
        $content.addClass("addHeight");
    };

    $this.text(linkText);
});


$( ".bigger" ).each(function( index ) {
    if ($(this).find(".hideContent").height() < 30) {
        // if less than 30, don't show the "show more"
        $(this).find(".show-more").addClass("hidden");
    } else {
        // need the addHeight to hide the extra lines at the beginning
        $(this).find(".hideContent").addClass("addHeight");
    }
});
</script>
