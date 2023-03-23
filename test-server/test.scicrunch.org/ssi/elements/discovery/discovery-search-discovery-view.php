<?php

    include 'process-elastic-search.php';

    $nifid = $data["nifid"];
    $community = $data["community"];
    $vars = $data["vars"];
    $search = $data["search"];
    $user = $data["user"];
    $recent_searches = $data["recent-searches"];
    $elastic_view = $data["elastic-view"];
    if(!isset($vars["sources"])) $vars["sources"] = $nifid;

    $show_collection_flag = includeRinIndices($nifid);

    if(isset($_GET["newIdx"]) && $_GET["newIdx"] == "on") $_SESSION['new_index'] = "true";
    else if(isset($_GET["newIdx"]) && $_GET["newIdx"] == "off") $_SESSION['new_index'] = "false";

    $dknet_flag = false;
    if($community->rinStyle()) {
        $dknet_flag = true;
    }

    $per_page = 20;
    $page = 1;
    $search_manager = ElasticRRIDManager::esManagerByViewID($vars["sources"]);
    $search_options = ElasticRRIDManager::searchOptionsFromGet($vars);

    ## added quotes to the search keyword -- Vicky-2019-2-5
    $keywords_s = formatKeywords($vars["q"]);
    $search_results = $search_manager->search($keywords_s, $per_page, $page, $search_options);
    $source_indices = $search_results->facets()["Data Sources"];

    $data_sources_list = file_get_contents("ssi/elements/discovery/json/discovery_data_sources.json");
    $data_sources = json_decode($data_sources_list, true);

    $indices = Array();

    foreach ($data_sources as $index => $val) {
        $indices[$index] = Array(
                              "plural_name" => $val["plural_name"],
                              "name" => $val["name"],
                              "index" => $val["index"],
                              "es_type" => $val["es_type"]
                          );
    }

    foreach ($source_indices as $idx => $source_index) {
        foreach ($indices as $key => $value) {
            if(\helper\startsWith($source_index["value"], $value["index"])) {
                $source_indices[$idx]["plural_name"] = $value["plural_name"];
                $source_indices[$idx]["name"] = $value["name"];
                $source_indices[$idx]["index"] = $key;
                $source_indices[$idx]["es_type"] = $value["es_type"];
            }
        }
    }

    ## split sources indices to selected and not_selected groups
    $selected_sources = Array();
    $not_selected_sources = Array();
    $SourcesInfo = getSourcesInfo($vars);

    $ids = explode(",", $nifid);
    $sources_ids = array_column($SourcesInfo, "index");
    $results_ids = array_column($source_indices, "index");

    // check results indices
    if($vars["sources"] == $nifid) $selected_sources = $source_indices;
    else {
        foreach ($source_indices as $source) {
            if(in_array($source["index"], $ids)) $selected_sources[] = $source;
            else $not_selected_sources[] = $source;
        }
    }

    // check sources indices
    foreach ($SourcesInfo as $source) {
        if(!in_array($source["index"], $results_ids)) {
            $source["count"] = 0;
            if(in_array($source["index"], $ids) || $vars["sources"] == $nifid) $selected_sources[] = $source;
            else $not_selected_sources[] = $source;
        }
    }

    $count = 0;
    foreach ($selected_sources as $source) {
        $count += $source["count"];
    }

    $selected_sources_layout = getDiscoveryViewLayout($selected_sources);
    $not_selected_sources_layout = getDiscoveryViewLayout($not_selected_sources);

    $src = new Sources();
    $sources = $src->getAllSources();

    function getDiscoveryViewLayout($source_indices) {
        $indices_count = count($source_indices);
        if($indices_count > 6) {
            $n = 3;
            $col = "col-md-4";
            $font_size = 16;
        } else if($indices_count <= 6 && $indices_count > 3) {
            $n = 2;
            $col = "col-md-6";
            $font_size = 18;
        } else {
            $n = 1;
            $col = "col-md-10";
            $font_size = 20;
        }

        $layout = Array(
            "n" => $n,
            "col" => $col,
            "font_size" => $font_size,
        );

        return $layout;
    }

?>

<link rel="stylesheet" href="/css/resources.view.css" />
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
                    <?php
                        if(isset($_GET["changed"]) && (isset($_SESSION["pre_facets"]) || isset($_SESSION["pre_filters"]))) echo \helper\htmlElement("view-previous-facets-filters");
                    ?>
                    <?php echo \helper\htmlElement("modified-date-picker30"); ## added last update -- Vicky-2019-4-18 ?>
                    <br/><?php echo \helper\htmlElement("new-records-link", Array("vars" => $vars, "search" => $search)); ?>
                    <hr />
                    <?php if($show_collection_flag): ?>
                        <h3>Options</h3>
                        <ul class="list-group">
                            <?php if(isset($user)): ?>
                                <li class="list-group-item"><a href="javascript:void(0)" class="simple-toggle" modal=".new-collection">Create New Collection</a></li>
                            <?php else: ?>
                                <li class="list-group-item"><a href="#" class="btn-login">Log in for Collection Options</a></li>
                            <?php endif ?>
                        </ul>
                        <hr class="hr-small" />
                    <?php endif ?>
                    <?php
                        if(count($SourcesInfo) > 0) {
                            echo \helper\htmlElement("view-data-sources", Array("vars" => $vars, "search" => $search, "data-sources" => $SourcesInfo, "nifid" => $nifid, "community" => $community));
                        }
                        // echo $search->currentFacets($vars, 'table');
                        // echo \helper\htmlElement("view-facets-rrid", Array("results" => $search_results, "search" => $search, "vars" => $vars));
                        echo "<h3>Facets Disabled in Discovery View</h3><hr />";
                    ?>
                    <?php echo \helper\htmlElement("recent-searches-list", Array("recent-searches" => $recent_searches, "community" => $community)) ?>
                    <hr/>
                </div>
            </div>
        </div>
        <!--/col-md-2-->

        <div class="col-md-10">
            <div class="row">
                <div class="col-md-12">
                    <b style="font-size:16px">RESULTS</b>
                    <a href="javascript:void(0)" class="switch-to-snippet"><button class="btn btn-default"><i class="fa fa-list"></i></button> Snippet
                    <a href="javascript:void(0)" class="switch-to-table"><button class="btn btn-default"><i class="fa fa-table"></i></button> Table</a>
                    <button class="btn btn-default active"><i class="fa fa-th"></i></button> Discovery
                </div>
                <div class="col-md-12">
                    <?php if($count > 0): ?>
                        <p>Total <?php echo number_format($count) ?> results</p>
                    <?php else: ?>
                        <p>No results found.</p>
                    <?php endif ?>
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
                <br>
            <?php endif ?>
            <?php if($count > 0): ?>
                <div class="row">
                    <div class="col-md-12">
                        <?php echo \helper\htmlElement("rin/search-cant-find-rrid", Array("community" => $community, "nifid" => $nifid, "top" => true)) ?>
                    </div>
                </div>
            <?php endif ?>

            <div class="row">
                <div class="col-md-12">
                <?php foreach($selected_sources as $i => $index): ?>
                    <?php
                        $souceInfo = getSourceInformation(strtolower($index["name"]));
                    ?>
                    <?php if($i % $selected_sources_layout['n'] == 0): ?>
                        <div class="row">
                    <?php endif ?>
                            <div class="single-view <?php echo $selected_sources_layout['col'] ?>">
                                <div class="row">
                                    <div class="col-md-8">
                                        <span style="font-size:<?php echo $selected_sources_layout['font_size'] ?>px">
                                            <a target="_blank" href="<?php echo $community->fullURL() ?>/discovery/source/<?php echo $index["index"] ?>/search?<?php echo explode("?", $search->generateURL($vars))[1] ?>&table"><?php echo $index["plural_name"] ?></a>
                                        </span>
                                    </div>
                                    <div class="col-md-4">
                                        <span style="font-size:<?php echo $selected_sources_layout['font_size'] ?>px"><?php echo number_format($index["count"]) ?></span>
                                    </div>
                                </div>
                                <?php if($souceInfo["sourceID"] != ""): ?>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <i style="font-size:<?php echo $selected_sources_layout['font_size']-4 ?>px">
                                                &nbsp;&nbsp;--
                                                <?php if($index["es_type"] == "rin"): ?>
                                                    <a target="_blank" href="<?php echo $community->fullURL() ?>/about/sources/<?php echo $souceInfo["sourceID"] ?>">Source: <?php echo $souceInfo["source"] ?></a>;
                                                    <?php if($selected_sources_layout['n'] > 2): ?>
                                                        <br>&nbsp;&nbsp;&nbsp;&nbsp;
                                                    <?php endif ?>
                                                <?php endif ?>
                                                RRID: <?php echo $souceInfo["RRID"] ?>
                                                <?php if($dknet_flag): ?>
                                                    <a target="_blank" href="<?php echo $community->fullURL() ?>/data/record/nlx_144509-1/<?php echo $souceInfo["RRID"] ?>/resolver"><i class='fa fa-file-o'></i></a>
                                                <?php else: ?>
                                                    <a target="_blank" href="<?php echo $community->fullURL() ?>/resolver/<?php echo $souceInfo["RRID"] ?>"><i class='fa fa-file-o'></i></a>
                                                <?php endif ?>
                                            </i>
                                        </div>
                                    </div>
                                <?php endif ?>
                            </div>
                    <?php if($i == count($selected_sources) - 1): ?>
                        </div>
                    <?php elseif($i % $selected_sources_layout['n'] == $selected_sources_layout['n']-1): ?>
                        </div>
                        <br>
                    <?php endif ?>
                <?php endforeach ?>
                </div>
            </div>
            <hr />

            <?php if(count($not_selected_sources) > 0): ?>
                <div class="row">
                    <div class="col-md-12">
                      <h2>Sources Not Currently Selected</h2>
                      <?php foreach($not_selected_sources as $i => $index): ?>
                          <?php
                              $souceInfo = getSourceInformation(strtolower($index["name"]));
                          ?>
                          <?php if($i % $not_selected_sources_layout['n'] == 0): ?>
                              <div class="row">
                          <?php endif ?>
                                  <div class="single-view <?php echo $not_selected_sources_layout['col'] ?>">
                                      <div class="row">
                                          <div class="col-md-8">
                                              <span style="font-size:<?php echo $not_selected_sources_layout['font_size'] ?>px">
                                                  <a target="_blank" href="<?php echo $community->fullURL() ?>/discovery/source/<?php echo $index["index"] ?>/search?<?php echo explode("?", $search->generateURL($vars))[1] ?>&table"><?php echo $index["plural_name"] ?></a>
                                              </span>
                                          </div>
                                          <div class="col-md-4">
                                              <span style="font-size:<?php echo $not_selected_sources_layout['font_size'] ?>px"><?php echo number_format($index["count"]) ?></span>
                                          </div>
                                      </div>
                                      <?php if($souceInfo["sourceID"] != ""): ?>
                                          <div class="row">
                                              <div class="col-md-12">
                                                  <i style="font-size:<?php echo $not_selected_sources_layout['font_size']-4 ?>px">
                                                      &nbsp;&nbsp;--
                                                      <?php if($index["es_type"] == "rin"): ?>
                                                          <a target="_blank" href="<?php echo $community->fullURL() ?>/about/sources/<?php echo $souceInfo["sourceID"] ?>">Source: <?php echo $souceInfo["source"] ?></a>;
                                                          <?php if($not_selected_sources_layout['n'] > 2): ?>
                                                              <br>&nbsp;&nbsp;&nbsp;&nbsp;
                                                          <?php endif ?>
                                                      <?php endif ?>
                                                      RRID: <?php echo $souceInfo["RRID"] ?>
                                                      <?php if($dknet_flag): ?>
                                                          <a target="_blank" href="<?php echo $community->fullURL() ?>/data/record/nlx_144509-1/<?php echo $souceInfo["RRID"] ?>/resolver"><i class='fa fa-file-o'></i></a>
                                                      <?php else: ?>
                                                          <a target="_blank" href="<?php echo $community->fullURL() ?>/resolver/<?php echo $souceInfo["RRID"] ?>"><i class='fa fa-file-o'></i></a>
                                                      <?php endif ?>
                                                  </i>
                                              </div>
                                          </div>
                                      <?php endif ?>
                                  </div>
                          <?php if($i == count($not_selected_sources) - 1): ?>
                              </div>
                          <?php elseif($i % $not_selected_sources_layout['n'] == $not_selected_sources_layout['n']-1): ?>
                              </div>
                              <br>
                          <?php endif ?>
                      <?php endforeach ?>
                    </div>
                </div>
                <hr />
            <?php endif ?>

            <div class="margin-bottom-30"></div>

            <?php echo \helper\htmlElement("rin/search-cant-find-rrid", Array("community" => $community, "nifid" => $nifid)) ?>
        </div>
        <!--/col-md-10-->
    </div>
    <!--<p>(last updated: <?php echo date("M j, Y", $sources[$vars['nif']]->data_last_updated) ?>)</p>-->
</div>
<?php $discovery_html = ob_get_clean(); ?>

<?php

if (strpos($vars["nif"], ',') !== false)
    $discovery_data = Array(
        "title" => "Multiple Sources" . " " . \helper\htmlElement("archived-source-warning", Array("viewid" => $vars["nif"])),
        "breadcrumbs" => Array(
            Array("text" => "Home", "url" => $community->fullURL()),
            Array("text" => "Discovery Sources", "url" => $community->fullURL() . "/discovery/source/all/search"),
            Array("text" => "Multiple Sources", "active" => true),
        ),
        "html-body" => $discovery_html,
    );
else if ($vars["nif"] == "all")
    $discovery_data = Array(
        "title" => $search_manager->getName(true) . " " . \helper\htmlElement("archived-source-warning", Array("viewid" => $vars["nif"])),
        "breadcrumbs" => Array(
            Array("text" => "Home", "url" => $community->fullURL()),
            Array("text" => "Discovery Sources", "active" => true),
        ),
        "html-body" => $discovery_html,
    );
else
    $discovery_data = Array(
        "title" => $search_manager->getName(true) . " " . \helper\htmlElement("archived-source-warning", Array("viewid" => $vars["nif"])),
        "breadcrumbs" => Array(
            Array("text" => "Home", "url" => $community->fullURL()),
            Array("text" => "Discovery Sources", "url" => $community->fullURL() . "/discovery/source/all/search"),
            Array("text" => $search_manager->getName(true), "active" => true),
        ),
        "html-body" => $discovery_html,
    );
echo \helper\htmlElement("rin-style-page", $discovery_data);

?>

<script>
    $(function() {
        $(".new-interface").click(function(e) {
            deleteCookie("old-interface-resources");
            window.location.reload(0);
        });


        $(".switch-to-table").on("click", function() {
            createCookie("search-table-view", "");
            // window.location.reload(false);
            window.location =  window.location.href.replace("&table", "");
        });

        $(".switch-to-snippet").on("click", function() {
            deleteCookie("search-table-view");
            deleteCookie("search-discovery-view");
            // window.location.reload(false);
            window.location =  window.location.href.replace("&table", "");
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
