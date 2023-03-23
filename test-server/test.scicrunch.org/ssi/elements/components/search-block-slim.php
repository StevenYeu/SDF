<?php

$community = $data["community"];
$user = $data["user"];
$vars = $data["vars"];
$search = $data["search"];
$expansion = $data["expansion"];

$synonyms = "true";
if(isset($_GET["exclude"]) && $_GET["exclude"] == "synonyms") $synonyms = "false";

$acronyms = "false";
if(isset($_GET["include"]) && $_GET["include"] == "acronyms") $acronyms = "true";

$component = $community->components["search"][0];
if(!$component) {
    return;
}

if($vars["l"]) {
    $input_val = $vars["l"];
} elseif($vars["q"]) {
    $input_val = $vars["q"];
} else {
    $input_val = "";
}

$viewid = $vars["nif"];

$input_search_filters = Array();
if($vars["filter"]) {
    foreach($vars["filter"] as $vf) {
        $filter_array = explode(":", $vf);
        if(count($filter_array) == 2) {
            $input_search_filters[$filter_array[0]] = $filter_array[1];
        }
    }
}

if($community->rinStyle() && $vars["category"] != "literature") {
    $search_manager = ElasticRRIDManager::managerByViewID($viewid);
    $filter_fields = Array();
    if($search_manager && $search_manager->fields()) {
        foreach($search_manager->fields() as $filter) {
            if($filter->visible("snippet-filter")) {
                $filter_fields[] = $filter;
            }
        }
    }
}

if($community->rinStyle() && $search->source) {
    $search_tips_template = "rin-search-tips.html";
} else if($vars["type"] == "interlex"){
    $search_tips_template = "interlex-search-tips.html";
} else {
    $search_tips_template = "search-tips.html";
}

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
        border-radius: 0;
    }

    .search-block-reset-search-btn {
        background-color: #408DC9;
        color: white;
        border-radius: 0;
    }
</style>

<div class="search-block-v2 <?php if($vars["editmode"]) echo 'editmode' ?>" style="margin-bottom:10px">
    <div class="container">
        <div>
            <form class="page-search-form1 submit-class">
                <?php if(!empty($filter_fields)): ?>
                    <div>
                        <p class="search-section-header" style="display: inline-block">Suggested Search Criteria</p>
                        <p style="display: inline-block">Enter extra filters to help narrow your search</p>
                    </div>
                    <div class="form-inline margin-bottom-20">
                        <?php foreach($filter_fields as $filter): ?>
                            <?php if(!$filter->visible("snippet-filter")) continue; ?>
                            <div class="form-group">
                                <label><?php echo $filter->name ?></label>
                                <input type="text" name="filter-<?php echo $filter->name ?>" class="form-control search-block-filter" data-filtername="<?php echo $filter->name ?>" value="<?php echo htmlspecialchars($input_search_filters[$filter->name]) ?>" />
                            </div>
                        <?php endforeach ?>
                    </div>
                <?php endif ?>
                <div>
                    <p class="search-section-header" style="display: inline-block">Search</p>
                    <p style="display: inline-block">Type in a keyword to search</p>
                </div>
                <div class="input-group">
                    <span class="input-group-btn">
                        <div class="btn btn-info help-tooltip-btn" data-name="<?php echo $search_tips_template ?>">
                            <i class="fa fa-question-circle"></i>
                        </div>
                    </span>
                    <input type="text" class="form-control searchbar" id="search-banner-input" name="l" value="<?php echo htmlspecialchars($input_val) ?>">
                    <input type="hidden" id="autoValues"/>
                    <input type="hidden" class="title-input" value="<?php echo htmlspecialchars($vars["title"]) ?>"/>
                    <input type="hidden" class="type-input" value="<?php echo htmlspecialchars($vars["type"]) ?>"/>
                    <input type="hidden" class="category-input" value="<?php echo htmlspecialchars($vars["category"]) ?>"/>
                    <input type="hidden" class="subcategory-input" value="<?php echo htmlspecialchars($vars["subcategory"]) ?>"/>
                    <input type="hidden" class="source-input" value="<?php echo htmlspecialchars($vars["nif"]) ?>"/>
                    <input type="hidden" class="search-community" value="<?php echo htmlspecialchars($community->portalName) ?>"/>
                    <input type="hidden" class="stripped-community" value="<?php echo htmlspecialchars($vars["stripped"]) ?>"/>
                    <input type="hidden" class="data-sources" value="<?php echo htmlspecialchars($vars["sources"]) ?>"/>
                    <input type="hidden" class="results-types" value="<?php echo $vars['results-types'] ?>"/>
                    <div class="autocomplete_append auto" style="z-index:10"></div>
                    <span class="input-group-btn">
                        <input type="submit" class="btn-u form-submit-button" type="button" value="Search" style="height:34px;" />
                    </span>
                    <!--<span class="input-group-btn">
                        <span style="margin-left:10px" class="btn btn-info tutorial-btn">Get a Tour</span>
                    </span>-->
                    <span class="input-group-btn">
                        <div class="btn-group tut-saved" style="margin-left:20px">
                            <button type="button" class="btn dropdown-toggle search-block-save-search-btn" data-toggle="dropdown" title="Save your search to receive notifications when there are updates">
                                <i class="fa fa-floppy-o"></i> Save search
                            </button>
                            <ul class="dropdown-menu" role="menu" style="text-align: left">
                                <?php if($user): ?>
                                    <?php
                                        $holder = new Saved();
                                        $searches = $holder->getUserSearches($user->id);
                                    ?>
                                    <?php if(count($searches) > 0): ?>
                                        <?php foreach($searches as $saved): ?>
                                            <li><a href="<?php echo $saved->returnURL(NULL, $community) ?>"><i class="fa fa-share"></i> <?php echo $saved->name ?></a></li>
                                        <?php endforeach ?>
                                    <?php else: ?>
                                        <li><a>No Saved Searches</a></li>
                                    <?php endif ?>
                                    <li class="divider"></li>
                                    <li><a href="javascript:void(0)" class="save-search"><i class="fa fa-floppy-o"></i> Save Search</a></li>
                                    <li><a href="/<?php echo htmlspecialchars($community->portalName) ?>/account/saved"><i class="fa fa-cogs"></i> Manage Saved Searches</a></li>
                                <?php else: ?>
                                    <li><a href="javascript:void(0)" class="btn-login">Login to Save Searches</a></li>
                                <?php endif ?>
                            </ul>
                        </div>
                        <div class="btn-group tut-saved" style="margin-left:10px">
                            <a target="_self" href="<?php echo $search->generateURLFromDiff(Array("q" => "", "l" => "", "filter" => Array(), "facet" => Array(), "sort" => null, "column" => null, "page" => 1, "exclude" => null, "include" => null, "type" => $vars["type"])); ?>" class="btn search-block-reset-search-btn">
                                <i class="fa fa-undo"></i> Reset search
                            </a>
                        </div>
                    </span>
                </div>
            </form>
            <?php if(!is_null($search) && !is_null($expansion)) echo $search->getExpansionResultText($expansion, $vars) ?>
            <?php if($vars["category"] == "data" && $vars["q"] != "*" && !isset($vars["nif"])): ?>
                <div class="row">
                    <div class="col-md-2">
                        <div class="checkbox">
                            &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input id="ScheckBox" type="checkbox"> Synonyms
                        </div>
                    </div>
                    <div class="col-md-1">
                        <div class="checkbox">
                            <input id="AcheckBox" type="checkbox"> Acronyms
                        </div>
                    </div>
                </div>
            <?php endif ?>
        </div>
    </div>

    <?php if ($vars["editmode"]): ?>
        <div class="body-overlay"><h3><?php htmlspecialchars($component->component_ids[$component->component]) ?></h3>
            <div class="pull-right">
                <button class="btn-u btn-u-default edit-body-btn" componentType="other" componentID="<?php echo $component->id ?>">
                    <i class="fa fa-cogs"></i><span class="button-text"> Edit</span>
                </button>
            </div>
        </div>
    <?php endif ?>
</div><!--/container-->

<script>

$(function(){
    var synonyms_checked_flag = false;
    var synonyms = "<?php echo $synonyms ?>";
    if(synonyms == "true") synonyms_checked_flag = true;
    $('#ScheckBox').prop('checked', synonyms_checked_flag || false);
});

$('#ScheckBox').on('change', function() {
    localStorage.checked = $(this).is(':checked');
    if(localStorage.checked == "true") window.location = window.location.href.replace("&exclude=synonyms", "");
    else window.location = window.location.href.replace("#", "&exclude=synonyms#");
});

$(function(){
    var acronyms_checked_flag = false;
    var acronyms = "<?php echo $acronyms ?>";
    if(acronyms == "true") acronyms_checked_flag = true;
    $('#AcheckBox').prop('checked', acronyms_checked_flag || false);
});

$('#AcheckBox').on('change', function() {
    localStorage.checked = $(this).is(':checked');
    if(localStorage.checked == "true") window.location = window.location.href.replace("#", "&include=acronyms#");
    else window.location = window.location.href.replace("&include=acronyms", "");
});

</script>
