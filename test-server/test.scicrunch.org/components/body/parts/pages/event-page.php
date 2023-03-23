<?php

$long_time_format = "h:ia F j, Y";
$short_time_format = "F j, Y";
$day_length = 86400;    // number of seconds in a day

?>

<div class="container content <?php if($vars['editmode']) echo 'editmode' ?>">
    <div class="row">
        <div style="height:100px">
            <div class="col-md-10 col-md-offset-1" style="font-size: 14px"><?php echo $thisComp->text3 ?></div>
            <div class="col-md-1 pull-right">
                <a class="btn-u" href="<?php echo $baseURL.$thisComp->text2.'/rss'?>">RSS Feed</a>
            </div>
        </div>
    </div>
    <div class="row">
        <ul class="timeline-v2">
            <?php foreach ($datas as $data) { ?>
                <li>
                    <time class="cbp_tmtime" datetime="">
                        <a href="<?php echo $data->link ?>">
                    <?php
                    $component_data_multis = ComponentDataMulti::loadArrayBy(Array("component_data_id"), Array($data->id));
                    if(!empty($component_data_multis)) {
                        usort($component_data_multis, function($a, $b) {
                            return strcmp($a->name, $b->name);
                        });
                    }
                    $time = time();
                    if($data->start > $time){
                        echo '<span>Starts on</span><span>'.date('F d, Y',$data->start).'</span>';
                    } elseif($time < $data->end + $day_length){
                        echo '<span>Ends on</span><span>'.date('F d, Y',$data->end).'</span>';
                    } elseif($data->end !== 0) {
                        echo '<span>Ended on</span><span>'.date('F d, Y',$data->end).'</span>';
                    } else {
                        echo '<span>Ended</span><span></span>';
                    }
                    ?>
<!--                            <span>--><?php //echo date('l', $data->time) . ' the ' . date('jS', $data->time) ?><!--</span><span>--><?php //echo date('F Y', $data->time) ?><!--</span>-->
                        </a>
                    </time>
                    <i class="cbp_tmicon rounded-x hidden-xs"></i>

                    <div class="cbp_tmlabel">
                        <h2>
                            <a href="<?php echo $data->link?>"><?php echo $data->title ?></a>
                            <div class="pull-right" style="font-size: 14px">

                                <?php if($data->start>0): ?>
                                    <b>Start:</b>
                                    <?php if(date("h:ia", $data->start) == "12:00am"): ?>
                                        <?php echo date($short_time_format, $data->start) ?>
                                    <?php else: ?>
                                        <?php echo date($long_time_format, $data->start) ?>
                                    <?php endif ?>
                                    &nbsp;&nbsp;&nbsp;&nbsp;
                                <?php endif ?>

                                <?php if($data->end > 0): ?>
                                    <b>End:</b>
                                    <?php if(date("h:ia", $data->end) == "12:00am"): ?>
                                        <?php echo date($short_time_format, $data->end) ?>
                                    <?php else: ?>
                                        <?php echo date($long_time_format, $data->end) ?>
                                    <?php endif ?>
                                <?php endif ?>

                            </div>
                        </h2>

                        <p><?php echo $data->description ?></p>
                        <?php
                        if($data->content){
                           $splits = explode(':',$data->content);
                            echo '<p><b>'.$splits[0].'</b>:'.$splits[1].'</p>';
                        }
                        if($data->icon){
                            $splits = explode(':',$data->icon);
                            echo '<p><b>'.$splits[0].'</b>:'.$splits[1].'</p>';
                        }
                        if($data->color){
                            $splits = explode(':',$data->color);
                            echo '<p><b>'.$splits[0].'</b>:'.$splits[1].'</p>';
                        }
                        foreach($component_data_multis as $cdm) {
                            if(!$cdm->value) continue;
                            $splits = explode(":", $cdm->value);
                            echo "<p><b>" . $splits[0] . "</b>:" . $splits[1] . "</p>";
                        }
                        ?>
                    </div>
                </li>
            <?php }

            ?>
        </ul>
    </div>
    <?php
    if(count($datas)==0){
        echo '<a class="btn-u btn-u-lg" href="/'.$community->portalName.'/about/search">No Articles Found, Browse All Community Articles</a>';
    }if ($vars['editmode']) {
        echo '<div class="body-overlay"><h3>Container Options</h3>';
        echo '<div class="pull-right">';
        echo '<button class="btn-u btn-u simple-toggle" modal=".add-content-box" title="Add New '.$thisComp->text1.'"><i class="fa fa-plus"></i></button>
              <a title="Manage the data under this container" href="/'.$community->portalName.'/account/communities/'.$community->portalName.'/view/'.$thisComp->component.'" class="btn-u btn-u-blue"><i class="fa fa-pencil-square-o"></i></a>
              <button class="btn-u btn-u-default simple-toggle" modal=".custom-form" title="Edit Container"><i class="fa fa-cogs"></i><span class="button-text"> Edit</span></button>
              <a href="javascript:void(0)" componentID="' . $thisComp->component . '" community="' . $community->id . '" class="btn-u btn-u-red article-delete-btn"><i class="fa fa-times"></i><span class="button-text"> Delete</span></a></div>';
        echo '</div>';
        if (count($tags) > 0) {
            foreach ($tags as $tag) {
                $tagText[] = $tag->tag;
            }
            $tt = join(', ', $tagText);
        } else {
            $tt = '';
        }

        ?>


        <div class="custom-form back-hide no-padding">
            <div class="close light less-right">X</div>
            <style>
                .servive-block-default {
                    cursor: pointer;
                }

                .panel-dark .panel-heading {
                    background: #555;
                    color: #fff;
                }
            </style>
            <form method="post"
                  action="/forms/component-forms/container-component-edit.php?cid=<?php echo $community->id ?>&id=<?php echo $thisComp->id ?>"
                  id="header-component-form" class="sky-form" enctype="multipart/form-data">
                <?php echo $thisComp->bodyComponentHTML(0, 0, false, array()); ?>
                <footer>
                    <button type="submit" class="btn-u btn-u-default" style="width:100%">Submit</button>
                </footer>
            </form>
        </div>
        <div class="large-modal back-hide add-content-box no-padding">
            <div class="close dark less-right">X</div>
            <form method="post" action="/forms/component-forms/component-insert.php?id=<?php echo $thisComp->component?>&cid=<?php echo $community->id?>" id="header-component-form" class="sky-form" enctype="multipart/form-data">

                <header><h2>Add <?php echo $thisComp->text1?></h2></header>
                <div class="row margin-bottom-10">
                    <?php
                    $holder = new Component_Data();
                    echo $holder->getContainerDataForm($thisComp->icon1,'');
                    ?>
                </div>

                <footer>
                    <button type="submit" class="btn-u btn-u-default" style="width:100%">Submit</button>
                </footer>
            </form>
        </div>
        <div class="article-delete back-hide">
            <div class="close dark">X</div>
            <h2 style="margin-bottom: 40px">Are you sure you want to delete this article and all data added to it?</h2>
            <a href="javascript:void(0)" class="btn-u close-btn">No</a>
            <a href="/forms/component-forms/container-component-delete.php?cid=<?php echo $community->id ?>&id=<?php echo $thisComp->id ?>"
               class="btn-u btn-u-default" style="">Yes</a>

        </div>
    <?php
    }

    ?>
</div>
