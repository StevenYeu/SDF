<?php
    include 'process-elastic-search.php';

    $community = $data["community"];

    $search_manager = ElasticRRIDManager::managerByViewID("nlx_144509-1");
    if(is_null($search_manager)) return;

    $res = $search_manager->getAliases();
    $res = preg_split('/\s+/', $res);   // split string based on tab
    $souce_indices = Array();
    foreach ($res as $index) {
        if(\helper\startsWith($index, "scr_")) $souce_indices[] = $index;
    }

    $res = $search_manager->getIndices(join(",", $souce_indices));
    $res = preg_split('/\n|\r\n?/', $res);  // split string based on \n or \r\n

    $sources = Array();
    foreach ($res as $value) {
        if($value == "") continue;
        $val = preg_split('/\s+/', $value);
        $source_index = $val[2];
        $source_rrid = explode("-", $val[2])[0];
        $source_docs_count = $val[6];

        $results = $search_manager->searchRRID($source_rrid);
        $result = $results->getByIndex(0);
        $sources[] = Array(
            "name" => $result->getField("Resource Name"),
            "description" => $result->getField("Description"),
            "rrid" => $source_rrid,
            // "id" => explode(", ", $result->getField("Alternate IDs"))[0] . "-1",
            "logo" => (int)str_replace("scr_", "", strtolower($source_rrid)).".png",
            "docs_count" => $source_docs_count,
        );
    }

    usort($sources, function($a, $b) {return strcmp($a["name"], $b["name"]);});

?>

<div class="container content">
    <div class="row">
        <div class="col-md-9">
            <div class="tab-v5">
                <ul class="nav nav-tabs nav-tabs-js" role="tablist">
                    <li class="active"><a href="#sources" role="tab" data-toggle="tab">RIN Sources</a></li>
                    <!-- <li><a href="#all" role="tab" data-toggle="tab">All Sources</a></li> -->
                </ul>

                <div class="tab-content">
                    <div class="tab-pane fade in active" id="sources">
                        <?php foreach ($sources as $rrid => $data_info): ?>
                            <div class="row">
                                <div class="col-md-2">
                                    <img src='<?php echo "https://dknet.org/upload/resource-images/".$data_info["logo"] ?>' style="width:100%"/>
                                </div>

                                <div class="col-md-10">
                                    <h2><a target="_blank" href="<?php echo $community->fullURL() ?>/rin/sources/<?php echo $data_info["rrid"] ?>"><?php echo $data_info["name"] ?></a></h2>
                                    <p><img src="https://dknet.org/images/scicrunch.png" style="width:15px" /> <?php echo number_format($data_info["docs_count"]) ?> resources</p>
                                    <p class="truncate-long"><?php echo \helper\formattedDescription($data_info['description']) ?></p>
                                </div>
                            </div>
                            <hr>
                        <?php endforeach ?>

                    </div>

                    <div class="tab-pane fade" id="all">

                    </div>
                </div>

                <!-- End Pagination -->
            </div>
        </div>
        <!--/col-md-9-->

        <div class="col-md-3">
            <!-- Our Services -->
            <h1>Category Breakdown</h1>
            <div class="headline" style="margin-top:40px">
                <h2><a target="_blank" href="<?php echo $community->fullURL() ?>/rin/rrids">dkNET sources</a></h2>
            </div>

            <?php foreach ($sources as $rrid => $data_info): ?>
                <p><a target="_blank" href="<?php echo $community->fullURL() ?>/rin/sources/<?php echo $data_info["rrid"] ?>"><?php echo $data_info["name"] ?></a></p>
            <?php endforeach ?>


        </div>
        <!--/col-md-3-->
    </div>
    <!--/row-->
</div>
<!--/container-->
<!--=== End Content Part ===-->
