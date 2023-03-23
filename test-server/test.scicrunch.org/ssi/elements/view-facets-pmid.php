<?php

$results = $data["results"];
$vars = $data["vars"];
$search = $data["search"];

$facets = $results->facets();
foreach($facets as $facet_name => $facet_values) {
    switch($facet_name) {
        case "Publication Year":
            $tmp = [];
            foreach ($facet_values as $facet) {
                $tmp[$facet["value"]] = $facet["count"];
            }
            krsort($tmp);   ## sort an associative array in descending order, according to the key
            $facet_values = [];
            foreach ($tmp as $key => $value) {
                $facet_values[] = Array(
                    "value" => $key,
                    "count" => $value
                );
            }
            $facets[$facet_name] = $facet_values;
            break;
    }
}

## modified facets order -- Vicky-2019-1-24
// switch($vars["nif"]) {
//     case "nlx_144509-1":  ## Tools
//         $facet_names = array("Resource Type", "Keywords", "Organism", "Related Condition", "Funding Agency", "Website Status");
//         break;
//     case "SCR_013869-1":  ## Cell lines
//         $facet_names = array("Vendor", "Category", "Disease", "Organism", "References", "Sex");
//         break;
//     case "nif-0000-07730-1":  ## Antibodies
//         $facet_names = array("Target Antigen", "Target Organism", "Vendor", "Clonality", "Host Organism");
//         break;
//     case "nlx_154697-1":  ## Organisms
//         $facet_names = array("Database", "Species", "Background", "Genomic Alteration", "Affected Gene", "Phenotype", "Availability");
//         break;
// }

if(empty($facets)) {
    return;
}

?>

<div>
    <h3>Facets</h3>
    <form class="multi-facets" url="<?php echo $search->generateURL($vars) ?>">
        <ul class="list-group sidebar-nav-v1" id="sidebar-nav">
            <?php foreach($facets as $facet_name => $facet_values):
              ## modified facets order -- Vicky-2019-1-24
              //foreach($facet_names as $facet_name): $facet_values = $facets[$facet_name];
            ?>
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
                                //$facet_text = htmlentities($facet_text);
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
            <?php endforeach ?>
        </ul>
        <button type="submit" class="btn-u">Perform Search</button>
    </form>

    <script type="text/javascript" src="/js/facets.js"></script>
</div>
