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

<!-- Software Search taken from resource search page -- Added by Steven -->

<div class="search-block-v2 parallaxBg <?php if ($vars['editmode']) echo 'editmode' ?>" style="padding:30px 0 38px">
    <div class="container">
            <div class="col-md-6 col-md-offset-3">
                <!-- <h2>Search Again</h2> -->

                <form method="get" action="/Software-Discovery-Portal/resources" _lpchecked="1">
                    <div class="input-group">
                        <input type="text" class="form-control" name="query" placeholder="<?php echo $component->text1 ?>" value="">
                        <!-- <input type="hidden" name="filter" value=""> -->
                                <!-- Added by Manu -->
                        <!-- <input type="hidden" name="status" value="curated"> -->
                                <!-- Added by Manu -->

                        <span class="input-group-btn">
                            <button class="btn-u" type="search"><i class="fa fa-search"></i></button>
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
</div>
