<?php
    include 'process-elastic-search.php';

    $nifid = $data["nifid"];
    $community = $data["community"];
    $vars = $data["vars"];  // GET request vars
    $search = $data["search"];  // search var
    $user = $data["user"];
    $recent_searches = $data["recent-searches"];

    $show_collection_flag = includeRinIndices($nifid);
    if(!isset($vars["sources"])) $vars["sources"] = $nifid;

    $dknet_flag = false;
    if($community->rinStyle()) {
        $dknet_flag = true;
    }

    if(isset($_GET["newIdx"]) && $_GET["newIdx"] == "on") $_SESSION['new_index'] = "true";
    else if(isset($_GET["newIdx"]) && $_GET["newIdx"] == "off") $_SESSION['new_index'] = "false";

    if(!$nifid || !$community || !$vars || !$search) {
        return;
    }

    require_once __DIR__ . '/../../../classes/schemas/schemas.class.php';

    $src = new Sources();
    $sources = $src->getAllSources();
    if(isset($sources[$vars['nif']])){
        $source = $sources[$vars['nif']];
        $portalName = $community->portalName;
        $dataFeedSchema = SchemaGeneratorSources::generateDataFeed($source, $portalName);
    }

    $categories = $sources[$vars["nif"]]->categories;
    usort($categories, function($a, $b) {
        return strcmp($a["category"], $b["category"]);
    });

    $search_manager = ElasticRRIDManager::esManagerByViewID($nifid);
    if(!$search_manager) {
        return;
    }
    $search_options = ElasticRRIDManager::searchOptionsFromGet($vars);
    ## added quotes to the search keyword -- Vicky-2019-2-5
    $keywords_s = formatKeywords($vars["q"]);
    $results = $search_manager->search($keywords_s, $search->per_page, $vars["page"], $search_options);
    $SourcesInfo = getSourcesInfo($vars);
    $csv_url = "/php/rin-data-csv.php?viewid=" . $nifid . "&q=" . $keywords_s;
    foreach($search_options["filters"] as $so_filter) {
        $csv_url .= "&filter[]=" . $so_filter[0] . ":" . $so_filter[1];
    }
    foreach($search_options["facets"] as $so_facet) {
        $csv_url .= "&facet[]=" . $so_facet[0] . ":" . $so_facet[1];
    }
    if($search_options["sort"]) {
        $csv_url .= "&sort=" . $search_options["sort"]["direction"] . "&column=" . $search_options["sort"]["column"];
    }
?>

<?php if($search->page >= Search::MAX_PAGE) echo \helper\htmlElement("too-many-pages", Array("max_page" => Search::MAX_PAGE)) ?>
<style>

    body {
        float: left;
        min-width: 100%;
    }
    .table-fixed > thead > tr > th, .table-fixed > thead > tr > td {
        min-width: 180px;
        background-color: white;
    }
    .table-fixed td {

        /* These are technically the same, but use both */
        overflow-wrap: break-word;
        word-wrap: break-word;

        word-break: break-word;

        /* Adds a hyphen where the word breaks, if supported (No Blink) */
        -ms-hyphens: auto;
        -moz-hyphens: auto;
        -webkit-hyphens: auto;
        hyphens: auto;
    }
    .grey-option {
        background-color: rgb(149, 165, 166);
    }
</style>


<?php //if (count($results["facets"])): ?>
    <link rel="stylesheet" href="/css/facets-wordcloud.css">
    <!-- Facet word cloud modal -->
    <div id="facets-wordcloud-modal">
        <div class="facets-wordcloud-modal-content">
            <div class="facets-wordcloud-modal-loading">
                <h3>Preparing word cloud <i class="fa fa-cog fa-spin"></i></h2>
                <img src="/images/scicrunch.png" style="height: 50px">
            </div>
            <span class="facets-wordcloud-close">&times;</span>
            <h3 class="facets-wordcloud-modal-title"></h3>
            <div class="facets-wordcloud-area" class="wordcloud-tooltip-available">
            </div>
        </div>
    </div>
    <script type="text/javascript">
        var query_facet_array = <?php echo json_encode($vars['facet']) ?>;
        var query_sources = "<?php echo "&sources=".$vars['sources'] ?>";
        var query_filter_array = <?php echo json_encode($vars['filter']) ?>;
        var query_column = <?php echo json_encode($vars['column']) ?>;
        var query_sort = <?php echo json_encode($vars['sort']) ?>;
        <?php ## added facets data for word cloud -- Vicky-2019-3-25 ?>
        var facets_data = <?php echo json_encode($results->facets()) ?>;
    </script>
    <script type="text/javascript" src="/js/wordcloud2.js"></script>
    <script type="text/javascript" src="/js/facets-wordcloud30.js"></script>
<?php //endif ?>

<?php ob_start(); ?>
<div>
    <div class="col-md-12">
        <p class="truncate-medium" id="sc-descr"><?php echo $sources[$vars['nif']]->description; ?></p>
    </div>
    <div class="col-md-12">
        <?php echo \helper\htmlElement("components/search-block-slim", Array(
            "user" => $user,
            "vars" => $vars,
            "community" => $community,
            "search" => $search,
        )) ?>
    </div>
    <div class="col-md-2" id="left-nav-facets">
        <?php
            if(isset($_GET["changed"]) && (isset($_SESSION["pre_facets"]) || isset($_SESSION["pre_filters"]))) echo \helper\htmlElement("view-previous-facets-filters");
        ?>
        <?php echo \helper\htmlElement("modified-date-picker30"); ?>
        <!-- <?php if(strpos(http_build_query($_GET), "v_status:") === false): ?> -->
            <br/><?php echo \helper\htmlElement("new-records-link", Array("vars" => $vars, "search" => $search)); ?>
        <!-- <?php endif ?> -->
        <hr/>
        <?php if($show_collection_flag): ?>
            <h3>Options</h3>
            <ul class="list-group">
                <?php if(isset($user)): ?>
                    <li class="list-group-item"><a href="javascript:void(0)" class="simple-toggle" modal=".new-collection">Create New Collection</a></li>
                <?php else: ?>
                    <li class="list-group-item"><a href="#" class="btn-login">Log in for Collection Options</a></li>
                <?php endif ?>
            </ul>
            <hr/>
        <?php endif ?>
        <?php
            if(count($SourcesInfo) > 0) {
                echo \helper\htmlElement("view-data-sources", Array("vars" => $vars, "search" => $search, "data-sources" => $SourcesInfo, "nifid" => $nifid, "community" => $community));
            }
            echo $search->currentFacets($vars, 'table');
            echo \helper\htmlElement("view-facets-rrid", Array("results" => $results, "search" => $search, "vars" => $vars));
        ?>
        <?php echo \helper\htmlElement("recent-searches-list", Array("recent-searches" => $recent_searches, "community" => $community)); ?>
    </div>
    <div class="col-md-10">
        <div class="row">
            <div class="col-md-12">
                <a href="javascript:void(0)" class="switch-to-snippet"><button class="btn btn-default"><i class="fa fa-list"></i></button> Snippet</a>
                <button class="btn btn-default active"><i class="fa fa-table"></i></button> Table
                <a href="javascript:void(0)" class="switch-to-discovery"><button class="btn btn-default"><i class="fa fa-th"></i></button> Discovery</a>
                <?php if ($_SESSION['user']->role == 2):  ## added debug button (show or hide elastic query)  -- Vicky-2019-2-22 ?>
                    <br>&nbsp;<br>
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
            </div>
        </div>
        <?php if($results->totalCount() > 0): ?>
            <div class="row">
                <div class="col-md-12">
                    <?php echo \helper\htmlElement("rin/search-cant-find-rrid", Array("community" => $community, "nifid" => $nifid, "top" => true)) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-9">
                    <?php if($show_collection_flag): ?>
                        <div class="panel panel-default">
                            <?php if (in_array($vars["nif"], ["SCR_013869-1", "nif-0000-07730-1"])): ?>
                                <div class="panel-heading" style="text-align: center; font-size: 14px">Click the <i class="fa fa-square-o" aria-hidden="true"></i> to add this resource to an Authentication Report or Collection</div>
                            <?php else: ?>
                                <div class="panel-heading" style="text-align: center; font-size: 14px">Click the <i class="fa fa-square-o" aria-hidden="true"></i> to add this resource to a Collection</div>
                            <?php endif ?>
                        </div>
                  <?php endif ?>
                </div>
            </div>
        <?php endif ?>
        <?php if($results->totalCount() == 0): ?>
            <h3>No results found.</h3>
        <?php else: ?>
            <div class="panel panel-grey margin-bottom-50">
                <div class="panel-heading">
                    <h3 class="panel-title pull-left">
                        <i class="fa fa-globe"></i> <?php echo $search->getResultText('table', array($results->totalCount(), $GLOBALS["notif_id"], $subscription_data["modified_time"]), NULL, $vars) ?> -
                        <select class="grey-option per-page-select">
                            <option class="grey-option" value="20" <?php if($search->per_page === 20) echo "selected" ?>>20</option>
                            <option class="grey-option" value="50" <?php if($search->per_page === 50) echo "selected" ?>>50</option>
                            <option class="grey-option" value="100" <?php if($search->per_page === 100) echo "selected" ?>>100</option>
                        </select>
                        per page
                    </h3>
                    <div class="pull-right">
                        <h3 class="panel-title">
                            <?php
                            $newVars = $vars;
                            $newVars['fullscreen'] = 'true';
                            $newVars["page"] = 1;
                            ?>
                            <a href="javascript:void(0)" class="not-rin showMoreColumns" id="smc"><i class="fa fa-plus"></i> Show More Columns</a> |
                            <!--<a class="not-rin" href="<?php //echo $search->generateURL($newVars) ?>" target="_blank"><i class="fa fa-arrows-alt"></i> Fullscreen</a> |-->
                            <a class="not-rin ga-download" href=<?php echo "'".$csv_url."'" ?>><i class="fa fa-cloud-download"></i> Download 1000 results</a>
                        </h3>
                    </div>
                    <div class="clearfix"></div>
                </div>

                <!-- <div id="table-container"> -->
                <div class="panel-body">
                    <table class="table table-bordered table-striped table-fixed" style="table-layout:fixed" id="result-table">
                        <thead>
                            <tr>
                                <?php
                                    $count = 0;
                                ?>
                                <?php foreach ($search_manager->fields() as $column): ?>
                                    <?php
                                        if(!$column->visible("table")) {
                                            continue;
                                        }
                                        if ($count > 6) {
                                            $thprops = 'style="position:relative" class="search-header hidden-column showing"';
                                        } else {
                                            $thprops = 'style="position:relative" class="search-header"';
                                        }
                                    ?>
                                    <th <?php echo $thprops ?>>
                                        <?php echo $column->name ?>
                                        <div class="column-search invis-hide">
                                            <form method="get" class="column-search-form" column="<?php echo $column->name ?>">
                                                <div class="input-group">
                                                    <input type="text" class="form-control" name="value" placeholder="Search Column" value="" autocomplete="off">
                                                    <span class="input-group-btn">
                                                        <button class="btn-u search-filter-btn" type="button"><i class="fa fa-search"></i></button>
                                                    </span>
                                                </div>
                                            </form>
                                            <hr style="margin:0px"/>
                                            <?php
                                                $newVars = $vars;
                                                $newVars["column"] = $column->name;
                                                $newVars["sort"] = "asc";
                                            ?>
                                            <?php if($column->visible("sort")): ?>
                                                <p><a class="sortin-column" href="<?php echo $search->generateURL($newVars) ?>"><i class="fa fa-sort-amount-asc"></i> Sort Ascending</a></p>
                                                <?php
                                                    $newVars["sort"] = "desc";
                                                ?>
                                                <p><a class="sortin-column" href="<?php echo $search->generateURL($newVars) ?>"><i class="fa fa-sort-amount-desc"></i> Sort Descending</a></p>
                                            <?php endif ?>
                                        </div>
                                        <?php
                                            ## added facet word cloud -- Vicky-2019-3-7
                                            if (!isset($results->facets()[$column->name]) || (count($results->facets()[$column->name]) == 1 && $results->facets()[$column->name][0]["value"] == "")) pass;
                                            else {
                                                echo '<a class="show-facets-wordcloud" onclick="showWordCloud('
                                                    . "'" . $vars['nif'] . "'" . ','
                                                    . "'" . htmlentities($vars['q']) . "'" . ','
                                                    . "'" . $column->name . "'" .
                                                    ')"><i class="fa fa-cloud wordcloud-button-grow"></i>';
                                            }
                                        ?>
                                    </th>
                                    <?php
                                        $count++;
                                    ?>
                                <?php endforeach ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                                $colcount = 0;
                            ?>
                            <?php foreach($results as $i => $result): ?>
                                <?php
                                    $idx = $result->getRRIDField("id"); ## got unique id information -- Vicky-2018-12-20
                                    $type = strtolower(explode(",", $result->getRRIDField("type"))[0]);
                                    $souceInfo = getSourceInformation($type);
                                    $count = 0;
                                    $collection_bookmark = NULL;
                                    $itemID = $result->getRRIDField("curie");
                                    if($itemID == "") $itemID = str_replace('/', '$U+002F;', $result->getRRIDField("item-curie"));

                                    if($dknet_flag) {
                                        $newVars = $vars;
                                        $newVars["page"] = 1;
                                        unset($newVars["nif"]);
                                        $newVars['view'] = $souceInfo["sourceID"];
                                        $newVars['category'] = $souceInfo["category"];
                                        $newVars['rrid'] = $itemID;
                                        $resource_report_url = $search->generateURL($newVars)."&i=".$idx;
                                    } else {
                                        $resource_report_url = $community->fullURL()."/resolver/".$itemID."?i=".$idx;
                                    }

                                    if($result->getRRIDField("uuid")) {
                                        $uuid = $result->getRRIDField("uuid");
                                        ## fixed adding resource item to authentication report -- Vicky-2019-3-6-2019
                                        $rrid_type = $result->getRRIDField("type");
                                        if($result->getRRIDField("type") == "Cell Line") $rrid_type = "cellline";
                                        $rrid_data = Array(
                                              "rrid" => $itemID,
                                              "type" => $rrid_type,
                                              "name" => $result->getRRIDField("name"),
                                              "subtypes" => "",
                                              "uid" => $idx,
                                            );
                                        $collection_bookmark = \helper\htmlElement("collection-bookmark", Array("user" => $user, "uuid" => $uuid, "community" => $community, "view" => $vars["nif"], "rrid-data" => $rrid_data));
                                    } else {
                                        $uuid = NULL;
                                        $collection_bookmark = "";
                                    }

                                    $source_config = getSourceConfigFile($result->getRRIDField("source-indices"));
                                    if($source_config != "") {
                                        $fields_url = getFieldsURL($source_config);
                                    }
                                ?>
                                <tr>
                                    <?php foreach ($search_manager->fields() as $field_name): ?>
                                        <?php
                                            if(!$field_name->visible("table")) {
                                                continue;
                                            }
                                            $fmt_value = $result->getField($field_name->name);
                                            if ($source->description_encoded) {
                                                $fmt_value = \helper\formattedDescription($fmt_value);
                                            }
                                            $fmt_value = preg_replace("/http-equiv=['\"]refresh['\"]/", "", $fmt_value);
                                            // $fmt_value = preg_replace("/RRID:([\.\+\w:-]+)/", "<a href=\"" . $community->fullURL() . "/data/record/" . $vars["nif"] . "/$1/resolver\">RRID:$1</a>", $fmt_value); // convert RRIDs to links
                                            $fmt_value = $field_name->name == "Reference" || $field_name->name == "Mentioned In Literature" || $field_name->name == "Reference/Provider" ? \helper\checkLongURL($fmt_value, $community, $vars["nif"], $uuid, $column->name) : $fmt_value;

                                            if ($fmt_value != "") {
                                                switch ($field_name->name) {
                                                    case "References":     ## added references link -- Vicky-2019-1-15
                                                    case "RRIDs used":
                                                        $fmt_value = join("<br>", buildLinks($fmt_value, $community));
                                                        break;
                                                    case "Target Antigen":
                                                        if (trim($fmt_value) == ",") $fmt_value = "";
                                                        break;
                                                    case "Hierarchy":   ## modified "Hierarchy" & "Originate from Same Individual" -- Vicky-2019-1-31
                                                    case "Originate from Same Individual":   ## modified "Hierarchy" & "Originate from Same Individual" -- Vicky-2019-1-31
                                                        if($fmt_value != "CVCL:") $fmt_value = str_replace(":", "_", $fmt_value);
                                                        else $fmt_value = "";
                                                        break;
                                                    case "Cross References":
                                                        $fmt_value = "<a target='_blank' href='https://www.ncbi.nlm.nih.gov/bioproject/".$fmt_value."'>".$fmt_value."</a>";
                                                        break;
                                                    case "Comments":
                                                        $fmt_value = str_replace(['<font color="#ff6347"></> ', '<font color="#000000"></> '], "", $fmt_value);
                                                        if (strpos(strtolower($result->getRRIDField("issues")), "problematic") !== false) {
                                                            $fmt_value = "<font color='red'>".$fmt_value."</font>";
                                                        }
                                                        break;
                                                }
                                            }

                                            if($count == 0 && isRIN($type)) { // first column should be link to resource, only for rin sources
                                                $fmt_value = '<a href="'.$resource_report_url.'">'.$fmt_value.'</a>';
                                            } else {
                                                if($fields_url[$field_name->name] != "") {
                                                    $field_url = explode("$$", $fields_url[$field_name->name]);
                                                    $field_url_param = $field_url[1];
                                                    $field_url[1] = $result->getField($field_url_param);

                                                    switch ($field_url_param) {
                                                        case "ID":
                                                            if(\helper\startsWith($field_url[1], "pdb:")) $field_url[1] = substr_replace($field_url[1], "", 0, strlen("pdb:"));
                                                            else if(\helper\startsWith($field_url[1], "pmid:")) $field_url[1] = substr_replace($field_url[1], "", 0, strlen("pmid:"));
                                                            break;

                                                    }
                                                    $field_url = join("", $field_url);
                                                    $fmt_value = '<a href="'.$field_url.'">'.$fmt_value.'</a>';
                                                }
                                            }
                                        ?>
                                        <?php if ($count > 6): ?>
                                            <td class="hidden-column showing"><span class="search-table-record-td"><?php echo $fmt_value ?></span></td>
                                        <?php else: ?>
                                            <?php
                                                if($count == 0 && !is_null($collection_bookmark)) {
                                                    $tdprops = 'class="bookmark-td"';
                                                } else {
                                                    $tdprops = "";
                                                    $collection_bookmark = "";
                                                }
                                            ?>
                                            <td <?php echo $tdprops ?>>
                                                <?php echo $collection_bookmark ?>
                                                <span class="search-table-record-td">
                                                  <?php echo $fmt_value ?>
                                                  <?php if($count == 0):  ## added report and external link icons -- Vicky-2018-11-30 ?>
                                                      <br>&nbsp;<br>
                                                      <?php if(isRIN($type)): ?>
                                                          <a href="<?php echo $resource_report_url ?> "><i class='fa fa-file-o'></i></a> Resource Report<br>
                                                      <?php endif ?>
                                                      <?php if($result->getRRIDField("url") != ""): ?>
                                                          <a target='_blank' href="<?php echo $result->getRRIDField("url") ?>"><i class='fa fa-external-link'></i></a> Resource Website
                                                      <?php endif ?>
                                                      <?php if($result->getRRIDField("mentionCount") > 0): ## added mentions count -- Vicky-2019-9-15?>
                                                          <?php
                                                              $mention_count = $result->getRRIDField("mentionCount");
                                                              $mentionCount = "1+";
                                                              if ($mention_count >= 10) $mentionCount = "10+";
                                                              if ($mention_count >= 50) $mentionCount = "50+";
                                                              if ($mention_count >= 100) $mentionCount = "100+";
                                                              if ($mention_count >= 500) $mentionCount = "500+";
                                                              if ($mention_count >= 1000) $mentionCount = "1000+";
                                                              if ($mention_count >= 5000) $mentionCount = "5000+";
                                                              if ($mention_count >= 10000) $mentionCount = "10000+";
                                                          ?>
                                                          <br><i class='fa fa-line-chart'></i> <?php echo $mentionCount ?> mentions
                                                      <?php endif ?>

                                                      <?php ## added warning and rating icons -- Vicky-2019-2-25 ?>
                                                      <?php if(!empty($result->getSpecialField("alerts"))): ?>
                                                        <?php foreach($result->getSpecialField("alerts") as $alert): ?>
                                                          <br><?php echo $alert["icon"] ?> <?php echo $alert["text"] ?>
                                                        <?php endforeach ?>
                                                      <?php endif ?>
                                                      <?php if(!empty($result->getSpecialField("ratings"))): ?>
                                                          <br><i class='fa fa-star text-success'></i> Rating or validation data
                                                      <?php endif ?>
                                                  <?php endif ?>
                                                </span>
                                            </td>
                                        <?php endif ?>
                                        <?php
                                            $count++;
                                            $colcount = $count;
                                        ?>
                                    <?php endforeach ?>
                                </tr>
                                <?php
                                    //changes the body width when displaying more columns
                                    $body_width = ' ';
                                    if($colcount > 6) {
                                        $body_width = '150%';
                                    }
                                    if($colcount > 8) {
                                        $body_width = '200%';
                                    }
                                ?>
                                <script>
                                    if(<?php echo $colcount ?> > 6) {
                                        $(".showMoreColumns").click(function(){
                                            if($(this).hasClass("active")){
                                                $("body").css("width", "100%");
                                                $("#left-nav-facets").removeAttr("style");
                                            } else {
                                                $("body").css("width", "<?php echo $body_width ?>");
                                                $("#left-nav-facets").css("width", "220px");
                                            }
                                        });
                                    }
                                </script>
                            <?php endforeach ?>
                        </tbody>
                    </table>
                <!-- </div> -->

                </div>
            </div>
            <?php echo $search->paginateLong($vars, "not-rin", $results->totalCount(), $search->per_page) ?>
        <?php endif ?>
        <hr/>
        <?php echo \helper\htmlElement("rin/search-cant-find-rrid", Array("community" => $community, "nifid" => $nifid)) ?>
        <?php if($results->totalCount() > 0): ?>
            <!--<p>(last updated: <?php echo date("M j, Y", $sources[$vars['nif']]->data_last_updated) ?>)</p>-->
        <?php endif ?>
    </div>
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

<?php echo \helper\htmlElement("collection-modals", Array("user" => $user, "community" => $community)); ?>
<div class="category-graph the-largest-modal back-hide">
    <div class="close dark">X</div>
    <div id="main">
        <div id="sequence"></div>
        <div id="chart">
            <!-- <div id="explanation" style="visibility: hidden;">
                <span id="percentage"></span><br/>
                of results have this facet
            </div> -->
        </div>
    </div>
    <div id="sidebar">


        <h4>Facet Graph</h4>

        <p>
            This is an overview of all the faceted data within your result set. You can click on the lowest level to
            apply the facet to your search.
        </p>

        <p>
            Please note that all facets are present and calculated in the chart, but if the result set has less than
            .001% of the total results returned it may not be visible.
        </p>
        <div id="legend"></div>
    </div>
    <!--    <div id="sidebar">-->
    <!--        <input type="checkbox" id="togglelegend"> Legend<br/>-->
    <!--        <div id="legend" style="visibility: hidden;"></div>-->
    <!--    </div>-->
</div>
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
        <h2>Collections</h2>
        <p>
            If you are logged into <?php echo $community->shortName ?> you can add data records to your collections to create custom spreadsheets
            across multiple sources of data.
        </p>
    </li>
    <li data-class="multi-facets" data-button="Next" data-options="tipLocation:right;tipAnimation:fade">
        <h2>Facets</h2>
        <p>
            Here are the facets that you can filter the data by.
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
<?php $url = $search->generateURLFromDiff(Array("page" => 1, "on_page" => NULL)); ?>


<!-- Go to www.addthis.com/dashboard to customize your tools -->
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-577ff645c9be4c0f"></script>


<script>

$(function () {
    $("#result-table").stickyTableHeaders();
});

/*! Copyright (c) 2011 by Jonas Mosbech - https://github.com/jmosbech/StickyTableHeaders
    MIT license info: https://github.com/jmosbech/StickyTableHeaders/blob/master/license.txt */

;
(function ($, window, undefined) {
    'use strict';

    var name = 'stickyTableHeaders',
        id = 0,
        defaults = {
            fixedOffset: 0,
            leftOffset: 0,
            marginTop: 0,
            scrollableArea: window
        };

    function Plugin(el, options) {
        // To avoid scope issues, use 'base' instead of 'this'
        // to reference this class from internal events and functions.
        var base = this;

        // Access to jQuery and DOM versions of element
        base.$el = $(el);
        base.el = el;
        base.id = id++;
        base.$window = $(window);
        base.$document = $(document);

        // Listen for destroyed, call teardown
        base.$el.bind('destroyed',
        $.proxy(base.teardown, base));

        // Cache DOM refs for performance reasons
        base.$clonedHeader = null;
        base.$originalHeader = null;

        // Keep track of state
        base.isSticky = false;
        base.hasBeenSticky = false;
        base.leftOffset = null;
        base.topOffset = null;

        base.init = function () {
            base.$el.each(function () {
                var $this = $(this);


                $this.css('padding', 0);

                base.$originalHeader = $('thead:first', this);
                base.$clonedHeader = base.$originalHeader.clone();
                $this.trigger('clonedHeader.' + name, [base.$clonedHeader]);

                base.$clonedHeader.addClass('tableFloatingHeader');
                base.$clonedHeader.css('display', 'none');

                base.$originalHeader.addClass('tableFloatingHeaderOriginal');

                base.$originalHeader.after(base.$clonedHeader);

                base.$printStyle = $('<style type="text/css" media="print">' +
                    '.tableFloatingHeader{display:none !important;}' +
                    '.tableFloatingHeaderOriginal{position:static !important;}' +
                    '</style>');
                $('head').append(base.$printStyle);
            });

            base.setOptions(options);
            base.updateWidth();
            base.toggleHeaders();
            base.bind();
        };

        base.destroy = function () {
            base.$el.unbind('destroyed', base.teardown);
            base.teardown();
        };

        base.teardown = function () {
            if (base.isSticky) {
                base.$originalHeader.css('position', 'static');
            }
            $.removeData(base.el, 'plugin_' + name);
            base.unbind();

            base.$clonedHeader.remove();
            base.$originalHeader.removeClass('tableFloatingHeaderOriginal');
            base.$originalHeader.css('visibility', 'visible');
            base.$printStyle.remove();

            base.el = null;
            base.$el = null;
        };

        base.bind = function () {
            base.$scrollableArea.on('scroll.' + name, base.toggleHeaders);
            if (!base.isWindowScrolling) {
                base.$window.on('scroll.' + name + base.id, base.setPositionValues);
                base.$window.on('resize.' + name + base.id, base.toggleHeaders);
            }
            base.$scrollableArea.on('resize.' + name, base.toggleHeaders);
            base.$scrollableArea.on('resize.' + name, base.updateWidth);
        };

        base.unbind = function () {
            // unbind window events by specifying handle so we don't remove too much
            base.$scrollableArea.off('.' + name, base.toggleHeaders);
            if (!base.isWindowScrolling) {
                base.$window.off('.' + name + base.id, base.setPositionValues);
                base.$window.off('.' + name + base.id, base.toggleHeaders);
            }
            base.$scrollableArea.off('.' + name, base.updateWidth);
        };

        base.toggleHeaders = function () {
            if (base.$el) {
                base.$el.each(function () {
                    var $this = $(this),
                        newLeft,
                        newTopOffset = base.isWindowScrolling ? (
                        isNaN(base.options.fixedOffset) ? base.options.fixedOffset.outerHeight() : base.options.fixedOffset) : base.$scrollableArea.offset().top + (!isNaN(base.options.fixedOffset) ? base.options.fixedOffset : 0),
                        offset = $this.offset(),

                        scrollTop = base.$scrollableArea.scrollTop() + newTopOffset,
                        scrollLeft = base.$scrollableArea.scrollLeft(),

                        scrolledPastTop = base.isWindowScrolling ? scrollTop > offset.top : newTopOffset > offset.top,
                        notScrolledPastBottom = (base.isWindowScrolling ? scrollTop : 0) < (offset.top + $this.height() - base.$clonedHeader.height() - (base.isWindowScrolling ? 0 : newTopOffset));

                    if (scrolledPastTop && notScrolledPastBottom) {
                        newLeft = offset.left - scrollLeft + base.options.leftOffset;
                        base.$originalHeader.css({
                            'position': 'fixed',
                                'margin-top': base.options.marginTop,
                                'left': newLeft,
                                'z-index': 3,
                                'background-color' : 'white',
                                'border-bottom': 'solid 1px #cccccc'
                        });
                        base.leftOffset = newLeft;
                        base.topOffset = newTopOffset;
                        base.$clonedHeader.css('display', '');
                        if (!base.isSticky) {
                            base.isSticky = true;
                            // make sure the width is correct: the user might have resized the browser while in static mode
                            base.updateWidth();
                        }
                        base.setPositionValues();
                    } else if (base.isSticky) {
                        base.$originalHeader.css('position', 'static');
                        base.$clonedHeader.css('display', 'none');
                        base.isSticky = false;
                        base.resetWidth($('td,th', base.$clonedHeader), $('td,th', base.$originalHeader));
                    }
                });
            }
        };

        base.setPositionValues = function () {
            var winScrollTop = base.$window.scrollTop(),
                winScrollLeft = base.$window.scrollLeft();
            if (!base.isSticky || winScrollTop < 0 || winScrollTop + base.$window.height() > base.$document.height() || winScrollLeft < 0 || winScrollLeft + base.$window.width() > base.$document.width()) {
                return;
            }
            base.$originalHeader.css({
                'top': base.topOffset - (base.isWindowScrolling ? 0 : winScrollTop),
                'left': base.leftOffset - (base.isWindowScrolling ? 0 : winScrollLeft)
            });
        };

        base.updateWidth = function () {
            if (!base.isSticky) {
                return;
            }
            // Copy cell widths from clone
            if (!base.$originalHeaderCells) {
                base.$originalHeaderCells = $('th,td', base.$originalHeader);
            }
            if (!base.$clonedHeaderCells) {
                base.$clonedHeaderCells = $('th,td', base.$clonedHeader);
            }
            var cellWidths = base.getWidth(base.$clonedHeaderCells);
            base.setWidth(cellWidths, base.$clonedHeaderCells, base.$originalHeaderCells);

            // Copy row width from whole table
            base.$originalHeader.css('width', base.$clonedHeader.width());
        };

        base.getWidth = function ($clonedHeaders) {
            var widths = [];
            $clonedHeaders.each(function (index) {
                var width, $this = $(this);

                if ($this.css('box-sizing') === 'border-box') {
                    width = $this[0].getBoundingClientRect().width; // #39: border-box bug
                } else {
                    var $origTh = $('th', base.$originalHeader);
                    if ($origTh.css('border-collapse') === 'collapse') {
                        if (window.getComputedStyle) {
                            width = parseFloat(window.getComputedStyle(this, null).width);
                        } else {
                            // ie8 only
                            var leftPadding = parseFloat($this.css('padding-left'));
                            var rightPadding = parseFloat($this.css('padding-right'));
                            // Needs more investigation - this is assuming constant border around this cell and it's neighbours.
                            var border = parseFloat($this.css('border-width'));
                            width = $this.outerWidth() - leftPadding - rightPadding - border;
                        }
                    } else {
                        width = $this.width();
                    }
                }

                widths[index] = width;
            });
            return widths;
        };

        base.setWidth = function (widths, $clonedHeaders, $origHeaders) {
            $clonedHeaders.each(function (index) {
                var width = widths[index];
                $origHeaders.eq(index).css({
                    'min-width': width,
                        'max-width': width
                });
            });
        };

        base.resetWidth = function ($clonedHeaders, $origHeaders) {
            $clonedHeaders.each(function (index) {
                var $this = $(this);
                $origHeaders.eq(index).css({
                    'min-width': $this.css('min-width'),
                        'max-width': $this.css('max-width')
                });
            });
        };

        base.setOptions = function (options) {
            base.options = $.extend({}, defaults, options);
            base.$scrollableArea = $(base.options.scrollableArea);
            base.isWindowScrolling = base.$scrollableArea[0] === window;
        };

        base.updateOptions = function (options) {
            base.setOptions(options);
            // scrollableArea might have changed
            base.unbind();
            base.bind();
            base.updateWidth();
            base.toggleHeaders();
        };

        // Run initializer
        base.init();
    }

    // A plugin wrapper around the constructor,
    // preventing against multiple instantiations
    $.fn[name] = function (options) {
        return this.each(function () {
            var instance = $.data(this, 'plugin_' + name);
            if (instance) {
                if (typeof options === 'string') {
                    instance[options].apply(instance);
                } else {
                    instance.updateOptions(options);
                }
            } else if (options !== 'destroy') {
                $.data(this, 'plugin_' + name, new Plugin(this, options));
            }
        });
    };

})(jQuery, window);


$(function() {
    createCookie("search-table-view", "");
    deleteCookie("search-discovery-view");

    $(".per-page-select").change(function() {
        var current = <?php echo $search->per_page; ?>;
        var per_page = $(".per-page-select option:selected").val();
        if(current === per_page) return;
        location = "<?php echo $url ?>&per_page=" + per_page;
    });

    $(".switch-to-snippet").on("click", function() {
        deleteCookie("search-table-view");
        deleteCookie("search-discovery-view");
        // window.location.reload(false);
         window.location =  window.location.href.replace("&table", "");
    });

    $(".switch-to-discovery").on("click", function() {
        createCookie("search-discovery-view", "");
        deleteCookie("search-table-view");
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
