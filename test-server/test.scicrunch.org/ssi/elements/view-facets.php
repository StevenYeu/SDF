<?php
    $results = $data["results"];
    $search = $data["search"];
    $vars = $data["vars"];
    $applied_facets = array_map(function ($f) {return html_entity_decode(substr($f, strpos($f, ":")+1));}, $search->facet);
?>

<?php if(count($results["facets"])): ?>
    <script>
        document.categoryGraphData = <?php echo json_encode($results["graph"]) ?>;
    </script>
    <h3>
        Facets
        <a href="javascript:categoryGraph2(document.categoryGraphData)">
            <i class="fa fa-bar-chart-o"></i>
        </a>
        <i class="help-tooltip" data-name="facet.html"></i>
    </h3>

    <form class="multi-facets" url="<?php echo $search->generateURL($vars) ?>">
        <a href="javascript:void(0)" class="facet-sort-alpha">Sort alphabetically</a> | <a href="javascript:void(0)" class="facet-sort-count">Sort by count</a>
        <ul class="list-group sidebar-nav-v1" id="sidebar-nav">
            <?php foreach ($results['facets'] as $column => $array): ?>
                <?php $column_href = str_replace(Array(" ", "/"), "_", $column); ?>
                <li class="list-group-item list-toggle" data-toggle="collapse" data-parent="#sidebar-nav" href="#collapse-<?php echo $column_href ?>">
                    <a href="javascript:void(0)"><?php echo $column ?></a>
                    <ul id="collapse-<?php echo $column_href ?>" class="collapse">
                        <?php foreach ($array as $facet): ?>
                            <?php
                                $newVars = $vars;
                                $newVars['facet'][] = $column . ':' . str_replace("#", "%23", $facet['value']);
                                $newVars["page"] = 1;
                                $facet_text = strlen($facet["value"]) > 20 ? substr($facet["value"], 0, 20) . "..." : $facet["value"];
                                $facet_text = htmlentities($facet_text);
                                $facet_in_use = in_array($facet['value'], $applied_facets);
                            ?>
                            <li style="border-top:1px solid #ddd">
                                <a href="<?php echo $facet_in_use ? "#" : $search->generateURL($newVars) ?>"
                                   style="width:90%;padding-right:30px;display:inline-block;border:0"
                                   data-count="<?php echo $facet["count"] ?>"
                                   title="<?php echo $facet["value"] ?>">
                                    <?php echo $facet_text ?> (<?php echo number_format($facet['count']) ?>)
                                </a>
                                <div class="pull-right" style="display: <?php echo $facet_in_use ? "none" : "block" ?>">
                                    <div class="checkbox">
                                        <input type="checkbox" class="facet-checkbox" style="margin-top:9px" column="<?php echo rawurlencode($column) ?>" facet="<?php echo rawurlencode($facet['value']) ?>"/>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach ?>
                    </ul>
                </li>
            <?php endforeach ?>
        </ul>
        <button type="submit" class="btn-u">Perform Search</button>
    </form>

    <script type="text/javascript" src="/js/facets.js"></script>
<?php endif ?>
