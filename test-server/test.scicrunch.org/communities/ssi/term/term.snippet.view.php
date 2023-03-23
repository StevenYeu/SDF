<?php
    $community->type = "interlex";

    if($vars["l"]) {
        $input_val = $vars["l"];
    } elseif($vars["q"]) {
        $input_val = $vars["q"];
    } else {
        $input_val = "";
    }

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

    $per_page = 20;
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
    $count = $search_results->totalCount();

    $term_pre_facets = Array();
    if(!isset($_GET["changed"])) {
        if(isset($vars["facet"])) $_SESSION["term_pre_facets"] = $vars["facet"];
        else unset($_SESSION["term_pre_facets"]);
    }

?>

<style>
    .flag {
      padding: 0px 4px;
      border-radius: 20px;
      font-size: 14px;
      color: white;
      align-content: center;
    }
</style>

<?php
    if($community->shortName != 'scicrunch' && $community->portalName != 'scicrunch') $home = $community->shortName.' Home';
    else $home = 'Home';

    echo Connection::createBreadCrumbs('Term Search',array($home, 'Term Dashboard'),array('/'.$community->portalName, '/'.$community->portalName . '/interlex/dashboard'),'Term Search');
?>

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
                    <?php echo \helper\htmlElement("view-types-interlex", Array("vars" => $vars, "types" => $_GET["types"], "results_types" => $results_types, "community" => $community)); ?>
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
                <div class="row">
                    <div class="col-md-7" style="font-size: 20px; color: #666766">
                        <?php if($count > $per_page): ?>
                            On page <?php echo $vars["page"] ?> showing <?php echo ($per_page * ($vars["page"] - 1) + 1) . " ~ " . ($per_page * ($vars["page"] - 1) + $search_results->hitCount()) ?> term(s) out of <?php echo number_format($count) ?> term(s)
                        <?php elseif($count > 0 && $count <= $per_page): ?>
                            On page <?php echo $vars["page"] ?> showing <?php echo "1 ~ " . $search_results->hitCount() ?> term(s) out of <?php echo number_format($count) ?> term(s)
                        <?php endif ?>
                    </div>
                    <div class="col-md-5">
                        <?php echo $search->paginateLong($vars, "not-rin", $count, $per_page) ?>
                    </div>
                </div>
                <br>
                <div class="col-md-4">
                    <?php
                        $newVars = $vars;
                        $newVars["title"] = "table";
                    ?>
                    <button class="btn btn-default active"><i class="fa fa-list"></i></button> Snippet view
                    <a href="<?php echo $search->generateURL($newVars) ?>" class="switch-to-table"><button class="btn btn-default"><i class="fa fa-table"></i></button> Table view</a>
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
                <div class="col-md-12" style="font-size: 20px; color: #666766">
                    <?php if($count == 0): ?>
                        No results found.
                    <?php endif ?>
                </div>
            </div>
            <?php foreach($search_results as $record): ?>
                <div class="inner-results">
                    <div class="the-title">
                        <ul class="list-inline up-ul" style="margin:7px 0">
                            <h4>
                                <a target="_self" href="<?php echo $community->fullURL() ?>/interlex/view/<?php echo $record->getField("ID") ?>?searchTerm=<?php echo $vars['q'] ?>"><?php echo $record->getField("Name") ?></a>
                                <?php
                                    switch ($record->getField("Type")) {
                                        case "cde":
                                            $background = "green";
                                            $type_name = "CDE";
                                            break;
                                        case "TermSet":
                                            $background = "orange";
                                            $type_name = "TermSet";
                                            break;
                                        case "pde":
                                            $background = "blue";
                                            $type_name = "PDE";
                                            break;
                                        case "fde":
                                            $background = "purple";
                                            $type_name = "FDE";
                                            break;
                                        case "annotation":
                                            $background = "red";
                                            $type_name = "Annotation";
                                            break;
                                        case "relationship":
                                            $background = "grey";
                                            $type_name = "Relationship";
                                            break;
                                        default:
                                            $background = "";
                                            $type_name = "";
                                            break;
                                    }
                                ?>
                                <span class="flag" style="background-color: <?php echo $background ?>"><?php echo $type_name." " ?><i class="fa fa-info-circle"></i></span></h4>
                            <p><b>Description:</b> <?php echo $record->getField("Description") ?></p>
                            <p><b>Preferred ID:</b> <?php echo $record->getField("Preferred ID") ?>&nbsp;&nbsp;&nbsp;&nbsp;
                            <b>Type:</b> <?php echo $record->getField("Type") ?>
                            <?php
                                $newVars = $vars;
                                if(!in_array("Type:" . $record->getField("Type"), $newVars["facet"])) $newVars["facet"][] = "Type:" . $record->getField("Type");
                            ?>
                            <a href="<?php echo $search->generateURL($newVars) ?>"><i class="fa fa-search"></i></a>&nbsp;&nbsp;&nbsp;&nbsp;
                            <b>ID# </b> <?php echo str_replace("_", ":", strtoupper($record->getField("ID"))) ?>&nbsp;&nbsp;&nbsp;&nbsp;
                            <b>Score: </b> <?php echo $record->getField("Score") ?></p>
                    </div>
                </div>
                <hr/>
            <?php endforeach ?>
            <?php echo $search->paginateLong($vars, "not-rin", $search_results->totalCount(), $search->per_page) ?>
        </div>
    </div>
</div>

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
