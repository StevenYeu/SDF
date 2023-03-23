<?php
    //if nothing searched, the query is empty string
    if(!isset($query)) $query = "";
    //if nothing displayed, set to query
    if(!isset($display_query)) $display_query = $query;
?>

<!--copies the image/colors of component to search style-->
<style>

    .term-search-block {
        height: 74px;
    }

</style>

<!--=== Search Block ===-->
<div class="term-search-block parallaxBg <?php if ($vars['editmode']) echo 'editmode' ?>">
    <div class="container">
        <div class="col-md-8 col-md-offset-2" style="margin-top: 20px;">
            <form action="" class="sky-form page-search-form">
                <input type="hidden" class="search-community" value="<?php echo $community->portalName ?>"/>
                <input type="hidden" class="stripped-community" value="<?php echo $vars['stripped'] ?>"/>
                <div class="input-group">
                    <!--if not scicrunch comm, show tooltip search button-->
                    <?php if($community->id != 0): ?>
                        <span class="input-group-btn">
                            <div class="btn btn-info help-tooltip-btn" data-name="interlex-search-tips.html"><i class="fa fa-question-circle"></i></div>
                        </span>
                    <?php endif ?>
                    <input type="text" class="form-control searchbar" style="height:34px" id="search-banner-input" name="l" placeholder="<?php echo $component->text1 ?>" value="<?php echo $display_query ?>">
                    <input type="hidden" id="autoValues" />

                    <div class="autocomplete_append auto" style="z-index:10"></div>
                    <!--to submit the search-->
                    <span class="input-group-btn">
                        <button class="btn search-icon" type="submit"><i class="fa fa-search"></i></button>
                    </span>
                </div>
            </form>
        </div>
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
</div><!--/container-->
<!--=== End Search Block ===-->
