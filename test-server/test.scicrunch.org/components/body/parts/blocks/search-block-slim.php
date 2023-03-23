<?php
if ($vars['l'] && $vars['l']!='') $input_val = $vars['l'];
elseif ($vars['q']) $input_val = $vars['q'];
else $input_val = "";
$input_val = str_replace('"', '&quot;', $input_val);
?>
<style>
    <?php if($component->color1){?>
    .search-block-v2 h2 {
        color: <?php echo '#'.$component->color1?>;
    }

    <?php } ?>
    <?php if($component->color2){?>
    .search-block-v2 .form-submit-button {
        background: <?php echo '#'.$component->color2?>;
    }

    <?php } ?>

    .search-block-save-search-btn {
        background: #<?php echo $component->color3 ? $component->color3 : "9B6BCC"; ?>;
        color: #FFF;
    }
</style>

<div class="search-block-v2 <?php if($vars['editmode']) echo 'editmode' ?>" style="margin-bottom:10px">
    <div class="container">
        <div class="col-md-8 col-md-offset-2">
            <form class="page-search-form1 submit-class">
                <div class="input-group">
                    <span class="input-group-btn">
                        <div class="btn btn-info help-tooltip-btn" data-name="search-tips.html">
                            <i class="fa fa-question-circle"></i>
                        </div>
                    </span>
                    <input type="text" class="form-control searchbar" id="search-banner-input" name="l" value="<?php echo $input_val ?>">
                    <input type="hidden" id="autoValues"/>
                    <input type="hidden" class="category-input" value="<?php echo $vars['category']?>"/>
                    <input type="hidden" class="subcategory-input" value="<?php echo $vars['subcategory']?>"/>
                    <input type="hidden" class="source-input" value="<?php echo $vars['nif']?>"/>
                    <input type="hidden" class="search-community" value="<?php echo $community->portalName?>"/>
                    <input type="hidden" class="stripped-community" value="<?php echo $vars['stripped'] ?>"/>
                    <div class="autocomplete_append auto" style="z-index:10"></div>
                    <span class="input-group-btn">
                        <button class="btn-u form-submit-button" type="button" style="height:34px;"><i class="fa fa-search"></i></button>
                    </span>
                    <!--<span class="input-group-btn">
                        <span style="margin-left:10px" class="btn btn-info tutorial-btn">Get a Tour</span>
                    </span>-->
                    <span class="input-group-btn">
                        <div class="btn-group tut-saved" style="margin-left:20px">
                            <button type="button" class="btn dropdown-toggle search-block-save-search-btn" data-toggle="dropdown" title="Save your search to receive notifications when there are updates">
                                <i class="fa fa-floppy-o"></i> Save this search
                            </button>
                            <ul class="dropdown-menu" role="menu" style="text-align: left">
                                <?php
                                if(isset($_SESSION['user'])){
                                    $holder = new Saved();
                                    $searches = $holder->getUserSearches($_SESSION['user']->id);
                                    if(count($searches)>0){
                                        foreach($searches as $saved){
                                            echo '<li><a href="'.$saved->returnURL(NULL, $community).'"><i class="fa fa-share"></i> '.$saved->name.'</a></li>';
                                        }
                                    } else {
                                        echo '<li><a>No Saved Searches</a></li>';
                                    }
                                    echo '<li class="divider"></li>';
                                    echo '<li><a href="javascript:void(0)" class="save-search"><i class="fa fa-floppy-o"></i> Save This Search</a></li>';
                                    echo '<li><a href="/'.$community->portalName.'/account/saved"><i class="fa fa-cogs"></i> Manage Saved Searches</a></li>';
                                } else {
                                    echo '<li><a href="javascript:void(0)" class="btn-login">Login to Save Searches</a></li>';
                                }
                                ?>
                            </ul>
                        </div>
                    </span>
                </div>
            </form>
            <?php if(isset($search) && isset($results["expansion"]) ) echo $search->getExpansionResultText($results["expansion"], $vars) ?>
        </div>
    </div>

    <?php if ($vars['editmode']) {
        echo '<div class="body-overlay"><h3>' . $component->component_ids[$component->component] . '</h3>';
        echo '<div class="pull-right">';
        echo '<button class="btn-u btn-u-default edit-body-btn" componentType="other" componentID="'.$component->id.'"><i class="fa fa-cogs"></i><span class="button-text"> Edit</span></button></div>';
        echo '</div>';
    } ?>
</div><!--/container-->
