<?php
    //if nothing searched, the query is empty string
    if(!isset($query)) $query = "";
    //if nothing displayed, set to query
    if(!isset($display_query)) $display_query = $query;
?>

<!--copies the image/colors of component to search style-->
<style>

    <?php if($component->image){ ?>
    .search-block {
        background: url('/upload/community-components/<?php echo $component->image ?>') 50% 0 fixed;
    }
    <?php } ?>
    <?php if($component->color1){ ?>
    .search-block form.page-search-form .checkbox, .search-block h1 {
        color: <?php echo '#'.$component->color1 ?>
    }

    <?php } ?>
    .search-icon, .search-icon:hover, .search-banner-category-button {
        background: <?php echo $component->color2 ? '#'.$component->color2 : "#72c02c" ?>;
        color: #FFFFFF;
    }

    .search-banner-category-button {
        margin-bottom: 5px;
    }

    <?php if($component->color3){ ?>
    .save-icon, .save-icon:hover, .save-icon:active, .open .dropdown-toggle.save-icon, .save-icon:focus {
        background: <?php echo '#'.$component->color3 ?>
    }

    <?php } ?>

    .search-block {
        margin-bottom: 0;
    }
</style>

<!--=== Search Block ===-->
<div class="search-block parallaxBg <?php if ($vars['editmode']) echo 'editmode' ?>">
    <div class="container">
        <div class="col-md-8 col-md-offset-2">
            <h1><?php echo $component->text1 ?></h1>
            <form action="" class="sky-form page-search-form">
                <input type="hidden" class="search-community" value="<?php echo $community->portalName ?>"/>
                <input type="hidden" class="stripped-community" value="<?php echo $vars['stripped'] ?>"/>
                <div class="input-group">
                    <!--if not scicrunch comm, show tooltip search button-->
                    <?php if($community->id != 0): ?>
                        <span class="input-group-btn">
                            <div class="btn btn-info help-tooltip-btn" data-name="search-tips.html"><i class="fa fa-question-circle"></i></div>
                        </span>
                    <?php endif ?>
                    <input type="text" class="form-control searchbar" style="height:34px" id="search-banner-input" name="l" placeholder="<?php echo $component->text2 ?>" value="<?php echo $display_query ?>">
                    <input type="hidden" id="autoValues" />

                    <div class="autocomplete_append auto" style="z-index:10"></div>
                    <!--to submit the search-->
                    <span class="input-group-btn">
                        <button class="btn search-icon" type="submit"><i class="fa fa-search"></i></button>
                    </span>
                </div>
                 <?php if($community->id != 0 && $community->id != 72): ?>
                    <!--checkboxes-->
                    <div class="btn-group">
                        <a href="<?php echo Community::fullURLStatic($community) ?>/Any/search?q=%2A&l=">
                            <div class="btn search-banner-category-button">
                                Any
                            </div>
                        </a>
                        <?php
                            $first = true;
                        ?>
                        <?php foreach ($community->urlTree as $cat => $array): ?>
                            <?php
                                $checked_string = "";
                                if($first && $one_group) $checked_string = "checked";
                            ?>
                                <a class="search-banner-category" href="javascript:void(0)" data-href="<?php echo Community::fullURLStatic($community) . '/' . $cat ?>/search?l=&q=">
                                    <div class="btn search-banner-category-button">
                                        <?php echo $cat ?>
                                    </div>
                                </a>
                            <?php
                                $first = false;
                            ?>
                        <?php endforeach ?>
                    </div>
                 <?php endif ?>
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
