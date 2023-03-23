<!--=== Search Block Version 2 ===-->
<?php if (!$data["id"] && (!$data["mode"] || $data["mode"] != 'edit') && $data["search_bar_type"] !== "none") { ?>
    <div class="search-block-v2" style="padding:30px 0 38px">
        <div class="container">
            <div class="col-md-6 col-md-offset-3">
                <h2><?php echo $data["search_message"] ?></h2>

                <?php if ($data["search_bar_type"] === "simple"): ?>
                    <form method="get" action="<?php echo $data["search_action"] ?>">
                        <div class="input-group">
                            <input type="text" class="form-control" name="<?php echo $data["query_label"] ?>" placeholder="<?php echo $data["searchText"] ?>" value="<?php echo $data["display_query"] ?>">
			    <input type="hidden" name="filter" value="<?php echo $data["filter"] ?>"/>
                <!-- Added by Manu --> 
                <script>
                    console.log("<?php echo $data["display_query"]?>")
                </script>           
                <?php if (empty($data["display_query"])) { ?>  
			    <!-- <input type="hidden" name="status" value="curated"/>  commented out by Steven--> 
                <?php } ?>  
                <!-- Added by Manu -->
                            <span class="input-group-btn">
                                <button class="btn-u" type="search"><i class="fa fa-search"></i></button>
                            </span>
                        </div>
                    </form>
                    <?php if($data["type"]=='resources'){?>
                    <p style="text-align: center;padding-top:10px;margin-bottom: 0">We support boolean queries, use +,-,<,>,~,* to alter the weighting of terms</p>
                    <?php } ?>
                <?php endif ?>
            </div>
            <?php if($data["search_bar_type"] === "autocomplete"): ?>
                <input type="hidden" class="search-banner-type" value="<?php echo $data["search_banner_type"] ?>" />
                <?php $display_query = $data["display_query"]; ?>
                <?php include $data["docroot"]."/components/body/parts/parralax/search-banner.php" ?>
            <?php endif ?>
        </div>
    </div>
<?php } ?>
<!--/container-->
<!--=== End Search Block Version 2 ===-->
