<?php

include 'process-elastic-search.php';

$nifid = $data["nifid"];
$community = $data["community"];
$vars = $data["vars"];
$search = $data["search"];
$user = $data["user"];
$recent_searches = $data["recent-searches"];
$elastic_view = $data["elastic-view"];

$protocol_flag = false;
if($nifid == "protocol") $protocol_flag = true;

if(isset($_GET["newIdx"]) && $_GET["newIdx"] == "on") $_SESSION['new_index'] = "true";
else if(isset($_GET["newIdx"]) && $_GET["newIdx"] == "off") $_SESSION['new_index'] = "false";

$per_page = 20;
$search_manager = ElasticRRIDManager::managerByViewID($nifid);
$search_options = ElasticRRIDManager::searchOptionsFromGet($vars);

## added quotes to the search keyword -- Vicky-2019-2-5
$keywords_s = formatKeywords($vars["q"]);
$search_results = $search_manager->search($keywords_s, $per_page, $vars["page"], $search_options);
//$search_results = $search_manager->search($vars["q"], $per_page, $vars["page"], $search_options);
$count = $search_results->totalCount();

$src = new Sources();
$sources = $src->getAllSources();

?>

<script type="text/javascript" src="/js/facets-wordcloud30.js"></script>
<script>
    document.categoryGraphData = <?php echo json_encode($category_graph_tree) ?>;
</script>
<?php if($search->page >= Search::MAX_PAGE) echo \helper\htmlElement("too-many-pages", Array("max_page" => Search::MAX_PAGE)) ?>

<?php ob_start(); ?>
<div>
    <div class="row">
        <div class="col-md-12">
            <p style="margin-bottom: 0px" class="truncate-medium" id="sc-descr"><?php echo $sources[$vars['nif']]->description; ?></p>
        </div>
        <div class="col-md-12 results-number" style="margin-top:10px;">
            <?php echo \helper\htmlElement("components/search-block-slim", Array(
                "user" => $user,
                "vars" => $vars,
                "community" => $community,
                "search" => $search,
            )) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-2 hidden-xs related-search">
            <div class="row" style="margin-top:10px">
                <div class="col-md-12 col-sm-4">
                    <?php echo \helper\htmlElement("modified-date-picker30"); ## added last update -- Vicky-2019-4-18 ?>
                    <br/><?php echo \helper\htmlElement("new-records-link-for-resource-report", Array("vars" => $vars, "search" => $search)); ?>
                    <hr />
                    <h3>Options</h3>
                    <ul class="list-group">
                        <?php if(isset($user)): ?>
                            <li class="list-group-item"><a href="javascript:void(0)" class="simple-toggle" modal=".new-collection">Create New Collection</a></li>
                            <!--<li class="list-group-item"><a href="javascript:void(0)" class="simple-toggle" modal=".add-all">Add All on Page to a Collection</a></li>-->
                        <?php else: ?>
                            <li class="list-group-item"><a href="#" class="btn-login">Log in for Collection Options</a></li>
                        <?php endif ?>
                    </ul>
                    <hr class="hr-small" />
                    <?php echo $search->currentFacets($vars, 'table') ?>
                    <?php echo \helper\htmlElement("view-facets-rrid", Array("results" => $search_results, "search" => $search, "vars" => $vars)); ?>
                    <!-- <?php if(!empty($search_results->facets())): ?>
                        <hr class="hr-small" />
                    <?php endif ?> -->
                    <?php echo \helper\htmlElement("recent-searches-list", Array("recent-searches" => $recent_searches, "community" => $community)) ?>
                    <hr/>
                </div>
            </div>
        </div>
        <!--/col-md-2-->

        <div class="col-md-10">
            <div class="row">
                <div class="col-md-3">
                  <?php if($count > $per_page): ?>
                        On page <?php echo $vars["page"] ?> showing <?php echo ($per_page * ($vars["page"] - 1) + 1) . " ~ " . ($per_page * ($vars["page"] - 1) + $search_results->hitCount()) ?> out of <?php echo number_format($count) ?> results
                    <?php elseif($count > 0 && $count <= $per_page): ?>
                        On page <?php echo $vars["page"] ?> showing <?php echo "1 ~ " . $search_results->hitCount() ?> out of <?php echo number_format($count) ?> results
                    <?php else: ?>
                    No results found.
                    <?php endif ?>
                </div>
                <div class="col-md-4">
                    <button class="btn btn-default active"><i class="fa fa-list"></i></button> Snippet view
                    <a href="javascript:void(0)" class="switch-to-table"><button class="btn btn-default"><i class="fa fa-table"></i></button> Table view</a>
                </div>
                <div class="col-md-5">
                    <?php echo $search->paginateLong($vars, "not-rin", $count, $per_page) ?>
                </div>
            </div>
            <?php if ($_SESSION['user']->role == 2):  ## added debug button (show or hide elastic query)  -- Vicky-2019-2-22 ?>
                <div class="row">
                    <div class="col-md-12">
                        <button class="btn btn-primary" onclick="return showHideElasticQuery()">Elastic Search Query</button>&nbsp;&nbsp;
                        <input id="checkBox" type="checkbox"> New Index
                    </div>
                    <div class="col-md-12">
                        <div id="ElasticQuery" style="display:none">
                            <?php echo $_SESSION['elastic_query'] ?>
                        </div>
                    </div>
                </div>
            <?php endif ?>
            <?php if($count > 0): ?>
                <div class="row">
                    <div class="col-md-12">
                        <?php echo \helper\htmlElement("rin/search-cant-find-rrid", Array("community" => $community, "nifid" => $nifid, "top" => true)) ?>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-9">
                        <div class="panel panel-default">
                            <?php if (in_array($vars["nif"], ["SCR_013869-1", "nif-0000-07730-1"])): ?>
                                <div class="panel-heading" style="text-align: center; font-size: 14px">Click the <i class="fa fa-square-o" aria-hidden="true"></i> to add this resource to an Authentication Report or Collection</div>
                            <?php else: ?>
                                <div class="panel-heading" style="text-align: center; font-size: 14px">Click the <i class="fa fa-square-o" aria-hidden="true"></i> to add this resource to a Collection</div>
                            <?php endif ?>
                        </div>
                    </div>
                </div>
            <?php endif ?>
            <?php foreach($search_results as $record): ?>
                <div class="inner-results">
                    <div class="the-title">
                        <ul class="list-inline up-ul" style="margin:7px 0">
                            <?php
                                $newVars = $vars;
                                $newVars["page"] = 1;
                                unset($newVars["nif"]);
                                $newVars['view'] = $nifid;
                                $newVars['rrid'] = $record->getRRIDField("curie");
                                if($protocol_flag) $newVars['rrid'] = str_replace('/', '$U+002F;', $newVars['rrid']);
                                $idx = $record->getRRIDField("id"); ## got unique id information -- Vicky-2018-12-20
                                $resource_report_url = $search->generateURL($newVars);
                                //$item_mentions = \search\searchSingleItemMentions($newVars['view'], $newVars['rrid'], 0, 1);
                                ## fixed adding resource item to authentication report -- Vicky-2019-3-6-2019
                                $rrid_type = $record->getRRIDField("type");
                                if($record->getRRIDField("type") == "Cell Line") $rrid_type = "cellline";
                                $rrid_data = Array(
                                      "rrid" => str_replace("RRID:", "", $record->getRRIDField("curie")),
                                      "type" => $rrid_type,
                                      "name" => $record->getRRIDField("name"),
                                      "subtypes" => "",
                                      "uid" => $idx,
                                    );
                            ?>
                            <li class="body-hide">
                                <?php echo \helper\htmlElement("collection-bookmark", Array("user" => $user, "uuid" => $record->getRRIDField("uuid"), "community" => $community, "view" => $nifid, "rrid-data" => $rrid_data)); ?>
                            </li>
                            <h3 style="display:inline-block; text-transform:none">
                                <div><a href="<?php echo $resource_report_url."&i=".$idx ?>"><?php echo $record->getRRIDField("name") ?> <i class="fa fa-file-o"></i></a></div>
                            </h3>
                            <li class="body-hide">
                                <?php echo \helper\htmlElement("authentication-bookmark", Array("user" => $user, "uuid" => $record->getRRIDField("uuid"), "community" => $community, "view" => $nifid, "rrid-data" => $rrid_data)); ?>
                            </li>
                            <h4>
                                <?php if($protocol_flag): ?>
                                    DOI: <?php echo $record->getField("DOI") ?>
                                <?php else: ?>
                                    <?php echo $record->getRRIDField("curie") ?>
                                <?php endif ?>
                            </h4>
                            <?php if ($record->getRRIDField("mentionCount") > 0): ## added mentions count -- Vicky-2019-4-15 ?>
                                <span class="fa-stack fa-md">
                                    <i class="fa fa-circle fa-stack-2x" style="color:#666767"></i>
                                    <i class="fa fa-line-chart fa-stack-1x fa-inverse"></i>
                                </span>
                                <?php
                                    $mention_count = $record->getRRIDField("mentionCount");
                                    $mentionCount = "1+";
                                    if ($mention_count >= 10) $mentionCount = "10+";
                                    if ($mention_count >= 50) $mentionCount = "50+";
                                    if ($mention_count >= 100) $mentionCount = "100+";
                                    if ($mention_count >= 500) $mentionCount = "500+";
                                    if ($mention_count >= 1000) $mentionCount = "1000+";
                                    if ($mention_count >= 5000) $mentionCount = "5000+";
                                    if ($mention_count >= 10000) $mentionCount = "10000+";
                                ?>
                                This resource has <?php echo $mentionCount ?> mentions.
                                <span class="help-tooltip" data-name="mention-definition.html"></span>
                                <!-- This resource was mentioned <?php echo $record->getRRIDField("mentionCount") ?>
                                <?php if ($record->getRRIDField("mentionCount") == 1): ?>
                                    time.
                                <?php else: ?>
                                    times
                                <?php endif ?> -->
                            <?php endif ?>
                        </ul>
                    </div>
                    <?php if(!empty($record->getSpecialField("alerts"))): ?>
                        <p>
                            <?php foreach($record->getSpecialField("alerts") as $alert): ?>
                                <?php if($alert["type"] == "warning"): ?>
                                    <?php echo $alert["icon"] ?>
                                <?php endif ?>
                                <?php echo $alert["text"] ?>
                            <?php endforeach ?>
                        </p>
                    <?php endif ?>
                    <?php if(!empty($record->getSpecialField("ratings"))): ?>
                        <p>
                            <i class="fa fa-star text-success"></i> Ratings or validation data are available for this resource
                        </p>
                    <?php endif ?>
                    <!-- <?php if ($item_mentions["hits"]["total"] > 0): ?>
                        <p>
                            <i class="fa fa-star text-success"></i> mentions are available for this resource
                        </p>
                    <?php endif ?> -->
                    <?php if(!$protocol_flag): ?>
                        <p><a target="_blank" href="<?php echo $record->getRRIDField("url") ?>"><?php echo $record->getRRIDField("url") ?></a></p>
                    <?php endif ?>
                    <p><?php echo $record->snippet() ?></p>
                    <p><?php echo $record->getRRIDField("snippet") ?></p>
                    <p><strong>Proper citation:</strong> <?php echo $record->getField("Proper Citation") ?></p>

                    <ul class="list-inline up-ul">
                        <li>
                            <?php if($search_manager->getName() == "Protocol"): ?>
                                <a href="https://www.protocols.io/" target="_blank">
                                    <i class="fa fa-info"></i> &nbsp;&nbsp;Source: <?php echo "Protocols.io" ?>
                                </a>
                            <?php else: ?>
                            <a href="<?php echo $community->fullURL() ?>/about/sources/<?php echo $nifid ?>" target="_blank">
                                <?php
                                switch($search_manager->getName()) {
                                    case "Antibody":
                                        $source = "Antibody Registry";
                                        break;
                                    case "Tool":
                                        $source = "SciCrunch Registry";
                                        break;
                                    case "Cell Line":
                                        $source = "Cellosaurus";
                                        break;
                                    case "Organism":
                                        $source = "Integrated Animals";
                                        break;
                                    case "Plasmid":
                                        $source = "Addgene";
                                        break;
                                    case "Biosample":
                                        $source = "NCBI Biosample";
                                        break;
                                    // case "Protocol":
                                    //     $source = "Protocols.io";
                                    //     break;
                                }
                                ?>
                                <i class="fa fa-info"></i> &nbsp;&nbsp;Source: <?php echo $source ?>
                            </a>
                            <?php endif ?>
                        </li>
                    </ul>
                </div>
                <hr/>
            <?php endforeach ?>



            <div class="margin-bottom-30"></div>

            <div class="text-left">
                <?php echo $search->paginateLong($vars, "not-rin", $count, $per_page) ?>
            </div>

            <hr/>
            <?php echo \helper\htmlElement("rin/search-cant-find-rrid", Array("community" => $community, "nifid" => $nifid)) ?>
        </div>
        <!--/col-md-10-->
    </div>
    <!--<p>(last updated: <?php echo date("M j, Y", $sources[$vars['nif']]->data_last_updated) ?>)</p>-->
</div>
<?php $rin_html = ob_get_clean(); ?>

<?php

$rin_data = Array(
    "title" => $search_manager->getName(true),
    "breadcrumbs" => Array(
        Array("text" => "Home", "url" => $community->fullURL()),
        Array("text" => "Resource Reports", "url" => $community->fullURL() . "/rin/rrids"),
        Array("text" => $search_manager->getName(true), "active" => true),
    ),
    "html-body" => $rin_html,
);
echo \helper\htmlElement("rin-style-page", $rin_data);

?>

<div class="record-load back-hide"></div>
<div class="snippet-load back-hide"></div>

<?php echo \helper\htmlElement("collection-modals", Array("user" => $user, "community" => $community, "uuids" => $uuids, "views" => $theViews)); ?>

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
    $(function() {
        $(".new-interface").click(function(e) {
            deleteCookie("old-interface-resources");
            window.location.reload(0);
        });


        $(".switch-to-table").on("click", function() {
            createCookie("search-table-view", "");
            window.location.reload(false);
        });
    });

</script>

<script>
// added debug button (show elastic query)  -- Vicky-2019-2-22
function showHideElasticQuery() {
        var ele = document.getElementById("ElasticQuery");
        if(ele.style.display == "block") {
                ele.style.display = "none";
          }
        else {
            ele.style.display = "block";
        }
    }
</script>

<script>

$(function(){
    // added new index checkBox
    var checked_flag = false;
    var newIdx = "<?php echo $_SESSION['new_index'] ?>";
    if(newIdx == "true") checked_flag = true;
    $('#checkBox').prop('checked', checked_flag || false);
});

$('#checkBox').on('change', function() {
    localStorage.checked = $(this).is(':checked');
    if(localStorage.checked == "true") window.location = "<?php echo $search->generateURL($vars) ?>&newIdx=on";
    else window.location = "<?php echo $search->generateURL($vars) ?>&newIdx=off";
});
</script>
