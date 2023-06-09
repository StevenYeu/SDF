<?php
$holder = new Category();
$return = $holder->getUsed();

$communities = array();
$sources = array();
$colors = array();
$who = array();
$all_results_sources = $results['sources'];
foreach ($return as $cat) {
    if(!isset($all_results_sources[$cat->source])) continue;
    if (!$communities[$cat->cid]) {
        $comm = new Community();
        $comm->getByID($cat->cid);
        $communities[$cat->cid] = $comm;
    }
    if ($communities[$cat->cid]->private == 1) continue;
    if (!isset($sources[$cat->source]) || !in_array($cat->cid, $sources[$cat->source])) {
        $sources[$cat->source][] = $cat->cid;
        $colors[$cat->source][] = $communities[$cat->cid]->communityColor();
        $who[$cat->source][] = $communities[$cat->cid];
    }
}

?>

<div class="container s-results margin-bottom-50">
    <div class="row">
        <div class="col-md-2 hidden-xs related-search">
            <div class="row" style="margin-top:10px">
                <div class="col-md-12 col-sm-4">
                    <h3 class="tut-options">Options</h3>
                    <ul class="list-unstyled">
                        <li><a href="javascript:void(0)" class="sort-popular"><i class="fa fa-sort-numeric-desc"></i> Sort By Most Included</a></li>
                        <?php

                        $newVars = $vars;
                        $newVars['sort'] = 'rel';
                        echo '<li><a href="' . $search->generateURL($newVars) . '"><i class="fa fa-sort-amount-desc"></i> Sort By Most Recently Updated</a></li>';

                        $newVars = $vars;
                        $newVars['sort'] = 'alph';
                        echo '<li><a href="' . $search->generateURL($newVars) . '"><i class="fa fa-sort-alpha-asc"></i> Sort Alphabetically</a></li>';

                        if ($search->preference == 'off') {
                            $newVars = $vars;
                            $newVars['preference'] = null;
                            echo '<li><a href="' . $search->generateURL($newVars) . '"><i class="fa fa-star"></i> Show Community Sources First</a></li>';
                        } else {
                            $newVars = $vars;
                            $newVars['preference'] = 'off';
                            echo '<li><a href="' . $search->generateURL($newVars) . '"><i class="fa fa-star-o"></i> Show All Sources Equally</a></li>';
                        }?>
                    </ul>
                    <hr/>
                </div>
                <?php
                echo '<div class="col-md-12 col-sm-4">';
                echo '<h3 class="tut-categories">Categories</h3>';
                echo '<ul class="list-group sidebar-nav-v1" id="sidebar-nav">';

                foreach ($results['categories'] as $parent => $array) {
                    $parent_href = str_replace(Array(" ", "/"), "_", $parent);
                    echo '<li class="list-group-item list-toggle" href="#collapse-' . $parent_href . '" data-toggle="collapse">';
                    echo '<a class="accordion-toggle"  href="javascript:void(0)">' . $parent . '</a>';
                    echo '<ul id="collapse-' . $parent_href . '" class="collapse">';
                    ksort($array);
                    foreach ($array as $cat => $count) {
                        $newVars = $vars;
                        $newVars['parent'] = $parent;
                        $newVars['child'] = $cat;
                        if ($vars['parent'] == $parent && $cat == $vars['child'])
                            echo '<li class="active category-li"><a class="category-choose" href="' . $search->generateURL($newVars) . '" parent="' . $parent . '" child="' . $cat . '">' . $cat . ' (<span class="category-number">' . number_format($count) . '</span>)</a></li>';
                        else
                            echo '<li class="category-li"><a class="category-choose" href="' . $search->generateURL($newVars) . '" parent="' . $parent . '" child="' . $cat . '">' . $cat . ' (<span class="category-number">' . number_format($count) . '</span>)</a></li>';
                    }
                    echo '</ul></li>';
                }

                if (count($communities) > 0) {
                    echo '<li class="list-group-item list-toggle" href="#collapse-communities" data-toggle="collapse">';
                    echo '<a class="accordion-toggle"  href="javascript:void(0)">Used By</a>';
                    echo '<ul id="collapse-communities" class="collapse">';
                    foreach ($communities as $comm) {
                        if($comm->private==1 || $comm->name == "")
                            continue;
                        $newVars = $vars;
                        echo '<li class="category-li-comm"><a href="javascript:void(0)" class="category-choose-comm" community="' . $comm->id . '"><i class="fa fa-square" style="color:'.$comm->communityColor().'"></i> ' . $comm->shortName . '</a></li>';
                    }
                    echo '</ul></li>';
                }
                echo '</ul></div><hr/>';
                ?>
            </div>
        </div>
        <!--/col-md-2-->

        <div class="col-md-10">
            <div>
                <h4>
                    <a class="new-interface" href="javascript:void(0)"><span class="label label-danger">NEW</span></a>
                    We are transitioning to a new interface for data results.
                    <a class="new-interface" href="javascript:void(0)"><button class="btn btn-primary">Use new interface</button></a>
                </h4>
            </div>
            <hr/>
            <span class="results-number" style="margin-top:10px">
                <?php echo $search->getResultText('data', array($results['count'], count($results['sources']), $GLOBALS["notif_id"], $subscription_data["modified_time"]), $results['expansion'], $vars) ?>
            </span>
            <!-- Begin Inner Results -->

            <?php

            if ($search->sort == 'alph')
                $useArray = $results['alphabetical'];
            else
                $useArray = $results['cover'];

            if($search->sort == "rel") {
                uksort($useArray, function($a, $b) {
                    $sa = $allSources[$a];
                    $sb = $allSources[$b];
                    if($sa->data_last_updated > $sb->data_last_updated) return -1;
                    if($sa->data_last_updated < $sb->data_last_updated) return 1;
                    return 0;
                });
            }

            //print_r($useArray);
            $circle_counter = 0;	// added so that each circle will have a unique id
            foreach ($useArray as $nif => $value) {	// build the normal results
                //echo $nif.':'.$value;
                if ($search->sort == 'alph') {
                    $num = $nif;
                    $nif = $value;
                    $array = $results['sources'][$nif];
                } else {
                    $array = $results['sources'][$nif];
                }
                $source = $allSources[$nif];
                //print_r($array);
                buildInnerResults($source, $sources, $nif, $who, $array, $colors, $vars, $community, $search, "normal", $circle_counter++, $GLOBALS["notif_id"], $GLOBALS["notif_email"]);
            }
            foreach($results['hidden-sources'] as $nif => $array){	// build the hidden results
                $source = $allSources[$nif];
                foreach($array as $a){
                    buildInnerResults($source, $sources, $nif, $who, $a, $colors, $vars, $community, $search, "hidden", $circle_counter++, $GLOBALS["notif_id"], $GLOBALS["notif_email"]);
                }
            }
            ?>



            <div class="margin-bottom-30"></div>

        </div>
        <!--/col-md-10-->
    </div>
</div><!--/container-->
<?php echo \helper\htmlElement("collection-modals", Array("user" => $_SESSION["user"], "community" => $community)); ?>
<ol id="joyRideTipContent">
    <li data-class="community-logo" data-text="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2><?php echo $community->name?> Resources</h2>
        <p>
            Welcome to the <?php echo $community->shortName?> Resources search. From here you can search through
            a compilation of resources used by <?php echo $community->shortName?> and see how data is organized within
            our community.
        </p>
    </li>
    <li data-class="data-tab" data-button="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Navigation</h2>
        <p>
            You are currently on the More Resources tab looking through all the sources that SciCrunch has to offer. From
            here you can see which data sources have data pertaining to your search and explore the data in that source
            by clicking on it.
        </p>
    </li>
    <li data-class="btn-login" data-button="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Logging in and Registering</h2>
        <p>
            If you have an account on <?php echo $community->shortName ?> then you can log in from here to get additional
            features in <?php echo $community->shortName ?> such as Collections, Saved Searches, and managing Resources.
        </p>
    </li>
    <li data-class="searchbar" data-button="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Searching</h2>
        <p>
            Here is the search term that is being executed, you can type in anything you want to search for. Some tips
            to help searching:
        </p>
        <ol>
            <li style="color:#fff">Use quotes around phrases you want to match exactly</li>
            <li style="color:#fff">You can manually AND and OR terms to change how we search between words</li>
            <li style="color:#fff">You can add "-" to terms to make sure no results return with that term in them (ex. Cerebellum -CA1)</li>
            <li style="color:#fff">You can add "+" to terms to require they be in the data</li>
            <li style="color:#fff">Using autocomplete specifies which branch of our semantics you with to search and can help refine your search</li>
        </ol>
    </li>
    <li data-class="tut-saved" data-button="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Save Your Search</h2>
        <p>
            You can save any searches you perform for quick access to later from here.
        </p>
    </li>
    <li data-class="tut-expansion" data-button="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Query Expansion</h2>
        <p>
            We recognized your search term and included synonyms and inferred terms along side your term to help get
            the data you are looking for.
        </p>
    </li>
    <li data-class="circle-container" data-button="Next" data-options="tipLocation:right;tipAnimation:fade">
        <h2>Who is Using This</h2>
        <p>
            Each community in <?php echo $community->shortName ?> is using a set of sources, by clicking on these you can see what communities
            are using this source.
        </p>
    </li>
    <li data-class="tut-categories" data-button="Next" data-options="tipLocation:right;tipAnimation:fade">
        <h2>Categories</h2>
        <p>
            Here are the categories that we have tagged these sources with to filter your search on.
        </p>
    </li>
    <li data-class="tut-options" data-button="Next" data-options="tipLocation:right;tipAnimation:fade">
        <h2>Options</h2>
        <p>
            Here are the options you have for sorting your results.
        </p>
    </li>
    <li data-class="tutorial-btn" data-button="Done" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Further Questions</h2>
        <p>
            If you have any further questions please check out our
            <a href="/<?php echo $community->portalName ?>/about/faq">FAQs Page</a> to ask questions and see our tutorials.
            Click this button to view this tutorial again.
        </p>
    </li>
</ol>


<?php
    if(!is_null($category_filter)) {
        $cat_filter = explode(":", $category_filter);
        if(count($cat_filter) == 2) {
?>
            <script>
                $(function() {
                    $(".inner-hidden-results").hide();
                    $('.category-choose[parent="<?php echo $cat_filter[0] ?>"][child="<?php echo $cat_filter[1] ?>"]').click();
                    $('#collapse-<?php echo str_replace(Array(" ", "/"), "_", $cat_filter[0]) ?>').collapse("show");
                });
            </script>
<?php
        }
    } else {
?>
    <script>
        $(function() {
            $(".inner-hidden-results").hide();
        });
    </script>
<?php
    }
?>
<script>
    $(".new-interface").click(function(e) {
        deleteCookie("old-interface-data");
        window.location.reload(false);
    });
</script>


<?php
function buildInnerResults($source, $sources, $nif, $who, $array, $colors, $vars, $community, $search, $type, $circle_counter, $subid = NULL, $subemail = false){
    $subtext = is_null($subid) ? "" : "&notif=" . $subid;
    if($subemail) $subtext .= "&notif_email";
    $newVars = $vars;
    $newVars['nif'] = $nif;
    $table_url = $search->generateURL($newVars) . $subtext;
    $newVars = $vars;
    $newVars["parent"] = $array["parent"];
    $newVars["child"] = $array["child"];
    $parent_url = $search->generateURL($newVars) . $subtext;
    $type_results = $type == "hidden" ? "inner-results inner-hidden-results" : "inner-results";

    echo '<div class="' . $type_results . '" popularity="'.count($sources[$nif]).'" count="'.$array['count'].'" parent="' . $array['parent'] . '" child="' . $array['child'] . '" comms="'.join(' ',$sources[$nif]).'">';
    echo '<div class="the-title">';
    echo '<a href="' . $table_url . '"><h3 style="display:inline-block"><i class="fa fa-table"></i> ' . $array['name'];
    if(is_null($subid)) echo ' (' . number_format($array['count']) . ')';
    echo '</h3></a>';
    echo \helper\htmlElement("archived-source-warning", Array("viewid" => $nif));
    if(count($colors[$nif])>0){
        $names_counter = 0;
        foreach($colors[$nif] as $i=>$color) {
            if($who[$nif][$i]->name) $names_counter += 1;
        }
        echo '<div class="circle-container body-hide"><div class="circle" style="display:inline-block;margin-left:10px;vertical-align:middle" id="circle-'.$nif.$circle_counter.'" num="'.$names_counter.'" colors="'.join(',',$colors[$nif]).'"></div>';
        echo '<div class="who-container no-propagation shadow-effect-1"><h3 align="center" style="margin:0;text-decoration: underline">Used in</h3>';
        foreach($colors[$nif] as $i=>$color){
            if(!$who[$nif][$i]->name) continue;
            if($who[$nif][$i]->id==$community->id)
                echo '<div><i class="fa fa-square" style="color:'.$color.'"></i> '.$who[$nif][$i]->name.'</div>';
            else
                echo '<div><a target="_blank" href="'.$who[$nif][$i]->fullURL().'"><i class="fa fa-square" style="color:'.$color.'"></i> '.$who[$nif][$i]->name.'</a></div>';
        }
        echo '</div></div>';
    }
    echo \helper\htmlElement("collection-bookmark", Array("user" => $_SESSION["user"], "uuid" => $nif, "community" => $community, "view" => "view"));
    echo '</div>';
    echo '<ul class="list-inline up-ul">';
    echo '<li><a target="_blank" href="/'.$community->portalName.'/about/sources/'.$nif.'">' . $source->source . '</a>‎</li>';

    echo '</ul>';
    echo '<div class="overflow-h">';
    if (strlen($source->image) > 20)
        $imageSrc = $source->image;
    else $imageSrc = '/upload/source-images/notfound.gif';
    echo '<a target="_blank" href="/'.$community->portalName.'/about/sources/'.$nif.'"><img src="' . $imageSrc . '" alt=""></a>';
    echo '<div class="overflow-a">';
    echo '<p>' . $source->description . '</p>';
    echo '<ul class="list-inline down-ul">';
    echo '<li><a href="' . $parent_url . '">' . $array['child'] . '</a></li>';
    if(is_null($subid)) {
        echo '<li>-</li>';
        echo '<li><a href="'.$table_url.'">'.$array['cover'].' ('.number_format($array['count']).' results)</a></li>';
    }
    echo '</ul>';
    echo '</div></div><hr/></div>';
}
?>
