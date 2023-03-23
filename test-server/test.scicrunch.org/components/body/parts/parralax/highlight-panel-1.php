<?php
$holder = new Component_Data();
$datas1 = $holder->getByComponent(151, $community->id, 0, 2);
$datas2 = $holder->getByComponent(150, $community->id, 0, 5);
?>

<div class="container <?php if ($vars['editmode']) echo 'editmode' ?>">
    <div class="row">
        <div class="col-md-3 col-sm-6">
            <?php if ($datas1 == 0): ?>
                <img src="../../../../assets/img/new/img11.jpg" class="img-rounded" alt="Norway" style="width:100%">
            <?php else: ?>
                <a href="<?php echo $datas1[0]->link ?>">
                    <img src="/upload/community-components/<?php echo $datas1[0]->image ?>" class="img-rounded" alt="Norway" style="width:100%">
                </a>
            <?php endif ?>
        </div>
        <div class="col-md-6 col-sm-6">
            <div class="tp-banner-container">
                <div class="tp-banner">
                    <ul>
                        <!-- SLIDE -->
                        <?php if( $datas2 == 0): ?>
                    				<li data-transition="fade" data-slotamount="5" data-masterspeed="1000">
                        				<img src="../../../../assets/img/sliders/6.jpg">
                        				<div class="tp-caption sft"
                                                 data-x="right"
                                                 data-hoffset="0"
                                                 data-y="top"
                                                 data-speed="1600"
                                                 data-start="2800"
                                                 data-easing="Power4.easeOut"
                                                 data-endspeed="300"
                                                 data-endeasing="Power1.easeIn"
                                                 data-captionhidden="off"
                                                 style="z-index: 6" style="color:#FFFFFF">
                                                <a href="<?php echo $community->url ?>" class="btn-u btn-brd btn-brd-hover btn-u-light">Learn
                                                    More Button</a>
                                </div>
                    				</li>
                				<?php else: ?>
                            <?php foreach ($datas2 as $i => $data): ?>
                                <li data-transition="fade" data-slotamount="5">
                                    <!-- MAIN IMAGE -->
                                    <img src="/upload/community-components/<?php echo $data->image ?>">
                                    <!-- LAYER -->
                                    <div class="tp-caption sft"
                                         data-x="right"
                                         data-hoffset="0"
                                         data-y="top"
                                         data-speed="1600"
                                         data-start="2800"
                                         data-easing="Power4.easeOut"
                                         data-endspeed="300"
                                         data-endeasing="Power1.easeIn"
                                         data-captionhidden="off"
                                         style="z-index: 6" style="color:<?php echo '#' . $data->color ?>">
                                        <a href="<?php echo $data->link ?>" class="btn-u btn-brd btn-brd-hover btn-u-light">Learn
                                            More</a>
                                    </div>
                                </li>
                            <?php endforeach ?>
                        <?php endif ?>
                    </ul>
                    <div class="tp-bannertimer tp-bottom"></div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <?php if ($datas1 < 2): ?>
                <img src="../../../../assets/img/new/img13.jpg" class="img-rounded" alt="Norway" style="width:100%">
            <?php else: ?>
                <a href="<?php echo $datas1[1]->link ?>">
                    <img src="/upload/community-components/<?php echo $datas1[1]->image ?>" class="img-rounded" alt="Norway" style="width:100%">
                </a>
            <?php endif ?>
        </div>
    </div>
    <?php if ($vars['editmode']) {
        echo '<div class="body-overlay"><h3>' . $component->component_ids[$component->component] . '</h3>';
        echo '<div class="pull-right">';
        if ($componentCount > 0)
            echo '<a class="btn-u btn-u-blue" href="/forms/component-forms/body-component-shift.php?component=' . $component->id . '&cid=' . $component->cid . '&direction=up"><i class="fa fa-angle-up"></i><span class="button-text"> Shift Up</span></a>';
        if ($componentCount != $componentTotal - 1)
            echo '<a class="btn-u btn-u-blue" href="/forms/component-forms/body-component-shift.php?component=' . $component->id . '&cid=' . $component->cid . '&direction=down"><i class="fa fa-angle-down"></i><span class="button-text"> Shift Down</span></a>';
        // echo '<button class="btn-u add-data-btn" componentType="body" componentID="150" cid="' . $community->id . '"><i class="fa fa-plus" title="edit slide"></i><span class="button-text"> Edit</span></button>';
        echo '<a class="btn-u btn-u-light-green" href="/'.$community->portalName.'/account/communities/'.$community->portalName.'/dynamic/150"><i class="fa fa-list-alt" title="edit slides"></i></a>';

        if($community->id==0)
            echo '<a class="btn-u btn-u-purple" href="/account/scicrunch/dynamic/151"><i class="fa fa-list-alt" title="replace images"></i></a>';
        else
            echo '<a class="btn-u btn-u-purple" href="/'.$community->portalName.'/account/communities/'.$community->portalName.'/dynamic/151"><i class="fa fa-file-image-o" title="edit images"></i></a>';

        echo '<button class="btn-u btn-u-default edit-body-btn" componentType="body" componentID="' . $component->id . '"><i class="fa fa-cogs"></i><span class="button-text"> Edit</span></button><a href="javascript:void(0)" componentID="' . $component->id . '" community="' . $community->id . '" class="btn-u btn-u-red component-delete-btn"><i class="fa fa-times"></i><span class="button-text"> Delete</span></a></div>';
        echo '</div>';
    } ?>
    <br>
</div>
