<?php
    $community->type = "interlex";

    if($vars["l"]) {
        $input_val = $vars["l"];
    } elseif($vars["q"]) {
        $input_val = $vars["q"];
    } else {
        $input_val = "";
    }

    $per_page = 20;
    if(isset($_GET["per_page"]) && $_GET["per_page"] != "") $per_page = $_GET["per_page"];

    $newVars = $vars;
    if(!isset($newVars["facet"])) {
        $newVars["facet"] = Array();
    } else {
        $types_facet_flag = false;
        foreach ($newVars["facet"] as $facet) {
            if (strpos($facet, 'Type:') !== false) $types_facet_flag = true;
        }
    }

    if(isset($_GET["types"]) && $_GET["types"] != "") {
        if($types_facet_flag == false) {
            foreach (explode(",", $_GET["types"]) as $type) {
                $newVars["facet"][] = "Type:" . $type;
            }
        }
        $vars["results-types"] = $_GET["types"];
    } else {
        if($types_facet_flag == false) $newVars["facet"][] = "Type:term";
        $vars["results-types"] = "term";
    }

    $search_manager = ElasticInterLexManager::managerByViewID("interlex");
    $search_options = ElasticInterLexManager::searchOptionsFromGet($newVars);
    $keywords_s = formatKeywords($input_val);

    $search_type_results = $search_manager->search($keywords_s, $per_page, 1, Array());
    $results_types = array_column($search_type_results->facets()["Type"], "value");
    $types_order = ["term", "relationship", "annotation", "cde", "fde", "pde", "termset"];
    $tmp = Array();
    foreach ($types_order as $value) {
        if(in_array($value, $results_types)) $tmp[] = $value;
    }
    $results_types = $tmp;

    $search_results = $search_manager->search($keywords_s, $per_page, $vars["page"], $search_options);

    $results_count = $search_results->totalCount();

    $term_pre_facets = Array();
    if(!isset($_GET["changed"])) {
        if(isset($vars["facet"])) $_SESSION["term_pre_facets"] = $vars["facet"];
        else unset($_SESSION["term_pre_facets"]);
    }

    $csv_url = "/php/rin-data-csv.php?viewid=interlex" . "&q=" . $keywords_s;
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

<?php
    if($community->shortName != 'scicrunch' && $community->portalName != 'scicrunch') $home = $community->shortName.' Home';
    else $home = 'Home';

    echo Connection::createBreadCrumbs('Term Search',array($home, 'Term Dashboard'),array('/'.$community->portalName, '/'.$community->portalName . '/interlex/dashboard'),'Term Search');
?>

<style>
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

<?php echo \helper\htmlElement("components/search-block-slim", Array(
    "user" => $_SESSION["user"],
    "vars" => $vars,
    "community" => $community,
    "search" => $search,
)) ?>

<div class='container'>
    <div class="row">
        <div class="col-md-2 hidden-xs related-search">
            <div class="row" style="margin-top:10px">
                <div class="col-md-12 col-sm-4">
                    <?php
                        if(isset($_GET["changed"]) && isset($_SESSION["term_pre_facets"])) echo \helper\htmlElement("view-term-previous-facets");
                    ?>
                    <?php echo \helper\htmlElement("view-types-interlex", Array("vars" => $vars, "types" => $_GET["types"], "results_types" => $results_types, "community" => $community, "view_type" => "table")); ?>
                    <?php echo $search->currentFacets($vars, 'table') ?>
                    <?php echo \helper\htmlElement("view-facets-interlex", Array("results" => $search_results, "search" => $search, "vars" => $vars)); ?>
                    <a class="btn btn-primary" href="<?php echo $community->fullURL() ?>/interlex/create?label=<?php echo $_GET['q'] ?>"><i class="fa fa-plus" aria-hidden="true"></i> Add new term</a>
                    <?php echo \helper\htmlElement("recent-searches-list", Array("recent-searches" => $_SESSION["recent-searches"], "community" => $community)) ?>
                    <hr/>
                </div>
            </div>
        </div>
        <div class="col-md-10">
            <div class="row">
                <div class="col-md-7">
                    <?php
                        $newVars = $vars;
                        $newVars["title"] = "search";
                    ?>
                    <a href="<?php echo $search->generateURL($newVars) ?>" class="switch-to-snippet"><button class="btn btn-default"><i class="fa fa-list"></i></button> Snippet view</a>
                    <button class="btn btn-default active"><i class="fa fa-table"></i></button> Table view
                </div>
                <div class="col-md-5">
                    <?php echo $search->paginateLong($vars, "not-rin", $results_count, $per_page) ?>
                </div>
            </div>
            <br>
            <?php if ($_SESSION['user']->role == 2): ?>
                <div class="row">
                    <div class="col-md-12">
                        <button class="btn btn-primary" onclick="return showHideElasticQuery()">Elastic Search Query</button>
                    </div>
                    <div class="col-md-12">
                        <div id="ElasticQuery" style="display:none">
                            <?php echo $_SESSION['elastic_interlex_query'] ?>
                        </div>
                    </div>
                </div>
            <?php endif ?>
            <div class="row">
                <div class="col-md-12">
                    <a class="btn btn-primary" href="<?php echo $community->fullURL() ?>/interlex/create?label=<?php echo $_GET['q'] ?>" style="white-space: normal; font-size: 14px; width: 100%">Can’t find your term? Help us by adding it to InterLex - it’s easy. Click this button to be taken to the term addition page (account is required).</a>
                </div>
            </div>
            <br>
            <div class="row">
                <div class="col-md-12">
                    <?php if($search_results->totalCount() > 0): ?>
                        <div class="panel panel-grey margin-bottom-50">
                            <div class="panel-heading">
                                <h3 class="panel-title pull-left">
                                    <i class="fa fa-globe"></i> <?php echo $search->getResultText('table', array($search_results->totalCount(), $GLOBALS["notif_id"], $subscription_data["modified_time"]), NULL, $vars) ?> -
                                    <select class="grey-option per-page-select">
                                        <option class="grey-option" value="20" <?php if($per_page == "20") echo "selected" ?>>20</option>
                                        <option class="grey-option" value="50" <?php if($per_page == "50") echo "selected" ?>>50</option>
                                        <option class="grey-option" value="100" <?php if($per_page == "100") echo "selected" ?>>100</option>
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
                                        <a class="not-rin ga-download" href=<?php echo "'".$csv_url."'" ?>><i class="fa fa-cloud-download"></i> Download 1000 results</a>
                                    </h3>
                                </div>
                                <div class="clearfix"></div>
                            </div>

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
                                                    if ($count > 5) {
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
                                                        ## facet word cloud
                                                        if (!isset($search_results->facets()[$column->name]) || (count($search_results->facets()[$column->name]) == 1 && $search_results->facets()[$column->name][0]["value"] == "")) pass;
                                                        else {
                                                            echo '<a class="show-facets-wordcloud" onclick="showWordCloud('
                                                                . "'" . $vars['type'] . "'" . ','
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
                                        <?php foreach($search_results as $i => $result): ?>
                                            <?php
                                                $count = 0;
                                            ?>
                                            <tr>
                                                <?php foreach ($search_manager->fields() as $field_name): ?>
                                                    <?php
                                                        if(!$field_name->visible("table")) {
                                                            continue;
                                                        }
                                                        if($field_name->name == "ID") {
                                                            $linearray = explode('_', $result->getField($field_name->name));
                                                            $fmt_value = strtoupper($linearray[0]) . ':' . $linearray[1];
                                                        } else $fmt_value = $result->getField($field_name->name);
                                                        if(count($fmt_value) > 1) $fmt_value = join(", ", $fmt_value);
                                                        else if(count($fmt_value) == 0) $fmt_value = "";
                                                    ?>
                                                    <?php if ($count > 5): ?>
                                                        <td class="hidden-column showing"><span class="search-table-record-td"><?php echo $fmt_value ?></span></td>
                                                    <?php else: ?>
                                                        <td>
                                                            <?php if ($count == 0): ?>
                                                                <a target="_self" href="<?php echo $community->fullURL() ?>/interlex/view/<?php echo $result->getField("ID") ?>?searchTerm=<?php echo $vars['q'] ?>"><?php echo $fmt_value ?></a>
                                                            <?php else: ?>
                                                                <span class="search-table-record-td"><?php echo $fmt_value ?></span>
                                                            <?php endif ?>
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
                            </div>
                        </div>
                        <?php echo $search->paginateLong($vars, "not-rin", $search_results->totalCount(), $search->per_page) ?>
                    <?php else: ?>
                        <span style="font-size: 20px; color: #666766">No results found.</span>
                    <?php endif?>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="/js/wordcloud2.js"></script>
<script type="text/javascript" src="/js/facets-wordcloud30.js"></script>

<script type="text/javascript">
    var query_facet_array = <?php echo json_encode($vars['facet']) ?>;
    var query_filter_array = <?php echo json_encode($vars['filter']) ?>;
    var query_column = <?php echo json_encode($vars['column']) ?>;
    var query_sort = <?php echo json_encode($vars['sort']) ?>;
    var query_types = "&types=<?php echo $vars['results-types'] ?>";
    var facets_data = <?php echo json_encode($search_results->facets()) ?>;
</script>

<link rel="stylesheet" href="/css/facets-wordcloud.css">
<!-- Facet world cloud modal -->
<div id="facets-wordcloud-modal">
    <div class="facets-wordcloud-modal-content">
        <div class="facets-wordcloud-modal-loading">
            <h3>Preparing word cloud <i class="fa fa-cog fa-spin"></i></h3>
            <img src="/images/scicrunch.png" style="height: 50px">
        </div>
        <span class="facets-wordcloud-close">&times;</span>
        <h3 class="facets-wordcloud-modal-title"></h3>
        <div class="facets-wordcloud-area" class="wordcloud-tooltip-available">
        </div>
    </div>
</div>

<script>

$(function() {
    $(".per-page-select").change(function() {
        var current = <?php echo $search->per_page; ?>;
        var per_page = $(".per-page-select option:selected").val();
        if(current === per_page) return;
        <?php
            $newVars = $vars;
            unset($newVars["per_page"]);
        ?>
        location = "<?php echo $search->generateURL($newVars) ?>&per_page=" + per_page;
    });

    $(".switch-to-snippet").on("click", function() {
        deleteCookie("search-table-view");
        window.location.reload(false);
    });

    $('.search-table-record-td').truncate({max_length: 200});
});
</script>

<script>

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
