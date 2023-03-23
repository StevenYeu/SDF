<?php

    $results = $data["results"];
    $vars = $data["vars"];
    $search = $data["search"];
    $results_types = $data["results_types"];
    $community = $data["community"];

    $facets = $results->facets();

    ## modified facets order -- Vicky-2019-1-24
    switch($vars["type"]) {
        case "interlex":
            $facet_names = array("Superclasses", "Ancestors");
            break;
    }

    if(empty($facets)) {
        return;
    }

?>

<?php if($results->totalCount() > 0): ?>
    <div>
        <h3>Facets</h3>
        <form class="multi-facets" url="<?php echo $search->generateURL($vars) ?>">
            <ul class="list-group sidebar-nav-v1" id="sidebar-nav">
                <?php
                  ## modified facets order -- Vicky-2019-1-24
                  foreach($facet_names as $facet_name): $facet_values = $facets[$facet_name];
                ?>
                    <?php if((count($facet_values) == 1 && $facet_values[0]["value"] == "") || count($facet_values) == 0): ?>
                    <?php else: ?>
                        <?php $column_href = str_replace(Array(" ", "/"), "_", $facet_name); ?>
                        <li class="list-group-item list-toggle" data-toggle="collapse" data-parent="#sidebar-nav" href="#collapse-<?php echo $column_href ?>">
                            <a href="javascript:void(0)"><?php echo $facet_name ?></a>
                            <ul id="collapse-<?php echo $column_href ?>" class="collapse">
                                <?php foreach($facet_values as $idx => $facet): ?>
                                    <?php
                                        if ($idx == 20) break;    // only show top 20
                                        if(!$facet["value"]) {     // no blank facets
                                            continue;
                                        }
                                        $newVars = $vars;
                                        $newVars["facet"][] = $facet_name . ":" . str_replace("#", "%23", $facet["value"]);
                                        $newVars["page"] = 1;
                                        $facet["value"] = htmlentities($facet["value"]);
                                        $facet_text = strlen($facet["value"]) > 20 ? substr($facet["value"], 0, 20) . "..." : $facet["value"];
                                        $facet_flag = false;
                                        if(in_array($facet_name.":".$facet["value"], $vars["facet"])) $facet_flag = true;
                                    ?>
                                    <li style="border-top: 1px solid #ddd">
                                        <?php if($facet_flag): ?>
                                            <a>
                                            <?php if($facet_name == "Mentions"): ?>
                                                Yes (<?php echo number_format($facet["count"]) ?>)
                                            <?php else: ?>
                                                <?php echo $facet_text ?> (<?php echo number_format($facet["count"]) ?>)
                                            <?php endif ?>
                                            </a>
                                        <?php else: ?>
                                            <?php if($facet_name == "Mentions"): ?>
                                                <?php
                                                    $newVars["sort"] = "desc";
                                                    $newVars["column"] = "Mentions Count";
                                                ?>
                                                <a href="<?php echo $search->generateURL($newVars) ?>" style="width:90%;padding-right:30px;display:inline-block;border:0" data-count="<?php echo $facet["count"] ?>" title="Yes">
                                                    Yes (<?php echo number_format($facet["count"]) ?>)
                                                </a>
                                            <?php else: ?>
                                                <a href="<?php echo $search->generateURL($newVars) ?>" style="width:90%;padding-right:30px;display:inline-block;border:0" data-count="<?php echo $facet["count"] ?>" title="<?php echo $facet["value"] ?>">
                                                    <?php echo $facet_text ?> (<?php echo number_format($facet["count"]) ?>)
                                                </a>
                                            <?php endif ?>
                                            <div class="pull-right">
                                                <div class="checkbox">
                                                    <input type="checkbox" class="facet-checkbox" style="margin-top: 9px" column="<?php echo rawurlencode($facet_name) ?>" facet="<?php echo rawurlencode($facet["value"]) ?>" />
                                                </div>
                                            </div>
                                        <?php endif ?>
                                    </li>
                                <?php endforeach ?>
                            </ul>
                        </li>
                    <?php endif ?>
                <?php endforeach ?>
            </ul>
            <button type="submit" class="btn-u">Perform Search</button>
        </form>

        <script type="text/javascript" src="/js/facets.js"></script>
    </div>
    <hr class="hr-small" />
<?php endif ?>
