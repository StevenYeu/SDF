<?php
    include 'process-elastic-search.php';

    $community = $data["community"];
    $search_manager = $data['search_manager'];
    $result = $data['result'];
    $source_image = (int)str_replace("scr_", "", strtolower($data['rrid'])).".png";

    ## generated data information
    $data_info = Array();
    $data_info['URL'] = '<a target="_blank" href="'.$result->getRRIDField("url").'">'.$result->getRRIDField("url").'</a>';
    $data_info['Description'] = $result->getRRIDField("description");
    foreach($search_manager->fields() as $field_name) {
        if (in_array($field_name->name, ["Uid", "Mentions Count", "Old URLs"])) continue;
        if(!$result->getField($field_name->name) || !$field_name->visible("single-item") || $result->getField($field_name->name) == "CVCL:") continue;
        switch($field_name->name) {
            case "Alternate URLs":
                $urls = explode(", ", $result->getField($field_name->name));
                $url_list = Array();
                foreach ($urls as $url) {
                    $url_list[] = '<a target="_blank" href="'.$url.'">'.$url.'</a>';
                }
                $data_info[$field_name->name] = "<br>".join("<br>", $url_list);
                break;
            case "References":
                $data_info[$field_name->name] = join(", ", buildLinks($result->getField($field_name->name), $community));
                break;
            default:
                $data_info[$field_name->name] = $result->getField($field_name->name);
        }
    }

    $data_order = array_keys($data_info);
    $top_info_count = 16;
    if(!empty($result->getSpecialField("report-data-order"))) {
        if(!empty($result->getSpecialField("report-data-order")["data_order"])) $data_order = $result->getSpecialField("report-data-order")["data_order"];
        if(!empty($result->getSpecialField("report-data-order")["top_info_count"])) $top_info_count = $result->getSpecialField("report-data-order")["top_info_count"];
    }
?>

<div class="container">
    <p style="margin: 40px 0; color: #1c2d5c; font-size: 40px"><b><?php echo $data_info['Resource Name'] ?></b></p>

    <div class="tab-v5">
        <ul class="nav nav-tabs nav-tabs-js" role="tablist">
            <li class="active"><a href="#description" role="tab" data-toggle="tab">Description</a></li>
            <!-- <li><a href="#views" role="tab" data-toggle="tab">Views</a></li>
            <li><a href="#licenses" role="tab" data-toggle="tab">License</a></li> -->
        </ul>

        <div class="tab-content">
            <!-- Description -->
            <div class="tab-pane fade in active" id="description">
                <div class="row">
                    <div class="col-md-7">
                        <p><?php echo \helper\formattedDescription($data_info['Description']) ?></p><br>
                    </div>

                    <div class="col-md-5">
                        <div class="responsive-image" style="text-align: center">
                            <img src='<?php echo "https://dknet.org/upload/resource-images/".$source_image ?>' class="responsive-image"/>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-12">
                        <h3 class="heading-md margin-bottom-20">Details</h3>

                        <div class="row">

                            <?php
                            $count = 0;
                            foreach ($data_info as $key => $value) {
                                if ($key == 'Resource Name' || $key == 'Description')
                                    continue;
                                if ($count < 8)
                                    $left[$key] = $value;
                                else
                                    $right[$key] = $value;
                                $count++;
                            }
                            ?>
                            <div class="col-sm-6">
                                <ul class="list-unstyled specifies-list">
                                    <?php foreach ($left as $key => $value) {
                                        echo '<li style="margin-left:15px;text-indent:-15px;margin-bottom:5px;"><i class="fa fa-caret-right"></i> <b><u>' . $key . ':</u></b> ' . $value . '</li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                            <div class="col-sm-6">
                                <ul class="list-unstyled specifies-list">
                                    <?php foreach ($right as $key => $value) {
                                        echo '<li style="margin-left:15px;text-indent:-15px;margin-bottom:5px;"><i class="fa fa-caret-right"></i> <b><u>' . $key . ':</u></b> ' . $value . '</li>';
                                    }
                                    ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- End Description -->

            <!-- Reviews -->
            <div class="tab-pane fade" id="licenses">
                <div style="margin-bottom: 10px;"><b>License URL: </b> <a href="<?php echo $views_data['license-url'] ?>"><?php echo $views_data['license-url'] ?></a></div>
                <div style="margin-bottom: 10px;margin-left: 15px;text-indent: -15px"><b>License Information: </b><br/><?php echo $views_data['license'] ?></div>
            </div>
            <div class="tab-pane fade" id="views">
                <?php echo \helper\generateHTMLViewsTab($views, $community); ?>
            </div>
            <!-- End Reviews -->
        </div>
    </div>
</div>
