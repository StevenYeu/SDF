<?php
$category_tree = $results["info"]["tree"]["children"];
$category_tree_count = count($category_tree);

foreach($category_tree as &$category) {
    foreach($category["children"] as &$subcategory) {
        foreach($subcategory["children"] as &$view) {
            if(isset($allSources[$view["nif"]])) {
                $view["description"] = $allSources[$view["nif"]]->description;
                $view["categories"] = $allSources[$view["nif"]]->categories;
            }
        }
    }
}

// category graph data
$count_single = 0;
foreach($results["info"]["counts"]["nif"] as $count) {
    if($count > 0) $count_single += 1;
}
if($count_single === 1) {
    $category_graph_data = $results["graph"];
} else {
    $category_graph_data = $results["info"]["tree"];
}

//if(isset($allSources[$subcat_child["nif"]])) $view_description = $allSources[$subcat_child["nif"]]->description;

?>
<style>
    .view-row:hover {
        background: #DDD;
    }
</style>
<div class="container s-results margin-bottom-50" ng-app="categoryApp" ng-cloak ng-controller="categoryController as cc">
    <div class="col-md-2 col-sm-12">
        <h3>Options</h3>
        <ul class="list-unstyled">
            <li><a href="javascript:categoryGraph2(<?php echo "'". str_replace("%27", "\%27", str_replace('"','%22',json_encode($category_graph_data)))."'" ?>)">Category Graph <i class="fa fa-graph"></i></a></li>
            <li><?php echo \helper\htmlElement("modified-date-picker"); ?></li>
            <?php if(strpos($_SERVER["QUERY_STRING"], "v_status:") === false): ?>
                <li><?php echo \helper\htmlElement("new-records-link", Array("vars" => $vars, "search" => $search)); ?></li>
            <?php endif ?>
        </ul>
        <hr/>
        <?php echo $search->currentFacets($vars, 'table') ?>
   </div>

    <!-- results -->
    <div class="col-md-10 col-sm-12">
        <div>
            <img src="/images/BetaTest64x54.png" style="display:inline-block" />
            <h4 style="display:inline-block">
                This is our new data interface which we are currently testing.
                Our previous interface is still available if you would still like to use it.
                <a class="old-interface" href="javascript:void(0)"><button class="btn btn-primary">Use other interface</button></a>
            </h4>
        </div>
        <hr/>
        <div class="row">
            <?php
            $newVars = $vars;
            $newVars["category"] = "data";
            $newVars["subcategory"] = NULL;
            $newVars['nif'] = null;
            $newVars['uuid'] = false;
            $newVars['facet'] = null;
            $newVars['filter'] = null;
            $newVars['page'] = 1;
            ?>
            <strong>Don't see what you're looking for?  Search through <a href="<?php echo $search->generateURL($newVars) ?>">all sources</a>.</strong>
        </div>
        <span class="results-number" style="margin-top:10px;">
            <?php echo $search->getResultText("resource", Array(count($results["results"]), $results["total"], count($results["info"]["counts"]["nif"]), $GLOBALS["notif_id"], $subscription_data["modified_time"]), $results["expansion"], $vars); ?>
        </span>
        <div class="row">
            <div class="col-md-4 col-sm-12" ng-repeat="col in cc.col_indexes">
                <div class="row" ng-repeat="row_index in col" ng-hide="cc.categories[row_index].rows_size == 0">
                    <h3>{{ cc.categories[row_index].name }}</h3>
                    <div class="container" ng-repeat="subcat in cc.categories[row_index].children" ng-hide="subcat.size == 0">
                        <h5 ng-hide="subcat.name == 'Not in a Subcategory'"><u>{{ subcat.name }}</u></h5>
                        <div class="row view-row" uib-popover-template="'/templates/view-popover.html'" popover-trigger="'mouseenter'" ng-repeat="view in subcat.children" ng-hide="view.size == 0">
                            <div class="col-md-9"><a style="color:#72c02c" ng-href="{{ view.url }}">{{ view.name }}</a></div>
                            <div style="text-align:right" class="col-md-3">{{ view.size | number }}</div>
                        </div>
                    </div>
                    <hr/>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- category graph -->
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
    window.globals = window.globals || {};
    window.globals.categories = <?php echo json_encode($category_tree); ?>
</script>
<script src="/js/angular-1.7.9/angular.min.js"></script>
<script src="/js/ui-bootstrap-tpls-2.5.0.min.js"></script>
<script src="/js/module-utilities.js"></script>
<script>
    (function() {
        var category_app = angular.module("categoryApp", ["ui.bootstrap", "utilitiesApp"]);

        category_app.controller("categoryController", ["$scope", "$log", function($scope, $log) {
            var that = this;
            this.categories = window.globals.categories;
            this.ncol = 3;

            this.rechunk = function() {
                for(var i = 0; i < that.categories.length; i++) estimateCategoryRows(that.categories[i]);
                binIntoColumns(that.categories, that.ncol);
            };

            // function to get the number of views that have at lease one result
            function categoryNonZeroViews(category) {
                var sum = 0;
                if(category.hasOwnProperty("size")) return category.size > 0 ? 1 : 0;
                if(!category.hasOwnProperty("children")) return 0;
                for(var i = 0; i < category.children.length; i++) {
                    var subcat = category.children[i];
                    sum += categoryNonZeroViews(subcat);
                }
                return sum;
            }

            // function to estimate the number of rows that each category will take up
            // category title = 3 rows, subcategory title = 2 rows, view = 1 row
            // mutates category
            function estimateCategoryRows(category) {
                var category_title_size = 2;
                var subcat_title_size = 1;
                var view_size = 1;

                var total_count = 0;
                for(var i = 0; i < category.children.length; i++) {
                    var subcat = category.children[i];
                    var subcat_views_count = categoryNonZeroViews(subcat);
                    subcat.size  = subcat_views_count;
                    if(subcat_views_count > 0) total_count += (subcat_views_count * view_size) + subcat_title_size;
                }
                if(total_count > 0) total_count += category_title_size;
                category.rows_size = total_count;
            }

            // greedily bins categories into columns based on the number of rows
            // mutates categories
            function binIntoColumns(categories, ncol) {
                var space_between = 2;

                var indexes = [];
                var total_rows_size = 0;
                var nonZeroCategories = 0;
                for(var i = 0; i < categories.length; i++) {
                    indexes.push({"index": i, "size": categories[i].rows_size, "used": false});
                    if(categories[i].rows_size > 0) {
                        total_rows_size += categories[i].rows_size;
                        nonZeroCategories += 1;
                    }
                }
                indexes = indexes.sort(function(a,b) {
                    if(a.size == b.size) return 0;
                    return a.size > b.size ? -1 : 1;
                });
                var max_per_col = Math.ceil(Math.ceil(total_rows_size / ncol) * 1.1) + Math.ceil((nonZeroCategories - ncol) / ncol);
                that.col_indexes = [];
                for(var i = 0; i < ncol; i++) {
                    var col_sum = 0;
                    var this_col_index = [];
                    for(var j = 0; j < indexes.length; j++) {
                        var index = indexes[j];
                        if(index.used) continue;
                        if(col_sum == 0 || col_sum + index.size < max_per_col || i == ncol - 1) {
                            if(col_sum != 0) col_sum += space_between;  // rows between groups
                            col_sum += index.size;
                            index.used = true;
                            that.categories[index.index].col_index = i;
                            this_col_index.push(index.index);
                        }
                    }
                    that.col_indexes.push(this_col_index);
                }
            }

            this.rechunk();
            $log.log(this.categories);

        }]);
    }());

    $(".old-interface").click(function(e) {
        createCookie("old-interface-resources", 1, false);
        window.location.reload(false);
    });
</script>
