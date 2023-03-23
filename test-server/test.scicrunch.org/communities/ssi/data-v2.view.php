<?php
include 'process-elastic-search.php';

function formatResults($results, $lit_count, $allSources) {
    $sources = $results["sources"];

    /* get the primary category and set the archived flag */
    foreach($sources as $viewid => &$source) {
        $source["categories"] = Array(Array("parent" => $source["parent"], "child" => $source["child"]));
        if(isset($allSources[$viewid])) $source["description"] = $allSources[$viewid]->description;
        unset($source["parent"]);
        unset($source["child"]);
        $source["archived"] = false;
    }

    /* append the other categories to the category array */
    foreach($results["hidden-sources"] as $nifid => $source_array) {
        foreach($source_array as $hidden_source) {
            $sources[$nifid]["categories"][] = Array("parent" => $hidden_source["parent"], "child" => $hidden_source["child"]);
        }
    }
    unset($results["hidden-sources"]);

    /* set the sources that are archived */
    foreach(Search::$archivedViews as $av) {
        if(isset($sources[$av])) {
            $sources[$av]["archived"] = true;
        }
    }

    /* add literature source */
    $sources["literature"] = Array(
        "name" => "Literature",
        "count" => $lit_count,
        "cover" => "100.00%",
        "categories" => Array(
            Array(
                "parent" => "Output Type",
                "child" => "Literature",
            ),
            Array(
                "parent" => "Category",
                "child" => "Literature",
            ),
        ),
        "description" => "PubMed abstracts",
        "archived" => false,
    );

    $results["sources"] = $sources;

    return $results;
}

$subscription_get = is_null($GLOBALS["notif_id"]) ? "" : "&notif=" . $GLOBALS["notif_id"];
if(is_null($GLOBALS["notif_email"])) $subscription_get .= "&notif_email";
$subscription_set = $subscription_get != "" ? "true" : "false";

$community_views_ids = $community->getUsedViewIDs();

/* do a literature search */
// $lit_search = new Search();
// $lit_search->category = "literature";
// $lit_search->query = $search->query;
// $lit_search->page = 1;
// $lit_results = $lit_search->doSearch();


## do a literature elastic search
/******************************************************************************************************/

$per_page = 20;
$search_manager = ElasticPMIDManager::managerByViewID("pubmed");
$search_options = ElasticPMIDManager::searchOptionsFromGet($vars);
$keywords_s = formatKeywords($vars["q"]);

$search_results = $search_manager->searchLiterature($keywords_s, $per_page, $vars["page"], $search_options);
$lit_count = $search_results->totalCount();

/******************************************************************************************************/


$formatted_results = formatResults($results, $lit_count, $allSources);

?>

<style>
    .results-count {
        color: #888;
    }
    .btn-primary .results-count {
        color: #CCC;
    }
</style>

<link rel="stylesheet" href="/css/resources.view.css" />
<input type="hidden" value="<?php echo $subscription_set ?>" id="subscription_set" />
<div id="viewsApp" ng-controller="viewsController as vc">
    <?php if ( (strpos(strtolower($community->shortName), "legacy-niddk") !== false) || (strpos(strtolower($community->shortName), "dknet") !== false) ): ?>
      <p>
          The new <?php echo $community->shortName ?> Discovery Portal connects researchers directly to more than 300 biomedical databases and millions of resources.
          Researchers can also explore community resources that are highly relevant to the disease fields in NIDDK's mission.
          Type in a keyword and start to discover now!
      </p>
    <?php else: ?>
      <p>
          The <?php echo $community->shortName ?> Discovery Portal connects researchers directly to more than 300 biomedical databases.
          Type in a keyword and start to discover now!
      </p>
    <?php endif ?>
    <?php echo \helper\htmlElement("components/search-block-slim", Array(
        "community" => $community,
        "user" => $_SESSION["user"],
        "vars" => $vars,
        "search" => $search,
        "expansion" => $results["expansion"],
    )) ?>
    <?php if($results["count"] > 0): ?>
        <div class="row">
            <div class="col-md-12" ng-show="!vc.category_filters_flag">
                <div class="container well">
                    <p>Sorry, no results were found based on your filter setting: {{ vc.category_filters }}.</p>
                    <p>{{ vc.error }}</p>
                    <p>Other search suggestions:</p>
                    <ul>
                        <li>Check your spelling</li>
                        <li>Try more general words or different words that mean the same thing</li>
                        <li>Unselect the synonyms</li>
                    </ul>
                </div>
                <hr class="hr-small" />
            </div>
            <div class="col-md-12">
                {{ category_name1="Output Type";category1 = vc.categories[category_name1];"" }}
                <?php if ($community->rinStyle()): ?>
                    <p class="search-section-header">Community filter</p>
                    <p>
                        <?php /* this block is on a single line to prevent link underline showing up under whitespace */ ?>
                        <?php ## added question mark behind Community Resources in discovery portal-- Vicky-2018-12-5 ?>
                        <a href="javascript:void(0)" ng-click="vc.selectBoxCategory(category_name1, vc.community_filter_name)"><i ng-show="!category1.children[vc.community_filter_name].selected" class="fa fa-toggle-off" style="font-size: 32px;"></i><i ng-show="category1.children[vc.community_filter_name].selected" class="fa fa-toggle-on" style="font-size: 32px;"></i></a>
                        Click here to include only <?php echo $community->shortName ?> <a target="_self" href="<?php echo $community->fullURL() ?>/about/sources">Community Resources</a> <span class="help-tooltip" data-name="discovery_portal_community_resource.html"></span>
                    </p>
                    <hr class="hr-small" />
                <?php endif ?>
                <p class="search-section-header">Category filters</p>
                <p>
                    Showing search results per category. To reset results toggle highlighted box or everything box to re-sort.
                    <span class="btn" style="background-color: #408DC9; color: white" ng-click="vc.clearCategories()">
                        Reset Categories
                    </span>
                </p>
                <div>
                    <div
                        ng-repeat="sc_name in keys(category1.children) | orderBy:outputTypeOrderBy"
                        class="category-box category-color-{{ $index }}"
                        ng-class="{'category-box-selected': category1.children[sc_name].selected, 'category-box-out': category1.children[sc_name].count == 0}"
                        ng-click="vc.selectBoxCategory(category_name1, sc_name)"
                        uib-popover-template="'output-type-template.html'"
                        popover-trigger="'mouseenter'"
                        ng-hide="sc_name == vc.community_filter_name"
                    >
                        <div ng-show="sc_name == vc.community_filter_name" class="category-star"><i class="fa fa-star"></i></div>
                        <div>{{ sc_name }}</div><div class="results-count">({{ category1.children[sc_name].count | number }})</div>
                    </div>
                    <div
                        class="category-box category-color-all"
                        ng-class="{'category-box-selected':!vc.isCategoriesSelected}"
                        ng-click="vc.clearCategories()"
                    >
                        <div>Show me everything</div>
                        <div class="results-count">(<?php echo number_format($results["count"]) ?>)</div>
                    </div>
                </div>
                <hr class="hr-small" />
                <p class="search-section-header">Sub-category filters</p>
                <p>Showing search results per sub-category. To reset results toggle highlighted or everything box to re-sort.</p>
                {{ category_name="Category";category = vc.categories[category_name];"" }}
                <div>
                    <button
                        class="btn"
                        ng-class="{
                            'btn-primary': category.children[sc_name].selected,
                            'btn-default disabled btn-disabled-light': category.children[sc_name].count == 0,
                            'btn-default btn-strong': category.children[sc_name].count > 0 && !category.children[sc_name].selected
                        }"
                        ng-click="vc.selectBoxCategory(category_name, sc_name)"
                        ng-repeat="sc_name in keys(category.children) | orderBy:'toString()'"
                        style="margin:2px;width:200px;max-width:200px"
                    >
                        {{ sc_name }}
                        <span class="results-count">({{ category.children[sc_name].count | number }})</span>
                    </button>
                </div>
                <hr class="hr-small" />
            </div>
        </div>
        <div class="row">
            <div class="col-md-12" style="margin-bottom:5px;margin-top:10px">
                <p style="display: inline-block" class="search-section-header">Results</p>
                <a ng-click="vc.changeResultDisplayMode('columns')" href="javascript:void(0)">
                    <div class="btn btn-default" ng-class="{'active': vc.resultDisplayMode == 'columns'}"><i class="fa fa-columns"></i></div>
                    Snippet View</a>
                <a ng-click="vc.changeResultDisplayMode('rows')" href="javascript:void(0)">
                    <div class="btn btn-default" ng-class="{'active': vc.resultDisplayMode == 'rows'}"><i class="fa fa-list"></i></div>
                    Detailed view</a>
                <p>
                    <strong>{{ vc.counts.current_count | number }}</strong>
                    <span ng-show="vc.counts.current_count != vc.counts.total_count">(filtered)</span>
                    results from <strong>{{ vc.viewids.length | number }}</strong> data sources
                    <span ng-show="vc.counts.current_count != vc.counts.total_count">
                        (out of {{ vc.counts.total_count | number }} results from {{ vc.allViewids.length | number }} data sources)
                    </span>
                </p>
            </div>
            <div class="col-md-12" ng-show="vc.resultDisplayMode == 'rows'">
                <table class="table table-hover views-table">
                    <thead>
                        <tr>
                            <th>Source</th>
                            <th>Count</th>
                            <th>Categories</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr ng-repeat="viewid in vc.viewids">
                            <td class="col-md-9">
                                {{ view = vc.views[viewid];"" }}
                                <a target="_blank" ng-show="['Grants.gov: Opportunity', 'dkNET Pilot Funding News: List'].indexOf(view.name) === -1" ng-href="{{ viewid | viewURLPath: vc.portalName }}?q=<?php echo urlencode($vars["q"])?>&l=<?php echo urlencode($vars["l"]) . $subscription_get ?>" style="color:black">
                                    <h4>
                                        <span>{{ view.name }}</span>
                                        <i ng-show="['AntibodyRegistry: Antibodies', 'Cellosaurus: Cell Lines', 'Integrated: Animals', 'SciCrunch:Registry'].indexOf(view.name) === -1" class="fa fa-external-link"></i>
                                        <span ng-show="['AntibodyRegistry: Antibodies', 'Cellosaurus: Cell Lines', 'Integrated: Animals', 'SciCrunch:Registry'].indexOf(view.name) > -1" class="fa-stack fa-md">
                                          <i class="fa fa-circle fa-stack-2x" style="color:#1C2D5C"></i>
                                          <i class="fa fa-globe fa-stack-1x fa-inverse"></i>
                                        </span>
                                    </h4>
                                    <span ng-bind-html="view.description"></span>
                                    <span ng-show="view.archived">
                                        <br/>
                                        <?php echo \helper\htmlElement("archived-source-warning", Array("always-show" => true)) ?>
                                        This source has been archived. We are no longer crawling this source or it is no longer updating.
                                    </span>
                                </a>
                                <a target="_blank" ng-show="['Grants.gov: Opportunity', 'dkNET Pilot Funding News: List'].indexOf(view.name) > -1" ng-href="{{ viewid | viewURLPath: vc.portalName }}?q=<?php echo urlencode($vars["q"])?>&l=<?php echo urlencode($vars["l"]) . $subscription_get ?>&sort=desc&column=Close%20Date" style="color:black">
                                    <h4>
                                        <span>{{ view.name }}</span>
                                        <i ng-show="['AntibodyRegistry: Antibodies', 'Cellosaurus: Cell Lines', 'Integrated: Animals', 'SciCrunch:Registry'].indexOf(view.name) === -1" class="fa fa-external-link"></i>
                                        <span ng-show="['AntibodyRegistry: Antibodies', 'Cellosaurus: Cell Lines', 'Integrated: Animals', 'SciCrunch:Registry'].indexOf(view.name) > -1" class="fa-stack fa-md">
                                          <i class="fa fa-circle fa-stack-2x" style="color:#1C2D5C"></i>
                                          <i class="fa fa-globe fa-stack-1x fa-inverse"></i>
                                        </span>
                                    </h4>
                                    <span ng-bind-html="view.description"></span>
                                    <span ng-show="view.archived">
                                        <br/>
                                        <?php echo \helper\htmlElement("archived-source-warning", Array("always-show" => true)) ?>
                                        This source has been archived. We are no longer crawling this source or it is no longer updating.
                                    </span>
                                </a>
                            </td>
                            <td class="col-md-1">{{ view.count | number }}</td>
                            <td class="col-md-2">
                                <span
                                    ng-click="vc.selectBoxCategory(category.parent, category.child)"
                                    ng-repeat="category in view.categories | orderBy:categoryOrderBy"
                                    ng-class="{'selected-category-badge': vc.categories[category.parent].children[category.child].selected}"
                                    style="cursor:pointer;margin:2px"
                                    class="badge"
                                >
                                    {{ category.child }}
                                </span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="col-md-12" ng-show="vc.resultDisplayMode == 'columns'">
                <div class="col-md-4 col-sm-12" ng-repeat="chunkViewId in vc.chunkedViewIds">
                    <div class="single-view row" ng-repeat="viewId in chunkViewId">
                        {{ view = vc.views[viewId];"" }}
                        <div class="col-md-9">
                            <!-- <a style="color:black" target="_blank" ng-href="/<?php echo $community->portalName ?>/data/source/{{ viewId }}/search?q=<?php echo urlencode($vars["q"])?>&l=<?php echo urlencode($vars["l"]) . $subscription_get ?>">{{ vc.views[viewId]["name"] }}</a> -->
                            <a style="color:black" ng-show="['Grants.gov: Opportunity', 'dkNET Pilot Funding News: List'].indexOf(view.name) === -1" target="_blank" ng-href="{{ viewId | viewURLPath: vc.portalName }}?q=<?php echo urlencode($vars["q"])?>&l=<?php echo urlencode($vars["l"]) . $subscription_get ?>" uib-popover-template="'/templates/view-popover.html'" popover-trigger="'mouseenter'">{{ vc.views[viewId]["name"] }}</a>
                            <a style="color:black" ng-show="['Grants.gov: Opportunity', 'dkNET Pilot Funding News: List'].indexOf(view.name) > -1" target="_blank" ng-href="{{ viewId | viewURLPath: vc.portalName }}?q=<?php echo urlencode($vars["q"])?>&l=<?php echo urlencode($vars["l"]) . $subscription_get ?>&sort=desc&column=Close%20Date" uib-popover-template="'/templates/view-popover.html'" popover-trigger="'mouseenter'">{{ vc.views[viewId]["name"] }}</a>
                            <span ng-show="view.archived" uib-popover="This source has been archived.  We are no longer crawling it or it is no longer updating with new data." popover-trigger="'mouseenter'"><i style="color:orange" class="fa fa-warning"></i></span>
                        </div>
                        <div class="col-md-3" style="text-align:right">
                            <span ng-hide="vc.subscription_set">{{ vc.views[viewId]["count"] | number }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="container well">
            <h4><strong>Sorry, no results were found based on your filter setting: {{ vc.category_filters }}.</strong></h4>
            <h4>Click Reset Search button and try a different search! </h4>
            <h4>Search Suggestions:</h4>
            <ul>
                <li>Check your spelling</li>
                <li>Try more general words or different words that mean the same thing</li>
                <li>Unselect the synonyms</li>
            </ul>
        </div>
    <?php endif ?>

    <script type="text/ng-template" id="output-type-template.html">
        <p>{{ vc.outputTypeTooltip(sc_name) }}</p>
    </script>
</div>
<?php if($vars["q"] && $vars["q"] != "*"): ?>
    <?php if($results["count"] > 1000): ?>
        <hr />
        <div class="container well">
            <p>If you have too many results, here are some search tips to narrow down your query:</p>
            <ul>
                <li>Try using multiple search terms and joining them with the AND operator (eg: mtor AND metabolism AND diabetes).</li>
                <li>Add quotes to your query if it is multiple words.</li>
                <li>Try excluding synonyms from the query expansion.</li>
            </ul>
        </div>
    <?php endif ?>
<?php endif ?>

<script>
    window.globals = window.globals || {};
    window.globals.views = <?php echo json_encode($formatted_results["sources"]); ?>;
    window.globals.viewids = <?php echo json_encode(array_keys($formatted_results["sources"])); ?>;
    window.globals.community_viewids = <?php echo json_encode($community_views_ids) ?>;
    window.globals.portalName = "<?php echo $community->portalName ?>";
</script>

<script src="/js/module-utilities.js"></script>
<script src="/js/data.view.js"></script>

<script>
    $(function() {
        $("a.accordion-toggle").click(function(e) {
            e.preventDefault();
        });
    });

    $(".old-interface").click(function(e) {
        createCookie("old-interface-data", 1, false);
        window.location.reload(false);
    });
</script>
