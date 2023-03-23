<?php
    include '/process-elastic-search.php';

    $results = $data["results"];
    $vars = $data["vars"];
    $search = $data["search"];
    $facets = $results->facets();

    $facet_names = checkFacetNames($vars["nif"]);
    if(empty($facet_names)) $facet_names = array_keys($facets);

    if(empty($facets) || (count($facet_names) == 1 && $facet_names[0] == "Data Sources")) {
        return;
    }

    $indices = convertIndices();
?>

<div>
    <h3>Facets</h3>
    <form class="multi-facets" url="<?php echo $search->generateURL($vars) ?>">
        <ul class="list-group sidebar-nav-v1" id="sidebar-nav">
            <?php //foreach($facets as $facet_name => $facet_values):
              ## modified facets order -- Vicky-2019-1-24
              foreach($facet_names as $facet_name): $facet_values = $facets[$facet_name];
            ?>
                <?php if(count($facet_values) == 0 || (count($facet_values) == 1 && $facet_values[0]["value"] == "") || $facet_name == "Data Sources"): ?>
                <?php else: ?>
                    <?php $column_href = str_replace(Array(" ", "/"), "_", $facet_name); ?>
                    <li class="list-group-item list-toggle" data-toggle="collapse" data-parent="#sidebar-nav" href="#collapse-<?php echo $column_href ?>">
                        <a href="javascript:void(0)"><?php echo $facet_name ?></a>
                        <ul id="collapse-<?php echo $column_href ?>" class="collapse">
                            <?php
                                if($facet_name == "Issues") {
                                    $tmp = array();
                                    $tmp_list = array("no known issues", "warning", "problematic", "discontinued");
                                    foreach($facet_values as $facet) {
                                        $tmp[$facet["value"]] = $facet["count"];
                                    }
                                    $facet_values = Array();
                                    foreach($tmp_list as $item) {
                                        if(in_array($item, array_keys($tmp))) {
                                            $facet_values[] = Array(
                                                "value" => $item,
                                                "count" => $tmp[$item]
                                            );
                                        }
                                    }
                                }
                            ?>
                            <?php foreach($facet_values as $idx => $facet): ?>
                                <?php
                                    if ($idx == 20) break;    // only show top 20
                                    if(!$facet["value"]) {     // no blank facets
                                        continue;
                                    }
                                    if($facet_name == "Validation" && $facet["value"] == 1) $facet["value"] = "true";
                                    $newVars = $vars;
                                    $newVars["facet"][] = $facet_name . ":" . str_replace("#", "%23", $facet["value"]);
                                    $newVars["page"] = 1;
                                    // if ($facet_name == "Mentions" && $facet["value"] == "available") $facet["value"] = htmlentities("Yes");
                                    // else
                                    $facet["value"] = htmlentities($facet["value"]);
                                    if($facet_name == "Data Sources") {
                                        foreach ($indices as $key => $value) {
                                            if(\helper\startsWith($facet["value"], $key)) $facet_text = $value;
                                        }
                                    } else if($facet_name == "Mentions") {
                                        $facet_text = "yes";
                                    } else if($facet_name == "Validation") {
                                        $facet_text = "information available";
                                    } else if($facet_name == "Issues" && $facet["value"] == "warning") {
                                        $facet_text = "issues found";
                                    } else {
                                        $facet_text = strlen($facet["value"]) > 20 ? substr($facet["value"], 0, 20) . "..." : $facet["value"];
                                    }
                                    //$facet_text = htmlentities($facet_text);
                                    $facet_flag = false;
                                    if(in_array($facet_name.":".$facet["value"], $vars["facet"])) $facet_flag = true;
                                ?>
                                <li style="border-top: 1px solid #ddd">
                                    <?php if($facet_flag): ?>
                                        <a>
                                            <?php echo $facet_text ?> (<?php echo number_format($facet["count"]) ?>)
                                        </a>
                                    <?php else: ?>
                                        <?php if($facet_name == "Mentions"): ?>
                                            <?php
                                                $newVars["sort"] = "desc";
                                                $newVars["column"] = "Mentions Count";
                                            ?>
                                        <?php endif ?>
                                            <a href="<?php echo $search->generateURL($newVars) ?>" style="width:90%;padding-right:30px;display:inline-block;border:0" data-count="<?php echo $facet["count"] ?>" title="<?php echo $facet["value"] ?>">
                                                <?php echo $facet_text ?> (<?php echo number_format($facet["count"]) ?>)
                                            </a>
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
<hr />
