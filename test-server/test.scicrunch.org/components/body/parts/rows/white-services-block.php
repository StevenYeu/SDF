<div class="container content <?php if($vars['editmode']) echo 'editmode' ?>">
    <div class="col-md-4 animated fadeIn">
        <a href="<?php echo $component->color1 ?>">
            <div class="service">
                <i style="color:#0000FF" class="fa <?php echo $component->icon1 ?> service-icon"></i>
                <div class="desc">
                    <?php echo $component->text1 ?>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4 animated fadeIn">
        <a href="<?php echo $component->color2 ?>">
            <div class="service">
                <i style="color:#0000FF" class="fa <?php echo $component->icon2 ?> service-icon"></i>
                <div class="desc">
                    <?php echo $component->text2 ?>
                </div>
            </div>
        </a>
    </div>
    <div class="col-md-4 animated fadeIn">
        <a href="<?php echo $component->color3 ?>">
            <div class="service">
                <i style="color:#0000FF" class="fa <?php echo $component->icon3 ?> service-icon"></i>
                <div class="desc">
                    <?php echo $component->text3 ?>
                </div>
            </div>
        </a>
    </div>
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
