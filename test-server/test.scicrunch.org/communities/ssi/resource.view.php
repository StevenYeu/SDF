<?php

function allNotSameCategory($cat_source){
    if(count($cat_source) <= 1) return false;
    $first = NULL;
    foreach($cat_source as $cat){
        $split = explode("|", $cat);
        if(is_null($first)){
            $first = $split[0];
        }elseif($first != $split[0]){
            return true;
        }
    }
    return false;
}

if($results["info"]["single-source"]) {
    $category_graph_tree = $results['graph'];
} else {
    $category_graph_tree = $results["info"]["tree"];
}

?>

    <script>
        document.categoryGraphData = <?php echo json_encode($category_graph_tree) ?>;
    </script>
    <?php if($search->page >= Search::MAX_PAGE) echo \helper\htmlElement("too-many-pages", Array("max_page" => Search::MAX_PAGE)) ?>
    <div class="container s-results margin-bottom-50">
    <div class="row">
    <div class="col-md-2 hidden-xs related-search">
        <div class="row" style="margin-top:10px">
            <div class="col-md-12 col-sm-4">
                <?php

                    // checks if the scicrunch registry is only source
                    $source_count = 0;
                    foreach ($results['info']['counts']['nif'] as $nif => $count) {
                        if($count > 0) $source_count += 1;
                    }
                    if(!!$results['info']['counts']['nif']['nlx_144509-1'] && $source_count == 1 && $community->id == 0) {
                        echo \helper\htmlElement("add-resource-button");
                    }

                ?>
                <hr/>
                <h3>Options</h3>
                <ul class="list-group">
                    <li class="list-group-item"><a href="javascript:categoryGraph2(document.categoryGraphData)">Category Graph <i class="fa fa-graph"></i></a></li>
                    <?php if (isset($_SESSION['user'])) { ?>
                        <li class="list-group-item"><a href="javascript:void(0)" class="simple-toggle" modal=".new-collection">Create New Collection</a></li>
                        <li class="list-group-item"><a href="javascript:void(0)" class="simple-toggle" modal=".add-all">Add All on Page to a Collection</a></li>
                    <?php } else { ?>
                        <li class="list-group-item"><a href="#" class="btn-login">Log in for Collection Options</a></li>
                    <?php } ?>
                    <li class="list-group-item"><?php echo \helper\htmlElement("modified-date-picker"); ?></li>
                    <?php if(strpos(http_build_query($_GET), "v_status:") === false): ?>
                        <li class="list-group-item"><?php echo \helper\htmlElement("new-records-link", Array("vars" => $vars, "search" => $search)); ?></li>
                    <?php endif ?>
                </ul>
                <hr/>
            </div>
            <div class="col-md-12 col-sm-4">
                <?php echo $search->currentFacets($vars, 'table') ?>
            </div>
            <?php
            if (count($community->urlTree[$vars['category']]['subcategories']) > 0) {
                echo '<div class="col-md-12 col-sm-4">';
                echo '<h3 class="tut-subcategories">Subcategories</h3>';
                echo '<ul class="list-unstyled">';
                foreach ($community->urlTree[$vars['category']]['subcategories'] as $sub => $array) {
                    $newVars = $vars;
                    $newVars["page"] = 1;
                    $newVars['subcategory'] = $sub;
                    if ($vars['subcategory'] && $sub == $vars['subcategory'])
                        echo '<li class="active"><a href="' . $search->generateURL($newVars) . '">' . $sub . ' (' . number_format($results['info']['counts']['subs'][$sub]) . ')</a></li>';
                    elseif($vars['subcategory'])
                        echo '<li><a href="' . $search->generateURL($newVars) . '">' . $sub . '</a></li>';
                    else
                        echo '<li><a href="' . $search->generateURL($newVars) . '">' . $sub . ' (' . number_format($results['info']['counts']['subs'][$sub]) . ')</a></li>';
                }
                echo '</ul><hr/></div>';
            } elseif ($vars['category'] == 'Any') {
                echo '<div class="col-md-12 col-sm-4">';
                echo '<h3 class="tut-categories">Categories</h3>';
                echo '<ul class="list-unstyled" id="sidebar-categories-list">';
                foreach ($results['info']['counts']['subs'] as $sub => $count) {
                    if($count == 0) continue;
                    $splits = explode('|', $sub);
                    if (!isset($cate[$splits[0]]))
                        $cate[$splits[0]] = $count;
                    else
                        $cate[$splits[0]] += $count;
                }
                foreach ($cate as $category => $count) {
                    $newVars = $vars;
                    $hasSubs = count($community->urlTree[$category]['subcategories']) > 0;
                    $newVars["page"] = 1;
                    $newVars['category'] = $category;
                    echo '<li><span class="glyphicon ' . ($hasSubs > 0 ? 'glyphicon-plus' : 'glyphicon-none') . 
                    '"></span> <a href="' . $search->generateURL($newVars) . '">' 
                    . $category . ' (' . number_format($count) . ')</a>';
                    if ($hasSubs) {
                        echo '<ul style="list-style-type:none;" class="collapse">';
                        $realCategory = $vars['category'];
                        $vars['category'] = $category;
                        foreach ($community->urlTree[$category]['subcategories'] as $sub => $array) {
                            $newVars = $vars;
                            $newVars["page"] = 1;
                            $newVars['subcategory'] = $sub;
                            $count = number_format($results['info']['counts']['subs'][$category . '|' . $sub]);
                            echo '<li><a href="' . $search->generateURL($newVars) . '">' . $sub . ($count ? (' (' . $count . ')') : '') . '</a></li>';
                        }
                        $vars['category'] = $realCategory;
                        echo '</ul>';
                    }
                    echo '</li>';
                }
                echo '</ul><hr/></div>';
            }
            ?>
            <div class="col-md-12 col-sm-4">
                <h3 id="sources-list" class="tut-sources">Sources</h3>
                <ul class="list-unstyled">
                    <?php
                    // print_r($results['info']['counts']['nif']);
                    $used_sources_count = 0;
                    foreach ($results['info']['counts']['nif'] as $nif => $count) {
                        if($count == 0) continue;
                        $used_sources_count += 1;
                        $source = $allSources[$nif];
                        $newVars = $vars;
                        $newVars["page"] = 1;
                        $newVars['nif'] = $nif;
                        $category_source = $results['info']['nifDirect'][(string)$nif];
                        if (!in_array('CURRENT', $category_source)) {
                            $splits = explode('|', $category_source[0]);    // just check the first one
                            if(allNotSameCategory($category_source)){
                                $newVars['category'] = "Any";
                            }
                            elseif(count($splits) > 1) {
                                $newVars['category'] = $splits[0];
                                if(count($category_source) == 1) $newVars['subcategory'] = $splits[1];
                            }elseif(count($category_source) == 1){
                                if ($search->category == 'Any'){
                                    $newVars['category'] = $category_source[0];
                                }else{
                                    $newVars['subcategory'] = $category_source[0];
                                }
                            }
                        }
                        echo '<li><a href="' . $search->generateURL($newVars) . '"> <i class="fa fa-table"></i> ' . $source->getTitle() . ' (' . number_format($count) . ')</a></li>';
                    }
                    ?>
                </ul>
                <hr>
                <?php echo \helper\htmlElement("recent-searches-list", Array("recent-searches" => $_SESSION["recent-searches"], "community" => $community)) ?>
            </div>

            <?php if($results["info"]["single-source"]): ?>

                <div class="col-md-12 col-sm-4">
                    <?php echo \helper\htmlElement("view-facets", Array("results" => $results, "search" => $search, "vars" => $vars)); ?>
                    <hr style="margin-top:10px;margin-bottom:15px;"/>
                </div>

            <?php endif ?>
        </div>
    </div>
    <!--/col-md-2-->

    <div class="col-md-10">
        <!--<div>
            <h4>
                <a class="new-interface" href="javascript:void(0)"><span class="label label-danger">NEW</span></a>
                We are transitioning to a new interface for community resource results.
                <a class="new-interface" href="javascript:void(0)"><button class="btn btn-primary">Use new interface</button></a>
            </h4>
        </div>-->
        <span class="results-number" style="margin-top:10px;">
            <?php echo $search->getResultText('resource', array(count($results['results']), $results['total'], $used_sources_count, $GLOBALS["notif_id"], $subscription_data["modified_time"]), $results['expansion'], $vars); ?>
        </span>
        <!-- Begin Inner Results -->

        <?php
        //print_r($results);
        $uuids = array();
        $theViews = array();
        foreach ($results['results'] as $array) {
            $uuids[] = $array['uuid'];
            $theViews[] = $array['nif'];
            $source = $allSources[$array['nif']];
            $result_count = $results['info']['counts']['nif'][$array['nif']];

            $custom = new View();
            $custom->getByCommView($community->id,$array['nif']);

            echo '<div class="inner-results">';
            echo '<div class="the-title">';
            echo '<ul class="list-inline up-ul" style="margin:7px 0">';

            $external_url_html = '';
            if($array['snippet']['url']) $external_url_html = '<a href="' . $array['snippet']['url'] . '" target="_blank">' . $array['snippet']['url'] . ' <i class="fa fa-external-link"></i></a>';
            if($custom->id){
                $newVars = $vars;
                $newVars["page"] = 1;
                $newVars['view'] = $array['nif'];
                $newVars['uuid'] = $array['uuid'];
                echo ' <h3 style="display:inline-block; text-transform:none"><a href="'.$search->generateURL($newVars).'">'.strip_tags($array['snippet']['title']) . '</a></h3>';
            } else {
                echo ' <h3 style="display:inline-block; text-transform:none">' . $array['snippet']['title'] . '</h3>';
            }

            $newVars = $vars;
            $newVars["page"] = 1;
            $newVars['nif'] = $array['nif'];
            $splits = explode('|', $array['subcategory']);
            if (count($splits) > 1) {
                $newVars['category'] = $splits[0];
                $newVars['subcategory'] = $splits[1];
            } elseif ($array['subcategory'] != 'CURRENT')
                $newVars['subcategory'] = $array['subcategory'];

            //echo '<li style="margin-left:15px"><i class="icon-sm fa fa-rss"></i></li>';
            echo '<li class="body-hide">';
            echo \helper\htmlElement("collection-bookmark", Array("user" => $_SESSION["user"], "uuid" => $array["uuid"], "community" => $community, "view" => $array["nif"]));
            ?>
            </li>
            <?php echo \helper\htmlElement("archived-source-warning", Array("viewid" => $source->nif)) ?>
            </ul>
            </div>
            <?php echo $external_url_html ?>

            <?php
            echo '<div class="overflow-h">';
            //if (strlen($source->image) > 20)
            //    $imageSrc = $source->image;
            //else $imageSrc = '/upload/source-images/notfound.gif';
            //echo '<img src="' . $imageSrc . '" alt="">';
            echo '<div class="overflow-a">';
            echo '<p>' . \helper\formattedDescription($array['snippet']['description']) . '</p>';
            echo '<ul class="list-inline down-ul">';
            $newVars = $vars;
            $newVars['page'] = 1;
            $splits = explode('|', $array['subcategory']);
            if (count($splits) > 1) {
                $newVars['category'] = $splits[0];
                $newVars['subcategory'] = $splits[1];
            } elseif ($search->category == 'Any')
                $newVars['category'] = $array['subcategory'];
            else
                $newVars['subcategory'] = $array['subcategory'];
            $splits = explode('|', $array['subcategory']);
            if ($array['subcategory'] != 'CURRENT') {
                if ($splits[1] == '')
                    echo '<li><a href="' . $search->generateURL($newVars) . '">' . $splits[0] . '</a></li>';
                else
                    echo '<li><a href="' . $search->generateURL($newVars) . '">' . join(':', $splits) . '</a></li>';
            } else echo '<li>From Current Category</li>';
            echo '</ul>';

            echo '<ul class="list-inline up-ul" style="margin:7px 0">';

            $newVars = $vars;
            $newVars['nif'] = $array['nif'];
            $newVars['page'] = 1;
            $splits = explode('|', $array['subcategory']);
            if($vars["subcategory"]) {
                if (count($splits) > 1) {
                    $newVars['category'] = $splits[0];
                    $newVars['subcategory'] = $splits[1];
                } elseif ($array['subcategory'] != 'CURRENT')
                    $newVars['subcategory'] = $array['subcategory'];
            }
            echo '<li><a href="' . $search->generateURL($newVars) . '"><span class="fa fa-table"></span> ' . $source->getTitle() . ' (' . number_format($result_count) . ')</a>‎</li>';

            if($array['snippet']['citation'] && $array['snippet']['citation']!=''){
                echo '<li>|</li>';
                echo '<li class="body-hide cite-this-div" style="position: relative;padding-left:10px"><a href="javascript:void(0)" class="cite-this-btn"><i class="fa fa-comment"></i> Cite This</a>';
                echo '<div class="hovering-cite-this body-hide no-propagation"><input type="text" class="form-control" style="background:#fff;cursor:text" readonly="readonly" value="'.strip_tags($array['snippet']['citation']).'"/></div>';
            }
            if (isset($_SESSION['user']) && $_SESSION['user']->levels[$community->id] > 1){
                echo '<li>|</li>';
                echo '<li><a class="snippet-edit" href="javascript:void(0)" cid="' . $community->id . '" view="' . $array['nif'] . '"><i class="fa fa-wrench"></i> Edit Source Snippet</a></li>';
            }
            echo '<li>|</li>';
            echo '<li><a href="/' . $community->portalName . '/about/sources/' . $array['nif'] . '" target="_blank"><i class="fa fa-info"></i> &nbsp;&nbsp;View Source Information</a></li>';
            echo '</ul>';


            echo '</div>';
            echo '</div>';
            echo '</div>';
            echo '<hr/>';
        }
        ?>



        <div class="margin-bottom-30"></div>

        <div class="text-left">
            <?php
            //print_r($search);
            echo $search->paginateLong($vars) ?>
        </div>
    </div>
    <!--/col-md-10-->
    </div>
    </div>
    <div class="record-load back-hide"></div>
    <div class="snippet-load back-hide"></div>

    <?php echo \helper\htmlElement("collection-modals", Array("user" => $_SESSION["user"], "community" => $community, "uuids" => $uuids, "views" => $theViews)); ?>

<ol id="joyRideTipContent">
    <li data-class="community-logo" data-text="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2><?php echo $community->name?> Resources</h2>
        <p>
            Welcome to the <?php echo $community->shortName?> Resources search. From here you can search through
            a compilation of resources used by <?php echo $community->shortName?> and see how data is organized within
            our community.
        </p>
    </li>
    <li data-class="resource-tab" data-button="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Navigation</h2>
        <p>
            You are currently on the Community Resources tab looking through categories and sources that <?php echo $community->shortName?>
            has compiled. You can navigate through those categories from here or change to a different tab to execute
            your search through. Each tab gives a different perspective on data.
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
    <li data-class="collection-icon" data-button="Next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Collections</h2>
        <p>
            If you are logged into <?php echo $community->shortName ?> you can add data records to your collections to create custom spreadsheets
            across multiple sources of data.
        </p>
    </li>
    <li data-class="tut-sources" data-button="Next" data-options="tipLocation:right;tipAnimation:fade">
        <h2>Sources</h2>
        <p>
            Here are the sources that were queried against in your search that you can investigate further.
        </p>
    </li>
    <li data-class="tut-categories" data-button="Next" data-options="tipLocation:right;tipAnimation:fade">
        <h2>Categories</h2>
        <p>
            Here are the categories present within <?php echo $community->shortName?> that you can filter your data on
        </p>
    </li>
    <li data-class="tut-subcategories" data-button="Next" data-options="tipLocation:right;tipAnimation:fade">
        <h2>Subcategories</h2>
        <p>
            Here are the subcategories present within this category that you can filter your data on
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
<div class="category-graph very-large-modal back-hide">
    <div class="close dark">X</div>
    <div id="main">
        <div id="sequence"></div>
        <div id="chart">
        </div>
    </div>
    <div id="sidebar">
        <h4>Category Graph</h4>
        <p>
            This is an overview of all the results for your given search. You will see each category, subcategory,
            and source present in this search and you can click on that section to be taken to just that portion.
        </p>
        <p>
            Please note that all sources are present and calculated in the chart, but if the result set has less than
            .001% of the total results returned it may not be visible. We recommend using the filters on the left of your
            results page to navigate to those result sets.
        </p>
    </div>
<!--    <div id="sidebar">-->
<!--        <input type="checkbox" id="togglelegend"> Legend<br/>-->
<!--        <div id="legend" style="visibility: hidden;"></div>-->
<!--    </div>-->
</div>

<script>
    $(".new-interface").click(function(e) {
        deleteCookie("old-interface-resources");
        window.location.reload(0);
    });
</script>
