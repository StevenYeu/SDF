<?php
    $count = (int) $component->text3;
    if(!$count) $count = 5;
    $holder = new Component_Data();
    $cdatas = $holder->tagSearch($component->text2, $community->id, NULL, 0, $count);

    $twitter_set = $component->color2 && $component->color3;

?>

<style>
<?php if($component->color1): ?>
.component-<?php echo $component->id ?>.list-group-item.active {
    background-color: #<?php echo $component->color1 ?>;
    border-color: #<?php echo $component->color1 ?>;
}
.component-<?php echo $component->id ?>.panel-heading {
    background-color: #<?php echo $component->color1 ?>;
}
.component-<?php echo $component->id ?>.panel-heading > h3 {
    color: #FFFFFF;
}
<?php endif ?>
</style>

<script>
$(function() {
    $(".news-feed-select").click(function() {
        $(this).addClass("active");
        $(this).siblings().removeClass("active");
        var id = $(this).data("id");
        var $panel = $(".news-feed-panel[data-id="+id+"]");
        $panel.show();
        $panel.siblings().hide();
    });
    <?php if($cdatas["count"] > 0): ?>
        $(".news-feed-select[data-id=<?php echo $component->id . "-" . $cdatas["results"][0]->id ?>]").trigger("click");
    <?php endif ?>
});
</script>

<div class="container content <?php if($vars["editmode"]) echo "editmode" ?>" style="padding-top:5px; padding-bottom:5px;">
    <?php if($cdatas["count"] === 0): ?>
        <h2>Check back here for news updates</h2>
    <?php else: ?>
        <div class="headline">
            <h2><?php echo $component->text1 ?></h2>
        </div>
        <div class="row">
            <div class="col-md-<?php echo $twitter_set ? "3" : "4" ?><?php if(!$twitter_set) echo " col-md-offset-1" ?>">
                <div class="list-group">
                    <?php foreach($cdatas["results"] as $cdata): ?>
                        <a href="javascript:void(0)" class="list-group-item news-feed-select component-<?php echo $component->id ?>" data-id="<?php echo $component->id . "-" . $cdata->id ?>">
                            <i class="fa fa-newspaper-o"></i>
                            <?php echo $cdata->title ?>
                        </a>
                    <?php endforeach ?>
                </div>
            </div>
            <div class="col-md-<?php echo $twitter_set ? "5" : "6" ?>">
                <?php foreach($cdatas["results"] as $cdata): ?>
                    <?php
                        $reference_component = new Component();
                        $reference_component->getByType($cdata->cid, $cdata->component);
                    ?>
                    <div class="panel panel-default news-feed-panel" data-id="<?php echo $component->id . "-" . $cdata->id ?>" style="display: none">
                        <div class="panel-heading component-<?php echo $component->id ?>"><h3><?php echo $cdata->title ?></h3></div>
                        <div class="panel-body">
                            <h5><?php echo $cdata->description ?></h5>
                            <p><a href="<?php echo "/" . $community->portalName . "/about/" . $reference_component->text2 . "/" . $cdata->id; ?>">Read more <i class="fa fa-external-link"></i></a></p>
                        </div>
                    </div>
                <?php endforeach ?>
            </div>
            <?php if($twitter_set): ?>
                <div class="col-md-4">
                    <!-- Begin Section-Block -->
                    <a class="twitter-timeline" data-height="500px" href="https://twitter.com/<?php echo $component->color2 ?>">Tweets by @<?php echo $component->text2 ?></a> <script async src="//platform.twitter.com/widgets.js" charset="utf-8"></script>
                </div>
            <?php endif ?>
        </div>
    <?php endif ?>

    <?php if ($vars['editmode']) {
        echo '<div class="body-overlay"><h3>' . $component->component_ids[$component->component] . '</h3>';
        echo '<div class="pull-right">';
        if ($componentCount > 0)
            echo '<a class="btn-u btn-u-blue" href="/forms/component-forms/body-component-shift.php?component=' . $component->id . '&cid=' . $component->cid . '&direction=up"><i class="fa fa-angle-up"></i><span class="button-text"> Shift Up</span></a>';
        if ($componentCount != $componentTotal - 1)
            echo '<a class="btn-u btn-u-blue" href="/forms/component-forms/body-component-shift.php?component=' . $component->id . '&cid=' . $component->cid . '&direction=down"><i class="fa fa-angle-down"></i><span class="button-text"> Shift Down</span></a>';
        echo '<button class="btn-u btn-u-default edit-body-btn" componentType="body" componentID="'.$component->id.'"><i class="fa fa-cogs"></i><span class="button-text"> Edit</span></button><a href="javascript:void(0)" componentID="'.$component->id.'" community="'.$community->id.'" class="btn-u btn-u-red component-delete-btn"><i class="fa fa-times"></i><span class="button-text"> Delete</span></a></div>';
        echo '</div>';
    } ?>
</div>
