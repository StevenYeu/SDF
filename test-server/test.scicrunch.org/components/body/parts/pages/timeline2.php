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
                        <a href="<?php echo $baseURL . $thisComp->text2 . '/' . $data->id ?>">
                            <span><?php echo date('l', $data->time) . ' the ' . date('jS', $data->time) ?></span><span><?php echo date('F Y', $data->time) ?></span>
                        </a>
                    </time>
                    <i class="cbp_tmicon rounded-x hidden-xs"></i>

                    <div class="cbp_tmlabel">
                        <h2><a href="<?php echo $baseURL . $thisComp->text2 . '/' . $data->id ?>"><?php echo $data->title ?></a></h2>

                        <p><?php echo $data->description ?></p>
                    </div>
                </li>
            <?php }
            ?>
        </ul>
    </div>
    <?php
    if($count>10){
        echo '<div class="content-load-div" style="display:none"></div>';
        echo '<div style="text-align:center">';
        echo '<a class="btn-u content-load-btn" type="timeline-v2" num="10" cid="'.$community->id.'" comp="'.$thisComp->component.'" href="javascript:void(0)">Load More</a>';
        echo '</div>';
    }
    if(count($datas)==0){
        echo '<a class="btn-u btn-u-lg" href="/'.$community->portalName.'/about/search">No Articles Found, Browse All Community Articles</a>';
    }
    if ($vars['editmode']) {
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
            <div class="close dark less-right">X</div>
            <h2 style="margin-bottom: 40px">Are you sure you want to delete this article and all data added to it?</h2>
            <a href="javascript:void(0)" class="btn-u close-btn">No</a>
            <a href="/forms/component-forms/container-component-delete.php?cid=<?php echo $community->id ?>&id=<?php echo $thisComp->id ?>"
               class="btn-u btn-u-default" style="">Yes</a>

        </div>
    <?php
    }

    ?>
</div>
