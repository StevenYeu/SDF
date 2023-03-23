<link rel="stylesheet" href="/css/custom.css">
<!-- Go to www.addthis.com/dashboard to customize your tools -->
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5c34b34e285f1d57"></script>

<?php

//$coming_soon_des = "This section is still a work in progress. We currently have good coverage for some locations such as Los Angeles, San Diego, and Boston areas, but not all areas are covered in Collaboration Network yet.";

$view = $data["view"];
$rrid = $data["rrid"];
$tab = $data["tab"];
$id = $_GET["i"];   // get uid
$cm_rrid = $_GET["cm"];   // get co-mentions rrid
$cm_name = $_GET["cn"];   // get co-mantions name
$cm_view = $_GET["cv"];   // get co-mantions view
$community = $data["community"];

$dknet_flag = false;
if($community->rinStyle()) {
    $dknet_flag = true;
    include 'process-elastic-search.php';
}

$sci_flag = false;
if ($community->portalName == "scicrunch") $sci_flag = true;

$protocol_flag = false;
if($view == "protocol") {
    $rrid = str_replace('$U+002F;', '/', $rrid);
    $protocol_flag = true;
}

switch($tab) {
    case "mentions":
        $page_type = "mentions";
        $page_title = "Resource Usage Report";
        break;
    case "co-mentions":
        $page_type = "co-mentions";
        $page_title = "Co-mentions Report";
        break;
    case "organizations":
        $page_type = "organizations";
        $page_title = "Organizational Usage Report";
        break;
    case "organization-mentions":
        $page_type = "organization-mentions";
        $page_title = "Organizational Usage Mention List";
        break;
    default:
        $page_type = "info";
        $page_title = "Resource Summary Report";
        break;
}

$search_manager = ElasticRRIDManager::managerByViewID($view);
if(is_null($search_manager)) {
    return;
}
if($protocol_flag) $results = $search_manager->searchDOI($rrid);
else $results = $search_manager->searchRRID($rrid);

if($results->hitCount() == 0) {
    echo \helper\errorPage("noresource", NULL, false);
    return;
}

$result = findResult($results, $id);
$id = $result->getRRIDField("id");

if(in_array($page_type, ["info", "organizations", "organization-mentions"])) {
    $children_results = $search_manager->searchChildren($rrid);
    $children = Array();
    $total_children_mentions_count = 0;
    $grand_children_count = 0;
    $organization_mentions_rrids = Array();
    if($result->getRRIDField("mentionCount") > 0) $organization_mentions_rrids[] = $result->getRRIDField("curie");
    foreach($children_results as $child) {
        $grand_children = Array();
        $grand_children_results = $search_manager->searchGrandChildren($child->getRRIDField("curie"));
        $total_children_mentions_count = $total_children_mentions_count + $child->getRRIDField("mentionCount");
        if($child->getRRIDField("mentionCount") > 0) $organization_mentions_rrids[] = $child->getRRIDField("curie");
        foreach ($grand_children_results as $grand_child) {
            $grand_children[] = Array(
                "rrid" => $grand_child->getRRIDField("curie"),
                "name" => $grand_child->getRRIDField("name"),
                "total_mentions_count" => $grand_child->getRRIDField("mentionCount"),
            );
            $grand_children_count += 1;
            $total_children_mentions_count = $total_children_mentions_count + $grand_child->getRRIDField("mentionCount");
            if($grand_child->getRRIDField("mentionCount") > 0) $organization_mentions_rrids[] = $grand_child->getRRIDField("curie");
        }

        $children[] = Array(
            "rrid" => $child->getRRIDField("curie"),
            "name" => $child->getRRIDField("name"),
            "total_mentions_count" => $child->getRRIDField("mentionCount"),
            "grand_children" => $grand_children,
        );
    }
    $organization_mentions_rrids = join(",", $organization_mentions_rrids);
}

$parents = Array();
$parents_name = explode(",", $result->getRRIDField("parents-name"));
$parents_id = explode(",", $result->getRRIDField("parents-id"));

foreach ($parents_id as $i => $v) {
    if($v !="") $parents = Array(trim($v) => trim($parents_name[$i])) + $parents;
}

$multi_vendors = Array();
if($results->hitCount() > 1 && $page_type == "info" && $result->getRRIDField("type") != "tool") {
    foreach($results as $res) {
        if($result->getRRIDField("curie") == $res->getRRIDField("curie")) {
            $multi_vendors[] = Array(
                "id" => $res->getRRIDField("id"),
                "value" =>  $res->getRRIDField("vendors-name")." - ".$res->getField("Catalog Number"),
            );
        }
    }
}

## co-mentions, search multiple rrids -- Vicky-2019-3-15
$rrids = [$result->getRRIDField("curie")];
if ($cm_rrid != "") $rrids[] = $cm_rrid;
$rrids = join(",", $rrids);

switch($result->getRRIDField("type")) {
    case "antibody":
        $type_name = "Antibody";
        $source = "Antibody Registry";
        $item_views = NULL;
        $source_database = NULL;
        break;
    case "tool":
        $resource_obj = new Resource();
        $resource_obj->getByRID(str_replace("RRID:", "", $rrid));
        $type_name = "Resource";
        $source = "SciCrunch Registry";
        $item_views = \helper\getViewsFromOriginalID($resource_obj->original_id);
        $source_database = NULL;
        break;
    case "Cell Line":   ##change "cellline" to "Cell Line" -- Vicky-2018-11-9
        $type_name = "Cell Line";
        $source = "Cellosaurus";
        $item_views = NULL;
        $source_database = NULL;
        break;
    case "Organism":  ##change "organism" to "Organism" -- Vicky-2018-11-13
        $type_name = "Organism";
        $source = "Integrated Animals";
        $item_views = NULL;
        $source_database = $result->getField("Database"); ##added source database information -- Vicky-2018-11-21
        break;
    case "Plasmid":   ##added "plasmid" -- Vicky-2019-7-5
        $type_name = "Plasmid";
        $source = "Addgene";
        $source_database = NULL;
        break;
    case "Biosample":   ##added "Biosample" -- Vicky-2019-7-15
        $type_name = "Biosample";
        $source = "NCBI Biosample";
        $source_database = NULL;
        break;
    case "Protocol":
        $type_name = "Protocol";
        $source = "Protocols.io";
        $source_database = NULL;
        break;
}

## added resource item to authentication report -- Vicky-2019-4-29
$rrid_type = $result->getRRIDField("type");
if($result->getRRIDField("type") == "Cell Line") $rrid_type = "cellline";
$rrid_data = Array(
      "rrid" => str_replace("RRID:", "", $result->getRRIDField("curie")),
      "type" => $rrid_type,
      "name" => $result->getRRIDField("name"),
      "subtypes" => "",
      "uid" => $id,
    );

## generated data information
$data_info = Array();
$n = 0;
$data_info['URL'] = '<a target="_blank" href="'.$result->getRRIDField("url").'">'.$result->getRRIDField("url").'</a>';
$data_info['Description'] = '<span class="truncate-long">'.$result->getRRIDField("description").'</span>';
foreach($search_manager->fields() as $field_name) {
    if (in_array($field_name->name, ["Uid", "Mentions Count"])) continue;
    if(!$result->getField($field_name->name) || !$field_name->visible("single-item") || $result->getField($field_name->name) == "CVCL:") continue;
    switch($field_name->name) {
        case "References":   ## added references link -- Vicky-2019-1-15
        case "RRIDs used":
            $data_info[$field_name->name] = '<span class="truncate-long">'.implode(", ", buildLinks($result->getField($field_name->name), $community)).'</span>';
            break;
        case "Hierarchy":   ## modified "Hierarchy" & "Originate from Same Individual" -- Vicky-2019-1-31
        case "Originate from Same Individual":
            $data_info[$field_name->name] = str_replace(":", "_", $result->getField($field_name->name));
            break;
        // case "Cross References":
        //     echo "<a target='_blank' href='https://www.ncbi.nlm.nih.gov/bioproject/".$result->getField($field_name->name)."'>".$result->getField($field_name->name)."</a>";
        //     break;
        case "Comments":
            $comment = str_replace(['<font color="#ff6347"></> ', '<font color="#000000"></> '], "", $result->getField($field_name->name));
            if (strpos(strtolower($result->getRRIDField("issues")), "problematic") !== false) {
                $comment = "<font color='red'>".$comment."</font>";
            }
            $data_info[$field_name->name] =  '<span class="truncate-long">'.$comment.'</span>';
            break;
        case "Target Antigen":
            if (trim($result->getField($field_name->name)) == ",") $data_info[$field_name->name] = "";
            else $data_info[$field_name->name] = $result->getField($field_name->name);
            break;
        case "Notes":
        case "Summary":
            $data_info[$field_name->name] = '<span class="truncate-long">'.$result->getField($field_name->name).'</span>';
            break;
        case "External URL":
            $data_info[$field_name->name] = '<a target="_blank" href="'.$result->getField($field_name->name).'">'.$result->getField($field_name->name).'</a>';
            break;
        default:
            $data_info[$field_name->name] = $result->getField($field_name->name);
    }
}

$data_order = array_keys($data_info);
$top_info_count = 7;
if(!empty($result->getSpecialField("report-data-order"))) {
    if(!empty($result->getSpecialField("report-data-order")["data_order"])) $data_order = $result->getSpecialField("report-data-order")["data_order"];
    if(!empty($result->getSpecialField("report-data-order")["top_info_count"])) $top_info_count = $result->getSpecialField("report-data-order")["top_info_count"];
}

## google scholar
$google_scholar_query = "https://scholar.google.com/scholar?hl=en&q=";
$google_scholar_keywords = ['"'.$result->getRRIDField("curie").'"'];
if($result->getRRIDField("curie") != $rrid) $google_scholar_keywords[] = '"'.$rrid.'"';
if($result->getField("Alternate IDs") != null && $result->getField("Alternate IDs") != "")
    $google_scholar_keywords = array_merge($google_scholar_keywords, explode(", ", '"'.str_replace(', ', '", "', $result->getField("Alternate IDs")).'"'));

switch ($result->getRRIDField("type")) {
    case "Cell Line":
        $google_scholar_keywords[] = '"'.$result->getField("Catalog Number").'"';
        break;
    case "antibody":
        $cat_nums_s = str_replace(" also", ",", $result->getField("Catalog Number"));
        $cat_nums_s = '"'.str_replace(', ', '", "', $cat_nums_s).'"';
        $cat_nums_s = join(" OR ", explode(", ", $cat_nums_s));
        $google_scholar_keywords[] = '["'.$result->getRRIDField("vendors-name").'" AND ('.$cat_nums_s.')]';
        break;
    case "Organism":
        $google_scholar_keywords[] = '"'.$result->getField("Catalog Number").'"';
        $google_scholar_keywords[] = '"'.$result->getField("Organism Name").'"';
        break;
    case "Plasmid":
        $catNum = str_replace("Addgene_", "", $rrid);
        $google_scholar_keywords[] = '"Addgene plasmid '.$catNum.'"';
        $google_scholar_keywords[] = '"Addgene plasmid %23'.$catNum.'"';
        $google_scholar_keywords[] = '"Addgene (plasmid '.$catNum.')"';
        $google_scholar_keywords[] = '"Addgene (plasmid %23'.$catNum.')"';
        $google_scholar_keywords[] = '"plasmid '.$catNum.'"';
        $google_scholar_keywords[] = '"plasmid %23'.$catNum.'"';
        $google_scholar_keywords[] = '"'.$result->getField("Plasmid Name").'"';
        break;
}

if($result->getRRIDField("url") != null && $result->getRRIDField("url") != "") {
    $url_string = str_replace("'", "", $result->getRRIDField("url"));
    $google_scholar_keywords[] = '"'.str_replace("http://", "", str_replace("https://", "", $url_string)).'"';
}

if($result->getRRIDField("external-url") != null && $result->getRRIDField("external-url") != "") {
    $external_url_string = str_replace("'", "", $result->getRRIDField("external-url"));
    $google_scholar_keywords[] = '"'.str_replace("http://", "", str_replace("https://", "", $external_url_string)).'"';
}

if($result->getField("Alternate URLs") != null && $result->getField("Alternate URLs") != "") {
    $alternate_urls = str_replace("'", "", $result->getField("Alternate URLs"));
    $alternate_urls = str_replace("http://", "", str_replace("https://", "", $alternate_urls));
    $alternate_urls = '"'.str_replace(', ', '", "', $alternate_urls).'"';
    $google_scholar_keywords = array_merge($google_scholar_keywords, explode(", ", $alternate_urls));
}
if($result->getField("Old URLs") != null && $result->getField("Old URLs") != "") {
    $old_urls = str_replace("'", "", $result->getField("Old URLs"));
    $old_urls = str_replace("http://", "", str_replace("https://", "", $old_urls));
    $old_urls = '"'.str_replace(', ', '", "', $old_urls).'"';
    $google_scholar_keywords = array_merge($google_scholar_keywords, explode(", ", $old_urls));
}
$google_scholar_keywords_s = join('+OR+', $google_scholar_keywords);

## google scholar keywords string length must be less than 256 characters
while(strlen($google_scholar_keywords_s) > 256) {
    array_pop($google_scholar_keywords);
    $google_scholar_keywords_s = join('+OR+', $google_scholar_keywords);
}

$google_scholar_query .= $google_scholar_keywords_s . "&btnG=";

function collaboratorNetworkFormHTML() {
    ob_start(); ?>

    <div>
        <div>
            <h4 style="color:#1c2d5c">Find mentions based on location
              <span ><img src="/images/BetaTest64x54.png" tooltip="This section is still a work in progress. We currently have good coverage for some locations such as Los Angeles, San Diego, and Boston areas, but not all areas are covered in Collaboration Network yet."/></span>
            </h4>
            <br/>
            <span class="text-danger" ng-show="ctrl2.mentions.errors.location">
                {{ ctrl2.mentions.errors.location }}
            </span>
        </div>
        <form class="form-horizontal" ng-submit="ctrl2.submitMentionFilter()">
            <!-- <div class="form-group">
                <label class="col-md-6 control-label" style="width:30%">Institution/Organization</label>
                <div class="col-md-6">
                    <input type="text"
                        autocomplete="no"
                        ng-model="ctrl2.mentions.search_filters.place_names"
                        uib-typeahead="place.key for place in ctrl2.mentions.autocompleteValues($viewValue, ctrl2.mentions.location_facets.place_names)"
                        typeahead-wait-ms="100"
                        typeahead-min-length="0"
                    />
                </div>
            </div> -->
            <div class="form-group">
                <label class="col-md-6 control-label" style="width:30%">City</label>
                <div class="col-md-6">
                    <input type="text"
                        autocomplete="no"
                        ng-model="ctrl2.mentions.search_filters.cities"
                        uib-typeahead="city.key for city in ctrl2.mentions.autocompleteValues($viewValue, ctrl2.mentions.location_facets.cities)"
                        typeahead-wait-ms="100"
                        typeahead-min-length="0"
                    />
                </div>
            </div>
            <div class="form-group">
                <label class="col-md-6 control-label" style="width:30%">State/Region/Province</label>
                <div class="col-md-6 ">
                    <input type="text"
                        autocomplete="no"
                        ng-model="ctrl2.mentions.search_filters.regions"
                        uib-typeahead="region.key for region in ctrl2.mentions.autocompleteValues($viewValue, ctrl2.mentions.location_facets.regions)"
                        typeahead-wait-ms="100"
                        typeahead-min-length="0"
                    />
                </div>
            </div>
            <div>
                <div class="form-group">
                    <div class="col-md-6  col-md-offset-5">
                        <button type="submit" class="btn btn-success">Search</button>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6">
                        <a href="javascript:void(0)" ng-click="ctrl2.submitMentionFilterLocation()"><i class='fa fa-search'></i> Search using your location</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php return ob_get_clean();
}

function collaboratorNetworkFormHTML2() {
    ob_start(); ?>

    <div>
        <div>
            <h4 style="color:#1c2d5c">Find mentions based on location
              <span ><img src="/images/BetaTest64x54.png" tooltip="This section is still a work in progress. We currently have good coverage for some locations such as Los Angeles, San Diego, and Boston areas, but not all areas are covered in Collaboration Network yet."/></span>
            </h4>
            <span class="text-danger" ng-show="ctrl2.mentions.errors.location">
                {{ ctrl2.mentions.errors.location }}
            </span>
        </div>
        <form class="form-horizontal" ng-submit="ctrl2.submitMentionFilter()">
            <div>
                <div class="row">
                    <!-- <label class="col-md-3" style="text-align: center">Institution/Organization</label> -->
                    <label class="col-md-3" style="text-align: center">City</label>
                    <label class="col-md-3" style="text-align: center">State/Region/Province</label>
                </div>
                <div class="form-group">
                    <!-- <div class="col-md-3">
                        <input type="text"
                            autocomplete="no"
                            ng-model="ctrl2.mentions.search_filters.place_names"
                            uib-typeahead="place.key for place in ctrl2.mentions.autocompleteValues($viewValue, ctrl2.mentions.location_facets.place_names)"
                            typeahead-wait-ms="100"
                            typeahead-min-length="0"
                        />
                    </div> -->
                    <div class="col-md-3">
                        <input type="text"
                            autocomplete="no"
                            ng-model="ctrl2.mentions.search_filters.cities"
                            uib-typeahead="city.key for city in ctrl2.mentions.autocompleteValues($viewValue, ctrl2.mentions.location_facets.cities)"
                            typeahead-wait-ms="100"
                            typeahead-min-length="0"
                        />
                    </div>
                    <div class="col-md-3 ">
                        <input type="text"
                            autocomplete="no"
                            ng-model="ctrl2.mentions.search_filters.regions"
                            uib-typeahead="region.key for region in ctrl2.mentions.autocompleteValues($viewValue, ctrl2.mentions.location_facets.regions)"
                            typeahead-wait-ms="100"
                            typeahead-min-length="0"
                        />
                    </div>
                    <div class="col-md-3">
                        <button type="submit" class="btn btn-success btn-sm">Search</button><br>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-12">
                        <a href="javascript:void(0)" ng-click="ctrl2.submitMentionFilterLocation()"><i class='fa fa-search'></i> Search using your location</a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    <?php return ob_get_clean();
}

?>

<script src="/js/module-search-single-item.js"></script>
<script src="/js/resolver.js"></script>
<script src="/js/module-resource.js"></script>
<script src="/js/module-resource-directives.js"></script>
<script src="/js/view-resource.js"></script>
<script src="/js/analytics-resource-comentions.js"></script>
<script src="/js/module-rrid-report-item-update.js"></script>

<style>

    .table-fixed > thead > tr > th, .table-fixed > thead > tr > td {
        min-width: 180px;
        background-color: white;
    }
    .table-fixed td {

        /* These are technically the same, but use both */
        overflow-wrap: break-word;
        word-wrap: break-word;

        word-break: break-word;

        /* Adds a hyphen where the word breaks, if supported (No Blink) */
        -ms-hyphens: auto;
        -moz-hyphens: auto;
        -webkit-hyphens: auto;
        hyphens: auto;
    }
    .grey-option {
        background-color: rgb(149, 165, 166);
    }
</style>

<?php
$rows = Array();
$row = Array();
$cell = Array();
ob_start();
?>
<div class="row">
    <?php if ($cm_rrid): ?>
        <div class="col-md-6">
            <span class="fa-stack fa-md">
                <i class="fa fa-circle fa-stack-2x" style="color:#FBBD1A"></i>
                <i class="fa fa-flask fa-stack-1x fa-inverse"></i>
            </span>
            <?php echo $type_name ?> Name <span class="help-tooltip" data-name="resource-report-name.html"></span>
        </div>
        <div class="col-md-6">
            <span class="fa-stack fa-md">
              <i class="fa fa-circle fa-stack-2x" style="color:#1C2D5C"></i>
              <i class="fa fa-comment fa-stack-1x fa-inverse"></i>
            </span>
            Co-mention Resource Name
        </div>
    <?php elseif (count($multi_vendors) > 1 && $page_type == "info"): ?>
        <div class="col-md-3">
            <span class="fa-stack fa-md">
                <i class="fa fa-circle fa-stack-2x" style="color:#FBBD1A"></i>
                <i class="fa fa-flask fa-stack-1x fa-inverse"></i>
            </span>
            <?php echo $type_name ?> Name <span class="help-tooltip" data-name="resource-report-name.html"></span>
        </div>
        <div class="col-md-9" style="text-align: right;font-size: 16px">
            <form action="<?php echo $community->fullURL() ?>/resolver/<?php echo $rrid ?>" method="get">
                <p><span style="color: red">*</span><b>NOTICE:</b> Multiple vendors found, please select your record:
                <select name="i" onchange="this.form.submit();">
                    <?php foreach($multi_vendors as $vendor): ?>
                        <?php if ($vendor["id"] == $id): ?>
                            <option value="<?php echo $vendor["id"]?>" selected="selected">
                        <?php else: ?>
                            <option value="<?php echo $vendor["id"]?>">
                        <?php endif ?>
                        <?php echo $vendor["value"] ?></option>
                    <?php endforeach ?>
                </select></p>
            </form>
        </div>
    <?php else: ?>
      <div class="col-md-12">
          <span class="fa-stack fa-md">
              <i class="fa fa-circle fa-stack-2x" style="color:#FBBD1A"></i>
              <i class="fa fa-flask fa-stack-1x fa-inverse"></i>
          </span>
          <?php echo $type_name ?> Name <span class="help-tooltip" data-name="resource-report-name.html"></span>
      </div>
    <?php endif ?>
</div>
<?php $cell["title"] = ob_get_clean(); ?>
<?php ob_start(); ?>
    <div class="row">
        <?php if ($page_type != "co-mentions"): ?>
            <div class="rrid-name col-md-12">
        <?php else: ?>
            <div class="rrid-name col-md-6">
        <?php endif ?>
                <a target="_self" href="
                <?php if($sci_flag): ?>
                    <?php echo '/resolver/'.$result->getRRIDField('curie').'?q='.$_GET['q'].'&i='.$id ?>
                <?php else: ?>
                    <?php echo $community->fullURL() ?>/data/record/<?php echo $view.'/'.$result->getRRIDField('curie') ?>/resolver?q=<?php echo $_GET['q'] ?>&i=<?php echo $id ?>
                <?php endif ?>
                " style="color:#1C2D5C"><?php echo $result->getRRIDField("name") ?></a>
            <?php if($result->getRRIDField("url")): ?>
                <a target="_blank" href="<?php echo $result->getRRIDField('url') ?>"><i class='fa fa-external-link'></i></a>
            <?php endif ?>
            <?php if($dknet_flag): ?>
                <?php echo \helper\htmlElement("collection-bookmark", Array("user" => $_SESSION['user'], "uuid" => $result->getRRIDField("uuid"), "community" => $community, "view" => $view, "rrid-data" => $rrid_data)); ?>
            <?php endif ?>
            </div>
        <?php if ($cm_name && $page_type == "co-mentions"): ?>
            <div class="rrid-name col-md-6" style="padding-left: 40px">
                <?php if($sci_flag): ?>
                    <a target="_blank" href="/resolver/<?php echo $cm_rrid ?>" style="color:#1C2D5C"><?php echo $cm_name ?></a>
                <?php else: ?>
                    <a target="_blank" href="<?php echo $community->fullURL() ?>/data/record/<?php echo $cm_view.'/'.$cm_rrid ?>/resolver" style="color:#1C2D5C"><?php echo $cm_name ?></a>
                <?php endif ?>
            </div>
        <?php endif ?>
    </div>
    <div class="row">
        <?php if ($cm_rrid): ?>
            <div class="col-md-6">
                <div class="rrid">
                    <?php echo $result->getRRIDField("curie") ?>
                    <a tooltip="Copy the RRID" href="javascript:void(0)" ng-click="ctrl.copyRRID(0)"><i class="fa fa-clipboard"></i></a>
                    <span class="copy-alert" ng-show="ctrl.alerts.copy_rrid[0]">RRID Copied</span>
                </div>
            </div>
            <div class="col-md-6" style="padding-left: 40px">
                <div class="rrid">
                  <?php if ($cm_rrid): ?>
                        <?php echo $cm_rrid ?>
                        <a tooltip="Copy the RRID" href="javascript:void(0)" ng-click="ctrl.copyRRID(1)"><i class="fa fa-clipboard"></i></a>
                        <span class="copy-alert" ng-show="ctrl.alerts.copy_rrid[1]">RRID Copied</span>
                  <?php endif ?>
                </div>
            </div>
        <?php else: ?>
            <div class="col-md-12">
                <div class="rrid">
                    <?php echo $result->getRRIDField("curie") ?>
                    <a tooltip="Copy the RRID" href="javascript:void(0)" ng-click="ctrl.copyRRID(0)"><i class="fa fa-clipboard"></i></a>
                    <span class="copy-alert" ng-show="ctrl.alerts.copy_rrid[0]">RRID Copied</span>
                    <?php if ($result->getRRIDField("type") == "tool" && $page_type == "info"):   ## added 'edit' function for curator & administrator -- Vicky-2019-3-4 ?>
                        <?php if (!$_SESSION["user"]): ?>
                            <a href="javascript:void(0)" class="btn btn-primary btn-login">Login to claim ownership</a>
                        <?php endif ?>
                        <?php if ($_SESSION["user"]->role > 0): ?>
                            &nbsp;<a target="_blank" href="https://scicrunch.org/browse/resourcesedit/<?php echo str_replace("RRID:", "", $result->getRRIDField("curie")) ?>"><i class="glyphicon glyphicon-pencil" tooltip="Edit this resource"></i></a>
                        <?php else: ?>
                            <span ng-show="is_owner">&nbsp;<a target="_blank" href="https://scicrunch.org/browse/resourcesedit/<?php echo str_replace("RRID:", "", $result->getRRIDField("curie")) ?>"><i class="glyphicon glyphicon-pencil" tooltip="Edit this resource"></i></a></span>
                        <?php endif ?>
                            &nbsp;<span claim-resource-ownership-dir></span claim-resource-ownership-dir>
                        <!-- &nbsp;<a ng-controller="altIDs as ai" ng-show="is_curator" class="fa fa-exchange" uib-popover-template="ai.dynamicPopover.templateUrl" popover-placement="right" href="javascript:void(0)"></a> -->
                        <!-- &nbsp;<span ng-controller="resourceMentions as rm" resource-mention-user-subscription-dir></span resource-mention-user-subscription-dir> -->
                    <?php endif ?>
                    &nbsp;<font size=4px><?php //echo \helper\htmlElement("authentication-bookmark", Array("user" => $_SESSION["user"], "uuid" => $result->getRRIDField("uuid"), "community" => $community, "view" => $view, "rrid-data" => $rrid_data)); ## added resource item to authentication report -- Vicky-2019-4-29 ?></font>
                </div>
            </div>
        <?php endif ?>
    </div>
    <div class="row">
        <div class="col-md-6">
            <?php if($dknet_flag): ?>
                <span style="font-size:20px">
                    <?php echo \helper\htmlElement("authentication-bookmark", Array("user" => $_SESSION['user'], "uuid" => $result->getRRIDField("uuid"), "community" => $community, "view" => $view, "rrid-data" => $rrid_data)); ?>
                    <?php if(in_array($view, ["SCR_013869-1", "nif-0000-07730-1"])): ?>
                        <span class="help-tooltip" data-name="authentication-report.html"></span>
                    <?php endif ?>
                </span>
            <?php endif ?>
        </div>
        <div class="col-md-6">
            <div class="pull-right">
                <span class="citation-wrapper">
                    <?php if($page_type == "info"): ?>
                        <a class="top-link" target="_blank" href="<?php echo $community->fullURL() ?>/data/record/<?php echo $view ?>/<?php echo str_replace('/', '$U+002F;', $rrid) ?>/resolver/pdf&i=<?php echo $id ?>">PDF Report</a>
                        <a class="top-link" href="javascript:void(0)" ng-click="ctrl.toggleCitation()">
                            How to cite
                        </a>
                    <?php elseif(in_array($page_type, ["mentions", "co-mentions"])): ?>
                        <a class="top-link" tooltip="Download the most recent 1,000 mentions" target="_self" href="/php/download-rin-rrid-mentions.php?viewid=<?php echo $view ?>&rrid=<?php echo $rrids ?>&cn=<?php echo $cm_name ?>&year={{ ctrl2.mentions.search_filters.publicationYear }}&place={{ ctrl2.mentions.search_filters.place_names }}&city={{ ctrl2.mentions.search_filters.cities }}&region={{ ctrl2.mentions.search_filters.regions }}&mode={{ ctrl2.mentions.mode }}&tab=<?php echo $page_type ?>">Download Mentions</a>
                    <?php endif ?>
                    <div ng-show="ctrl.show_citation" class="citation">
                        <pre style="display:inline-block"><?php echo $result->getRRIDField("proper-citation") ?></pre>
                        <a href="javascript:void(0)" ng-click="ctrl.copyCitation()">
                            <i class="fa fa-clipboard"></i>
                            Copy
                        </a>
                        <span class="copy-alert" ng-show="ctrl.alerts.copy_citation">Citation Copied</span>
                    </div>
                </span>
                <!--<span class="citation-wrapper">
                    <a class="top-link" href="javascript:void(0)" ng-click="ctrl.toggleFollow()">
                        Follow this resource
                    </a>
                    <div ng-show="ctrl.show_follow" class="citation">
                        Coming soon...
                    </div>
                </span>-->
            </div>
        </div>
    </div>
    <?php if($sci_flag && $page_type == "info"): ?>
        <div class="row">
            <div class="col-md-12">
                <?php if($_SESSION["resolver_alternated"][0]): ?>
                    <p><i class="text-danger fa fa-exclamation-triangle" style="color: orange"></i></b> the RRID (<b><?php echo $_SESSION["resolver_alternated"][1] ?></b>) is not the primary RRID. It is an alternate RRID or alternate ID.</p>
                    <?php unset($_SESSION["resolver_alternated"]); ?>
                <?php endif ?>
            </div>
        </div>
    <?php endif ?>
<?php
$cell["body"] = Array(Array("html" => ob_get_clean()));
$row[] = $cell;
$rows[] = $row;
?>

<?php if($page_type == "info"): ?>
    <!-- information -->
    <?php
        $row = Array();
        $cell = Array();
        ob_start();
    ?>
        <span class="fa-stack fa-md">
            <i class="fa fa-circle fa-stack-2x" style="color:#F57E29"></i>
            <i class="fa fa-info fa-stack-1x fa-inverse"></i>
        </span>
        <?php echo $type_name ?> Information <span class="help-tooltip" data-name="resource-report-information.html"></span>
    <?php $cell["title"] = ob_get_clean(); ?>
        <?php ob_start(); ?>
        <div>
            <?php foreach($data_order as $data_name): ?>
                <?php $n += 1; ?>
                <?php if($data_info[$data_name] != ""): ?>
                    <p>
                        <strong><?php echo $data_name ?>:</strong>
                        <?php echo $data_info[$data_name] ?>
                    </p>
              <?php endif ?>
                <?php if($n == $top_info_count): ?>
                    </div>
                    <a id="data_expand" style="display:block" onclick="return showHideData()">Expand All</a>
                    <div id="data_info" style="display:none">
                <?php endif ?>
            <?php endforeach ?>
        </div>
        <a id="data_collapse" style="display:none" onclick="return showHideData()">Collapse</a>
    <?php
        $cell["body"] = Array(Array("html" => ob_get_clean()));
        $row[] = $cell;
        $rows[] = $row;
    ?>
    <!-- /information -->

    <!-- usage and citation metrics -->
    <?php
        $row = Array();
        $cell = Array();
        ob_start();
    ?>
        <span class="fa-stack fa-md">
            <i class="fa fa-circle fa-stack-2x" style="color:#666767"></i>
            <i class="fa fa-line-chart fa-stack-1x fa-inverse"></i>
        </span>
        Usage and Citation Metrics <span class="help-tooltip" data-name="resource-report-usage-and-citation-metrics.html"></span>
    <?php $cell["title"] = ob_get_clean(); ?>
    <?php ob_start(); ?>
    <?php if($protocol_flag): ?>
        <p>Coming soon.</p>
    <?php else: ?>
        <div ng-show="ctrl2.mentions.total_count > 0">
            <p>
                <strong>
                    We found {{ ctrl2.mentions.total_count }} mentions in open access literature.
                </strong><br/>
                <a target="_self" href="
                <?php if($sci_flag): ?>
                  /resolver/<?php echo $rrid ?>/mentions?q=<?php echo $_GET['q'] ?>&i=<?php echo $id ?>
                <?php else: ?>
                  <?php echo $community->fullURL() ?>/data/record/<?php echo $view ?>/<?php echo $rrid ?>/resolver/mentions?q=<?php echo $_GET['q'] ?>&i=<?php echo $id ?>
                <?php endif ?>
                " tooltip="See all the usage and citation metrics for this resource">View full usage report</a><br/>
            </p>
            <p><strong>Most recent articles:</strong></p>
            <?php ## modified mentions information -- Vicky-2019-1-17 ?>
            <p ng-repeat="mention in ctrl2.mentions.mentions">
                <span ng-show="mention._source.dc.creators.length > 0">{{ mention._source.dc.creators[0].familyName }} {{ mention._source.dc.creators[0].initials }}</span><span ng-show="mention._source.dc.creators.length > 1">, et al</span>.
                ({{ mention._source.dc.publicationYear }})
                {{ mention._source.dc.title }}
                {{ mention._source.dc.publishers[0].name }}<span ng-show="mention._source.dc.publishers[0].volume.length > 0">, {{ mention._source.dc.publishers[0].volume }}</span><span ng-show="mention._source.dc.publishers[0].issue.length > 0">({{ mention._source.dc.publishers[0].issue }})</span><span ng-show="mention._source.dc.publishers[0].pagination.length > 0">, {{ mention._source.dc.publishers[0].pagination }}</span>.
                <!--(<a target="_blank" ng-href="<?php echo $community->fullURL() ?>/{{ mention._id.replace('PMID:', '') }}">Link</a>)-->
                (<a target="_blank" ng-href="/<?php echo $community->portalName ?>/{{ mention._id.replace('PMID:', '') }}?rpKey=on">PMID:{{ mention._id.replace('PMID:', '') }}</a>)  <!--open link in new tab - Vicky-2018-12-2-->
            </p>
        </div>
        <div ng-show="ctrl2.mentions.total_count == 0">
            <p>
                We have not found any literature mentions for this resource.
            </p>
        </div>
        <div ng-show="ctrl2.mentions.total_count == -1">
            <p>
                We are searching literature mentions for this resource.
            </p>
        </div>
    <?php endif ?>
        <p>
            <strong>Check<a target='_blank' href='<?php echo $google_scholar_query ?>'><img src="https://scicrunch.org/upload/community-components/google-scholar-logo_73278a4a86960eeb.png" style="width: 150px;"></a>for all resource mentions.</strong>
        </p>
    <?php
        $cell["body"] = Array(Array("html" => ob_get_clean()));
        $row[] = $cell;
    ?>
    <!-- /usage and citation metrics -->

    <!-- collaborator network -->
    <?php
        $cell = Array();
        ob_start();
    ?>
        <span class="fa-stack fa-md">
            <i class="fa fa-circle fa-stack-2x" style="color:#1C2D5C"></i>
            <i class="fa fa-user fa-stack-1x fa-inverse"></i>
        </span>
        Collaborator Network <span class="help-tooltip" data-name="resource-report-collaborator-network.html"></span>
        <!-- <div class="tooltip-grey">
          <button type="button" class="btn color-white background-orange"> Coming soon! </button>
          <span class="tooltiptext"><?php echo $coming_soon_des ?></span>
        </div> -->

    <?php $cell["title"] = ob_get_clean(); ?>
    <?php ob_start(); ?>
    <?php if($protocol_flag): ?>
        <p>Coming soon.</p>
    <?php else: ?>
        <div ng-show="ctrl2.mentions.total_count > 0">
            <p>A list of researchers who have used the resource and an author search tool</p>
            <?php echo collaboratorNetworkFormHTML() ?>
        </div>
        <div ng-hide="ctrl2.mentions.total_count > 0">
            <p>A list of researchers who have used the resource and an author search tool.  This is available for resources that have literature mentions.</p>
        </div>
    <?php endif ?>
    <?php
        $cell["body"] = Array(Array("html" => ob_get_clean()));
        $row[] = $cell;
        $rows[] = $row;
    ?>
    <!-- /collaborator network -->

    <!-- ratings and alerts -->
    <?php
        $row = Array();
        $cell = Array();
        ob_start();
    ?>
    <?php if(!$protocol_flag): ?>
        <span class="fa-stack fa-md">
            <i class="fa fa-circle fa-stack-2x" style="color:#408DC9"></i>
            <i class="fa fa-comment fa-stack-1x fa-inverse"></i>
        </span>
        Ratings and Alerts <span class="help-tooltip" data-name="resource-report-ratings-and-alerts.html"></span>

    <?php $cell["title"] = ob_get_clean(); ?>
    <?php ob_start(); ?>
        <p>
            <?php if(!empty($result->getSpecialField("ratings"))): ?>
                <ul>
                    <?php foreach($result->getSpecialField("ratings") as $rating): ?>
                        <li>
                            <?php if(isset($rating["score"])): ?>
                                <?php echo $rating["score"] ?>
                                <?php if(isset($rating["out-of"])): ?>
                                    / <?php echo $rating["out-of"] ?>
                                <?php endif ?>
                                <?php if(isset($rating["count"])): ?>
                                    (<?php echo $rating["count"] ?> votes)
                                <?php endif ?>
                            <?php endif ?>
                            <?php if($rating["text"]): ?>
                                <?php echo $rating["text"] ?>
                            <?php endif ?>
                            <?php if($rating["url"]): ?>
                                <a target="_blank" href="<?php echo $rating["url"] ?>"><?php echo $rating["url"] ?></a>
                            <?php endif ?>
                        </li>
                    <?php endforeach ?>
                </ul>
            <?php else: ?>
                No rating or validation information has been found for <?php echo $result->getRRIDField("name") ?>.
            <?php endif ?>
        </p>
        <?php if(!empty($result->getSpecialField("alerts"))): ?>
            <p>
                <?php
                    $alert_warning = Array();
                    foreach($result->getSpecialField("alerts") as $alert) {
                        if($alert["type"] == "warning") $alert_warning[] = $alert["icon"]." ".$alert["text"];
                    }
                    echo join("&nbsp;&nbsp;", $alert_warning);
                    echo "<br>".$comment."<br>"
                ?>
            </p>
        <?php else: ?>
            <p>No alerts have been found for <?php echo $result->getRRIDField("name") ?>.</p>
        <?php endif ?>
    <?php endif ?>
    <?php
        $cell["body"] = Array(Array("html" => ob_get_clean()));
        $row[] = $cell;
    ?>
    <!-- /ratings and alerts -->

    <!-- data and source information -->
    <?php
        $cell = Array();
        ob_start();
    ?>
        <span class="fa-stack fa-md">
            <i class="fa fa-circle fa-stack-2x" style="color:#75BD43"></i>
            <i class="fa fa-database fa-stack-1x fa-inverse"></i>
        </span>
        Data and Source Information <span class="help-tooltip" data-name="resource-report-data-and-source.html"></span>
    <?php $cell["title"] = ob_get_clean(); ?>
    <?php ob_start(); ?>
        <?php if(!is_null($item_views)): ?>
            <p>
                <strong>Data:</strong>
                <?php echo implode(", ", array_map(function($x) use($community) {
                    return '<a target="_self" href="' . $community->fullURL() . '/data/source/' . $x->nif . '/search">' . $x->view . '</a>';
                }, $item_views["views"])) ?>
            </p>
        <?php endif ?>
        <p>
            <strong>Source:</strong>
            <?php if($protocol_flag): ?>
                <a href="https://www.protocols.io/">
            <?php else: ?>
                <a href="<?php echo $community->fullURL() ?>/about/sources/<?php echo $view ?>">
            <?php endif ?>
                <?php echo $source ?>
            </a>
        </p>
        <!--## add source database information in Organism report - Vicky-2018-11-21-->
        <?php if($source_database): ?>
            <p>
                <strong>Source Database:</strong>
                <?php echo $source_database; ?>
            </p>
        <?php endif ?>
    <?php
        $cell["body"] = Array(Array("html" => ob_get_clean()));
        $row[] = $cell;
        $rows[] = $row;
    ?>
    <!-- /data and source information -->

    <!-- Authentication Plan -->
    <?php if(($type_name == "Cell Line" || $type_name == "Antibody") && $dknet_flag): ?>
        <?php
            $row = Array();
            $cell = Array();
            ob_start();
        ?>
        <span class="fa-stack fa-md">
            <i class="fa fa-circle fa-stack-2x" style="color:#F57F29"></i>
            <i class="fa fa-gears fa-stack-1x fa-inverse"></i>
        </span>
            Authentication Plan
        <?php $cell["title"] = ob_get_clean(); ?>
        <?php ob_start(); ?>
            <?php if($type_name == "Cell Line"): ?>
                <div>
                    <p>dkNET can assist you in preparing authentication plans for cell lines to <a target="_blank" href="https://dknet.org/about/NIH-Policy-Rigor-Reproducibility">comply with the NIH Submission Policy</a>. The information can be used while planning your experiments or submitting grant applications. The authentication plan for the cell lines is based on the International Cell Line Authentication Committee (ICLAC)'s "<a target="_blank" href="https://iclac.org/resources/cell-line-checklist/">Cell Line Checklist for Manuscripts and Grant Applications</a>" and the example "<a target="_blank" href="https://library.ucsd.edu/research-and-collections/data-curation/_files/ExampleAuthenticationKeyBiologicalChemicalResources201609b.pdf">Authentication of of Key Biological and/or Chemical Resources</a> "(Bandrowski A). For best practices for authenticating cell lines, check our <a target="_blank" href="https://dknet.org/rin/rrid-report">Authentication Reports</a> services.</p>
                </div>
            <?php elseif($type_name == "Antibody"): ?>
                <div>
                    <p>dkNET can assist you in preparing authentication plans for antibodies to <a target="_blank" href="https://dknet.org/about/NIH-Policy-Rigor-Reproducibility">comply with the NIH Submission Policy</a>. The information can be used while planning your experiments or submitting grant applications. The authentication plan for antibodies is based on methods suggested in "<a target="_blank" href="https://www.ncbi.nlm.nih.gov/pubmed/27595404">A proposal for validation of antibodies</a>" (Uhlen M et. al., 2016), <a target="_blank" href="https://onlinelibrary.wiley.com/doi/full/10.1002/cne.20839">the guideline published in the Journal of Comparative Neurology</a> (Saper C, 2005)(2), and the example " <a target="_blank" href="https://library.ucsd.edu/research-and-collections/data-curation/_files/ExampleAuthenticationKeyBiologicalChemicalResources201609b.pdf">Authentication of of Key Biological and/or Chemical Resources</a> "(Bandrowski A). For best practices for authenticating antibodies, check our <a target="_blank" href="https://dknet.org/rin/rrid-report">Authentication Reports</a> services.</p>
                </div>
            <?php endif ?>
        <?php
            $cell["body"] = Array(Array("html" => ob_get_clean()));
            $row[] = $cell;
            $rows[] = $row;
        ?>
    <?php endif ?>
    <!-- /Authentication Plan -->

    <!-- Organization Overview -->
    <?php if(($type_name == "Resource") && $dknet_flag && (count($parents) != 0 || count($children) != 0)): ?>
        <?php
            $row = Array();
            $cell = Array();
            ob_start();
        ?>
        <div class="row">
            <div class="col-md-12">
                <span class="fa-stack fa-md">
                    <i class="fa fa-circle fa-stack-2x" style="color:green"></i>
                    <i class="fa fa fa-sitemap fa-stack-1x fa-inverse"></i>
                </span>
                Organization Overview
            </div>
        </div>
        <?php $cell["title"] = ob_get_clean(); ?>
        <?php ob_start(); ?>
            <?php if(count($parents) != 0): ?>
                <div>
                    <p><?php echo $result->getRRIDField("name") ?> is part of the following organization(s):</p>
                    <?php foreach ($parents as $parent_rrid => $parent_name): ?>
                        <ul>
                            <li><a target="_blank" href="<?php echo $community->fullURL() ?>/data/record/<?php echo $view ?>/<?php echo $parent_rrid ?>/resolver"><?php echo $parent_name ?> (RRID:<?php echo $parent_rrid ?>)</a></li>
                    <?php endforeach ?>
                        <ul><li><b><?php echo $result->getRRIDField("name") ?></b></li></ul>
                    <?php foreach ($parents as $parent): ?>
                        </ul>
                    <?php endforeach ?>
                </div>
            <?php endif ?>
            <?php if(count($children) != 0): ?>
                <div>
                    <p>
                        <?php echo $result->getRRIDField("name") ?> has <?php echo count($children) ?> child organization(s)
                        <?php if($grand_children_count > 0): ?>
                            and <?php echo $grand_children_count ?> grandchild organization(s)
                        <?php endif ?>
                        and a total of {{ organization_mentions_total_count }} mentions.
                        <span class="help-tooltip" data-name="mention-definition.html"></span>
                        <span ng-show="organization_mentions_total_count > 0">
                            <a target="_blank" href="<?php echo $community->fullURL() ?>/data/record/<?php echo $view ?>/<?php echo str_replace("RRID:", "", $result->getRRIDField("curie")) ?>/resolver/organizations">[View Organizational Usage Report]</a>
                        </span>
                    </p>
                    <ul>
                        <?php foreach ($children as $child): ?>
                            <li><a target="_blank" href="<?php echo $community->fullURL() ?>/data/record/<?php echo $view ?>/<?php echo str_replace("RRID:", "", $child["rrid"]) ?>/resolver"><?php echo $child["name"] ?> (<?php echo $child["rrid"] ?>)</a></li>
                            <?php if(count($child["grand_children"] > 0)): ?>
                                <ul>
                                    <?php foreach ($child["grand_children"] as $grand_child): ?>
                                        <li><a target="_blank" href="<?php echo $community->fullURL() ?>/data/record/<?php echo $view ?>/<?php echo str_replace("RRID:", "", $grand_child["rrid"]) ?>/resolver"><?php echo $grand_child["name"] ?> (<?php echo $grand_child["rrid"] ?>)</a></li>
                                    <?php endforeach ?>
                                </ul>
                            <?php endif ?>
                        <?php endforeach ?>
                    </ul>
                </div>
            <?php endif ?>
        <?php
            $cell["body"] = Array(Array("html" => ob_get_clean()));
            $row[] = $cell;
            $rows[] = $row;
        ?>
    <?php endif ?>
    <!-- /Organization Overview -->
<?php elseif($page_type == "organizations"): ?>
    <!-- Organizational Usage Report Overview -->
    <?php
        $row = Array();
        $cell = Array();
        ob_start();
    ?>
    <span class="fa-stack fa-md">
        <i class="fa fa-circle fa-stack-2x" style="color:green"></i>
        <i class="fa fa fa-sitemap fa-stack-1x fa-inverse"></i>
    </span>
        Organizational Usage Report Overview
    <?php $cell["title"] = ob_get_clean(); ?>
    <?php ob_start(); ?>
        <p>
            Total Mentions for <b><?php echo $result->getRRIDField("name") ?></b> and its child organization(s) and grandchild organization(s): {{ organization_mentions_total_count }}
            <?php if($total_children_mentions_count + $result->getRRIDField("mentionCount") > 0): ?>
                &nbsp;&nbsp;<a target="_self" href="<?php echo $community->fullURL() ?>/data/record/<?php echo $view ?>/<?php echo $rrid ?>/resolver/organization-mentions?i=<?php echo $result->getRRIDField("id") ?>">[Full list]</a>
            <?php endif ?>
        </p>
        <table class="table table-bordered table-striped table-fixed" style="table-layout:fixed;">
            <thead>
                <tr>
                    <th width="60%">Organization Name</th>
                    <th width="20%">RRID</th>
                    <th width="20%">Number of Organization Mentions <span class="help-tooltip" data-name="mention-definition.html"></span></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="background-color:hsl(95,50%,50%)"><b><?php echo $result->getRRIDField("name") ?> (Parent)</b></td>
                    <td style="background-color:hsl(95,50%,50%)"><b><?php echo $result->getRRIDField("curie") ?></b></td>
                    <td style="background-color:hsl(95,50%,50%)"><b><?php echo $result->getRRIDField("mentionCount") ?>
                        <?php if($result->getRRIDField("mentionCount") > 0): ?>
                            &nbsp;&nbsp;<a target="_self" href="<?php echo $community->fullURL() ?>/data/record/<?php echo $view ?>/<?php echo str_replace("RRID:", "", $result->getRRIDField("curie")) ?>/resolver/mentions?i=<?php echo $result->getRRIDField("id") ?>">[Full list]</a>
                        <?php endif ?>
                    </b></td>
                </tr>
                <?php foreach ($children as $child): ?>
                    <tr>
                        <td style="background-color:hsl(95,50%,80%)">&nbsp;&nbsp;--&nbsp;<a target="_blank" href="<?php echo $community->fullURL() ?>/data/record/<?php echo $view ?>/<?php echo str_replace("RRID:", "", $child["rrid"]) ?>/resolver"><?php echo $child["name"] ?> (Child)</a></td>
                        <td style="background-color:hsl(95,50%,80%)"><?php echo $child["rrid"] ?></td>
                        <td style="background-color:hsl(95,50%,80%)"><?php echo $child["total_mentions_count"] ?>
                            <?php if($child["total_mentions_count"] > 0): ?>
                                &nbsp;&nbsp;<a target="_blank" href="<?php echo $community->fullURL() ?>/data/record/<?php echo $view ?>/<?php echo str_replace("RRID:", "", $child["rrid"]) ?>/resolver/mentions">[Full list]</a>
                            <?php endif ?>
                        </td>
                    </tr>
                    <?php if(count($child["grand_children"]) > 0): ?>
                        <?php foreach ($child["grand_children"] as $grand_child): ?>
                            <tr>
                                <td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;--&nbsp;<a target="_blank" href="<?php echo $community->fullURL() ?>/data/record/<?php echo $view ?>/<?php echo str_replace("RRID:", "", $grand_child["rrid"]) ?>/resolver"><?php echo $grand_child["name"] ?></a></td>
                                <td><?php echo $grand_child["rrid"] ?></td>
                                <td><?php echo $grand_child["total_mentions_count"] ?>
                                    <?php if($grand_child["total_mentions_count"] > 0): ?>
                                        &nbsp;&nbsp;<a target="_blank" href="<?php echo $community->fullURL() ?>/data/record/<?php echo $view ?>/<?php echo str_replace("RRID:", "", $grand_child["rrid"]) ?>/resolver/mentions">[Full list]</a>
                                    <?php endif ?>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    <?php endif ?>
                <?php endforeach ?>
            </tbody>
        </table>
    <?php
        $cell["body"] = Array(Array("html" => ob_get_clean()));
        $row[] = $cell;
        $rows[] = $row;
    ?>
    <!-- /Organizational Usage Report Overview -->
<?php else: ?>
<?php if(in_array($page_type, ["mentions", "co-mentions"])): ?>
    <!-- usage -->
    <?php
        $cell = Array();
        $row = Array();
        ob_start();
    ?>
        <span class="fa-stack fa-md">
            <i class="fa fa-circle fa-stack-2x" style="color:#76BD43"></i>
            <i class="fa fa-line-chart fa-stack-1x fa-inverse"></i>
        </span>
        <?php if ($cm_rrid): ?>
            Usage <span class="help-tooltip" data-name="resource-report-co-mentions-usage.html"></span>
        <?php else: ?>
            Usage <span class="help-tooltip" data-name="resource-report-mentions-usage.html"></span>
        <?php endif ?>
    <?php $cell["title"] = ob_get_clean(); ?>
    <?php ob_start(); ?>
        <div id="rrid-report-mentions-graph"></div>
        <div ng-show="ctrl2.mentions.mentions.length > 0">
            * Resource mentions from this and last year may still be in process.
        </div>
    <?php
        $cell["body"] = Array(Array("html" => ob_get_clean()));
        $row[] = $cell;
    ?>
    <!-- /usage -->

    <?php
        $cell = Array();
        ob_start();
    ?>
    <!-- welcome -->
    <span class="fa-stack fa-md">
        <i class="fa fa-circle fa-stack-2x" style="color:#F57F29"></i>
        <i class="fa fa-star fa-stack-1x fa-inverse"></i>
    </span>
    About this Report
    <?php $cell["title"] = ob_get_clean(); ?>
    <?php ob_start(); ?>
        <p>
            The report results are from <strong>Research Resource Identifier (RRID)</strong> - based text mining of the literature of <a target="_blank" href="https://www.ncbi.nlm.nih.gov/pmc/tools/openftlist/">PubMed Central (PMC) Open Access Subset (OA Subset)</a>.
        </p>
        <p>
            For tools, we also include additional results from searching for resource name and URL in the <a target="_blank" href="https://www.ncbi.nlm.nih.gov/pmc/tools/openftlist/">PMC OA Subset</a>. See Ozyurt, et. al., <a target="_self" href="<?php echo $community->fullURL() ?>/26730820?rpKey=on">PMID:26730820</a>
        </p>

        <?php if($result->getRRIDField("type") == "resource"): ?>
            <p>
                What's the difference between resource mentions and RRID mentions?
            </p>
            <p>
                <strong>Resource mentions</strong> are found through text mining open access literature for mentions of the resource's name or URL.
            </p>
        <?php endif ?>
        <p>
            <strong>Check<a target='_blank' href='<?php echo $google_scholar_query ?>'><img src="https://scicrunch.org/upload/community-components/google-scholar-logo_73278a4a86960eeb.png" style="width: 150px;"></a>for all resource mentions.</strong>
        </p>
        <hr style="border-color: #1C2D5C; border-width: 2px;" noshade>
    <!-- /welcome -->

  <?php if($page_type == "co-mentions"): ?>
      <h4 style="color:#1c2d5c">Co-Mentions Summary</h4>
      <span ng-show="ctrl2.mentions.mode == 'all'">
          <p><?php echo $result->getRRIDField("name") ?> (RRID:<?php echo $rrid ?>) total mentions count: <span ng-show="ctrl2.mentions.rrid1_total_count > -1">{{ ctrl2.mentions.rrid1_total_count }}</span></p>
          <p><?php echo $cm_name ?> (<?php echo $cm_rrid ?>) total mentions count: <span ng-show="ctrl2.mentions.rrid2_total_count > -1">{{ ctrl2.mentions.rrid2_total_count }}</span></p>
          <p>Overlapping total mentions count: <span ng-show="ctrl2.mentions.total_count > -1">{{ ctrl2.mentions.total_count }}</span></p>
      </span>
      <span ng-show="ctrl2.mentions.mode == 'rrid'">
          <p><?php echo $result->getRRIDField("name") ?> (RRID:<?php echo $rrid ?>) RRID mentions count: <span ng-show="ctrl2.mentions.rrid1_total_count > -1">{{ ctrl2.mentions.rrid1_total_count }}</span></p>
          <p><?php echo $cm_name ?> (<?php echo $cm_rrid ?>) RRID mentions count: <span ng-show="ctrl2.mentions.rrid2_total_count > -1">{{ ctrl2.mentions.rrid2_total_count }}</span></p>
          <p>Overlapping RRID mentions count: <span ng-show="ctrl2.mentions.total_count > -1">{{ ctrl2.mentions.total_count }}</span></p>
      </span>
  <?php endif ?>


    <!-- Other research resources frequently mentioned with this resource -->
        <div ng-show="ctrl2.comentions.length > 0">
            <h4 style="color:#1c2d5c">Other research resources frequently mentioned with this resource <span class="help-tooltip" data-name="resource-report-mentions-comentions.html"></span></h4>
            <p>*Please note that when co-mention number is small, resources listed here do not mean that they are frequently used together. We are also aware that commercial organizations are in the list and we are currently working on improving this service by removing these organizations. </p>
            <?php if($sci_flag): ?>
                <ul ng-show="ctrl2.mentions.mode == 'all'">
                    <li ng-repeat="cm in ctrl2.comentions" style="font-size: 14px">
                        <a target="_blank" ng-href="/resolver/{{ cm.rrid }}"><span ng-bind-html="cm.name"></span></a>
                        <a target="_blank" ng-href="/resolver/<?php echo $rrid ?>/co-mentions?q=<?php echo $_GET['q'] ?>&cm={{ cm.rrid }}&cn={{ cm.name }}&cv={{ cm.viewid }}&i=<?php echo $id ?>" title="Click bar to show co-mentions detail" style="text-decoration: none"><span ng-show="cm.rrid_count > 0 || cm.resource_count > 0"><div style="width: {{(cm.rrid_count + cm.resource_count) / (ctrl2.comentions[0].rrid_count + ctrl2.comentions[0].resource_count) * 80}}%; background-color: #408dc9; height:10px">&nbsp</div></span></a>
                        <!-- <span ng-show="!$last">, </span> -->
                    </li>
                </ul>
                <ul ng-show="ctrl2.mentions.mode == 'rrid'">
                    <li ng-repeat="cm in ctrl2.comentions" style="font-size: 14px" ng-show="cm.rrid_count > 0">
                        <a target="_blank" ng-href="/resolver/{{ cm.rrid }}"><span ng-bind-html="cm.name"></span></a>
                        <a target="_blank" ng-href="/resolver/<?php echo $rrid ?>/co-mentions?q=<?php echo $_GET['q'] ?>&cm={{ cm.rrid }}&cn={{ cm.name }}&cv={{ cm.viewid }}&i=<?php echo $id ?>" title="Click bar to show co-mentions detail" style="text-decoration: none"><span ng-show="cm.rrid_count > 0 || cm.resource_count > 0"><div style="width: {{cm.rrid_count / ctrl2.comentions[0].rrid_count * 80}}%; background-color: #408dc9; height:10px">&nbsp</div></span></a>
                        <!-- <span ng-show="!$last">, </span> -->
                    </li>
                </ul>
            <?php else: ?>
                <ul ng-show="ctrl2.mentions.mode == 'all'">
                    <li ng-repeat="cm in ctrl2.comentions" style="font-size: 14px">
                        <a target="_blank" ng-href="<?php echo $community->fullURL() ?>/data/record/{{ cm.viewid }}/{{ cm.rrid }}/resolver"><span ng-bind-html="cm.name"></span></a>
                        <a target="_blank" ng-href="<?php echo $community->fullURL() ?>/data/record/<?php echo $view.'/'.$rrid ?>/resolver/co-mentions?q=<?php echo $_GET['q'] ?>&cm={{ cm.rrid }}&cn={{ cm.name }}&cv={{ cm.viewid }}&i=<?php echo $id ?>" title="Click bar to show co-mentions detail" style="text-decoration: none"><span ng-show="cm.rrid_count > 0 || cm.resource_count > 0"><div style="width: {{(cm.rrid_count + cm.resource_count) / (ctrl2.comentions[0].rrid_count + ctrl2.comentions[0].resource_count) * 80}}%; background-color: #408dc9; height:10px">&nbsp</div></span></a>
                        <!-- <span ng-show="!$last">, </span> -->
                    </li>
                </ul>
                <ul ng-show="ctrl2.mentions.mode == 'rrid'">
                    <li ng-repeat="cm in ctrl2.comentions" style="font-size: 14px" ng-show="cm.rrid_count > 0">
                        <a target="_blank" ng-href="<?php echo $community->fullURL() ?>/data/record/{{ cm.viewid }}/{{ cm.rrid }}/resolver"><span ng-bind-html="cm.name"></span></a>
                        <a target="_blank" ng-href="<?php echo $community->fullURL() ?>/data/record/<?php echo $view.'/'.$rrid ?>/resolver/co-mentions?q=<?php echo $_GET['q'] ?>&cm={{ cm.rrid }}&cn={{ cm.name }}&cv={{ cm.viewid }}&i=<?php echo $id ?>" title="Click bar to show co-mentions detail" style="text-decoration: none"><span ng-show="cm.rrid_count > 0 || cm.resource_count > 0"><div style="width: {{cm.rrid_count / ctrl2.comentions[0].rrid_count * 80}}%; background-color: #408dc9; height:10px">&nbsp</div></span></a>
                        <!-- <span ng-show="!$last">, </span> -->
                    </li>
                </ul>
            <?php endif ?>
        </div>
    <?php
        $cell["body"] = Array(Array("html" => ob_get_clean()));
        $row[] = $cell;
        $rows[] = $row;
    ?>
    <!-- /Other research resources frequently mentioned with this resource -->

    <!-- additional report filters -->
    <?php
        $cell = Array();
        $row = Array();
        ob_start();
    ?>
        <span class="fa-stack fa-md">
            <i class="fa fa-circle fa-stack-2x" style="color:#408DC9"></i>
            <i class="fa fa-mouse-pointer fa-stack-1x fa-inverse"></i>
        </span>
        Additional Report Filters <span class="help-tooltip" data-name="resource-report-mentions-additional-report-filters.html"></span>
    <?php $cell["title"] = ob_get_clean(); ?>
    <?php ob_start(); ?>
    <?php if($result->getRRIDField("type") == "tool"): ?>
        <div style="margin-bottom:20px">
            <span ng-show="ctrl2.mentions.mode != 'all'">
                <a class="btn btn-primary" href="javascript:void(0)" ng-click="ctrl2.mentions.changeMode('all')">See All</a>
            </span>
            <span ng-show="ctrl2.mentions.mode != 'rrid'">
                <a class="btn btn-primary" href="javascript:void(0)" ng-click="ctrl2.mentions.changeMode('rrid')">See only RRID mentions</a>
            </span>
        </div>
    <?php endif ?>
    <form ng-submit="ctrl2.submitMentionFilter()">
        <div class="form-group">
            <label>Filter by Publication Year</label>
            &nbsp;&nbsp;
            <input type="text"
                autocomplete="no"
                ng-model="ctrl2.mentions.search_filters.publicationYear"
                typeahead="year.key for year in ctrl2.mentions.autocompleteValues($viewValue, ctrl2.mentions.counts_by_year)"
                typeahead-wait-ms="100"
                typeahead-min-length="0"
                style="width:65px"
                placeholder="Input year"
            />
        </div>
    </form>
    <?php
        $cell["body"] = Array(Array("html" => ob_get_clean()));
        $row[] = $cell;
    ?>
    <!-- /additional report filters -->

    <!-- Find mentions based on location -->
    <?php
        $cell = Array();
        ob_start();
    ?>
    <?php $cell["title"] = ob_get_clean(); ?>
    <?php ob_start(); ?>
        <span ng-show="(ctrl2.mentions.all_count > 0 && ctrl2.mentions.mode == 'all') || (ctrl2.mentions.rrid_count > 0 && ctrl2.mentions.mode == 'rrid')">
            <?php echo collaboratorNetworkFormHTML2() ?>
        </span>
    <?php
        $cell["body"] = Array(Array("html" => ob_get_clean()));
        $row[] = $cell;
        $rows[] = $row;
    ?>
    <!-- /Find mentions based on location -->
<?php endif ?>

    <!-- all mentions -->
    <?php
        $cell = Array();
        $row = Array();
        ob_start();
    ?>
        <span class="fa-stack fa-md" id="mentions-list">
            <i class="fa fa-circle fa-stack-2x" style="color:#1C2D5C"></i>
            <i class="fa fa-comment fa-stack-1x fa-inverse"></i>
        </span>
        <span ng-show="ctrl2.mentions.mode == 'all'">All Mentions</span>
        <span ng-show="ctrl2.mentions.mode == 'rrid'">RRID Usage</span>
        <span ng-show="ctrl2.mentions.mode == 'resource'">Resource Mentions</span>
        <span ng-show="ctrl2.mentions.all_count > 0 && ctrl2.mentions.mode == 'all'">({{ ctrl2.mentions.all_count }} mentions)</span>
        <span ng-show="ctrl2.mentions.rrid_count > 0 && ctrl2.mentions.mode == 'rrid'">({{ ctrl2.mentions.rrid_count }} RRID mentions out of {{ ctrl2.mentions.all_count }} total mentions)</span>
        <span ng-show="ctrl2.mentions.rrid_count == 0 && ctrl2.mentions.mode == 'rrid'">({{ ctrl2.mentions.rrid_count }} RRID mention out of {{ ctrl2.mentions.all_count }} total mentions)</span>
        <?php if($page_type == "organization-mentions"): ?>
            <a tooltip="Download the most recent 1,000 mentions" target="_self" href="/php/download-rin-rrid-mentions.php?viewid=<?php echo $view ?>&rrid=<?php echo $rrids ?>&om_rrids=<?php echo $organization_mentions_rrids ?>&tab=<?php echo $page_type ?>">[Download Mentions]</a>
            <span class="help-tooltip" data-name="mention-definition.html"></span>
        <?php else: ?>
            <a tooltip="Download the most recent 1,000 mentions" target="_self" href="/php/download-rin-rrid-mentions.php?viewid=<?php echo $view ?>&rrid=<?php echo $rrids ?>&cn=<?php echo $cm_name ?>&year={{ ctrl2.mentions.search_filters.publicationYear }}&place={{ ctrl2.mentions.search_filters.place_names }}&city={{ ctrl2.mentions.search_filters.cities }}&region={{ ctrl2.mentions.search_filters.regions }}&mode={{ ctrl2.mentions.mode }}&tab=<?php echo $page_type ?>">[Download Mentions]</a>
            <span class="help-tooltip" data-name="mention-definition.html"></span>
        <?php endif ?>
    <?php $cell["title"] = ob_get_clean(); ?>
    <?php ob_start(); ?>
        <div>
            <div style="margin-bottom:5px">
                <span style="cursor:pointer; margin: 0px 5px;" class="label label-danger" ng-repeat="filter in ctrl2.mentions.search_filters_display" ng-click="ctrl2.mentions.deleteRRIDMentionFilter(filter.name)">
                    <span class="fa fa-times"></span>
                    <span>
                        {{ filter.display_name }}<span ng-show="filter.value">: {{ filter.value }}</span>
                    </span>
                </span>
            </div>
            <?php ## added "select page" bar and "search page" bar -- Vicky-2019-15 ?>
            <div>
                <div style="display: inline-block;">
                    <ul ng-if="ctrl2.mentions.page_links.length" class="pagination">
                        <li ng-class="{disabled:ctrl2.mentions.page === 1}">
                            <a ng-click="ctrl2.mentions.changePage(1, ctrl2.mentions.page_links.length)">First</a>
                        </li>
                        <li ng-class="{disabled:ctrl2.mentions.page === 1}">
                            <a ng-click="ctrl2.mentions.changePage(ctrl2.mentions.page - 1, ctrl2.mentions.page_links.length)">Previous</a>
                        </li>
                        <li ng-repeat="link in ctrl2.mentions.page_links" ng-class="{active: link.page == ctrl2.mentions.page}" ng-click="ctrl2.mentions.changePage(link.page, ctrl2.mentions.page_links.length)">
                            <a ng-show="ctrl2.mentions.page >= link.page - 5 && ctrl2.mentions.page <= link.page + 5 &&  link.page > 6">{{ link.page }}</a>
                            <a ng-show="link.page <= 6 && ctrl2.mentions.page <= link.page + 5" >{{ link.page }}</a>
                        </li>
                        <li ng-class="{disabled:ctrl2.mentions.page === ctrl2.mentions.page_links.length}">
                            <a ng-click="ctrl2.mentions.changePage(ctrl2.mentions.page + 1, ctrl2.mentions.page_links.length)">Next</a>
                        </li>
                        <li ng-class="{disabled:ctrl2.mentions.page === ctrl2.mentions.page_links.length}">
                            <a ng-click="ctrl2.mentions.changePage(ctrl2.mentions.page_links.length, ctrl2.mentions.page_links.length)" >Last</a>
                        </li>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    </ul>
                </div>
                <div style="display: inline-block;" ng-show="ctrl2.mentions.page_links.length > 0">
                    <form ng-submit="ctrl2.mentions.changePage(ctrl2.mentions.pageVal, ctrl2.mentions.page_links.length)">
                        <div class="input-group-sm">
                            Page <input type="text" ng-model="ctrl2.mentions.pageVal" style="width:30px" ng-attr-placeholder="{{ctrl2.mentions.page}}"> of {{ctrl2.mentions.page_links.length}}
                            <span ng-show="ctrl2.mentions.mode == 'all' && ctrl2.mentions.page < ctrl2.mentions.page_links.length">({{ ctrl2.mentions.page * 100 - 100 + 1}} ~ {{ ctrl2.mentions.page * 100 }} of {{ctrl2.mentions.all_count}})</span>
                            <span ng-show="ctrl2.mentions.mode == 'all' && ctrl2.mentions.page == ctrl2.mentions.page_links.length">({{ ctrl2.mentions.page * 100 - 100 + 1}} ~ {{ ctrl2.mentions.all_count }} of {{ctrl2.mentions.all_count}})</span>
                            <span ng-show="ctrl2.mentions.mode == 'rrid' && ctrl2.mentions.page < ctrl2.mentions.page_links.length">({{ ctrl2.mentions.page * 100 - 100 + 1}} ~ {{ ctrl2.mentions.page * 100 }} of {{ctrl2.mentions.rrid_count}})</span>
                            <span ng-show="ctrl2.mentions.mode == 'rrid' && ctrl2.mentions.page == ctrl2.mentions.page_links.length">({{ ctrl2.mentions.page * 100 - 100 + 1}} ~ {{ ctrl2.mentions.rrid_count }} of {{ctrl2.mentions.rrid_count}})</span>
                        </div>
                    </from>
                </div>
            </div>
            <ul>
                <li ng-repeat="mention in ctrl2.mentions.mentions">
                    <?php ## modified mentions information -- Vicky-2019-1-17 ?>
                        <span ng-show="mention._source.dc.creators.length > 0">{{ mention._source.dc.creators[0].familyName }} {{ mention._source.dc.creators[0].initials }}</span><span ng-show="mention._source.dc.creators.length > 1">, et al</span>.
                        ({{ mention._source.dc.publicationYear }})
                        {{ mention._source.dc.title }}
                        {{ mention._source.dc.publishers[0].name }}<span ng-show="mention._source.dc.publishers[0].volume.length > 0">, {{ mention._source.dc.publishers[0].volume }}</span><span ng-show="mention._source.dc.publishers[0].issue.length > 0">({{ mention._source.dc.publishers[0].issue }})</span><span ng-show="mention._source.dc.publishers[0].pagination.length > 0">, {{ mention._source.dc.publishers[0].pagination }}</span>.

                    <!--(<a target="_blank" ng-href="<?php echo $community->fullURL() ?>/{{ mention._id.replace('PMID:', '') }}">Link</a>)-->
                    (<a target="_blank" ng-href="/<?php echo $community->portalName ?>/{{ mention._id.replace('PMID:', '') }}?rpKey=on">PMID:{{ mention._id.replace('PMID:', '') }}</a>)  <!--open link in new tab - Vicky-2018-12-2-->
                    <div ng-show="mention.matching_researchers.length > 0">
                        <ul>
                            <li ng-repeat="match in mention.matching_researchers">
                                <!-- {{ match.creator.name }} - {{ match.creator.locations[0].name }}, {{ match.creator.locations[0].city }}, {{ match.creator.locations[0].country }} -->
                                <!-- {{ match.creator.name }} - {{ match.matches.name }}, {{ match.matches.city }}, {{ match.matches.country }} -->
                                {{ match.creator.name }} - {{ match.matches.city }}, {{ match.matches.region }}, {{ match.matches.country }}
                            </li>
                        </ul>
                    </div>
                </li>
            </ul>
            <div ng-show="ctrl2.mentions.page > 100">
              <p>Can't find the mentions that you are looking for? Use <b>Publication Year</b> to filter firstly to retrieve the literature of your interest.</p>
            </div>
            <div>
              <?php ## added "select page" bar and "search page" bar -- Vicky-2019-15 ?>
                <div style="display: inline-block;">
                    <ul ng-if="ctrl2.mentions.page_links.length" class="pagination">
                        <li ng-class="{disabled:ctrl2.mentions.page === 1}">
                            <a ng-click="ctrl2.mentions.changePage(1, ctrl2.mentions.page_links.length)">First</a>
                        </li>
                        <li ng-class="{disabled:ctrl2.mentions.page === 1}">
                            <a ng-click="ctrl2.mentions.changePage(ctrl2.mentions.page - 1, ctrl2.mentions.page_links.length)">Previous</a>
                        </li>
                        <li ng-repeat="link in ctrl2.mentions.page_links" ng-class="{active: link.page == ctrl2.mentions.page}" ng-click="ctrl2.mentions.changePage(link.page, ctrl2.mentions.page_links.length)">
                            <a ng-show="ctrl2.mentions.page >= link.page - 5 && ctrl2.mentions.page <= link.page + 5 &&  link.page > 6">{{ link.page }}</a>
                            <a ng-show="link.page <= 6 && ctrl2.mentions.page <= link.page + 5" >{{ link.page }}</a>
                        </li>
                        <li ng-class="{disabled:ctrl2.mentions.page === ctrl2.mentions.page_links.length}">
                            <a ng-click="ctrl2.mentions.changePage(ctrl2.mentions.page + 1, ctrl2.mentions.page_links.length)">Next</a>
                        </li>
                        <li ng-class="{disabled:ctrl2.mentions.page === ctrl2.mentions.page_links.length}">
                            <a ng-click="ctrl2.mentions.changePage(ctrl2.mentions.page_links.length, ctrl2.mentions.page_links.length)" >Last</a>
                        </li>
                        &nbsp;&nbsp;&nbsp;&nbsp;
                    </ul>
                </div>
                <div class="input-group-sm" ng-show="ctrl2.mentions.page_links.length > 0" style="display: inline-block;">
                    Page <input type="text" ng-model="ctrl2.mentions.pageVal" style="width:30px" ng-attr-placeholder="{{ctrl2.mentions.page}}"> of {{ctrl2.mentions.page_links.length}}
                    <span ng-show="ctrl2.mentions.mode == 'all' && ctrl2.mentions.page < ctrl2.mentions.page_links.length">({{ ctrl2.mentions.page * 100 - 100 + 1}} ~ {{ ctrl2.mentions.page * 100 }} of {{ctrl2.mentions.all_count}})</span>
                    <span ng-show="ctrl2.mentions.mode == 'all' && ctrl2.mentions.page == ctrl2.mentions.page_links.length">({{ ctrl2.mentions.page * 100 - 100 + 1}} ~ {{ ctrl2.mentions.all_count }} of {{ctrl2.mentions.all_count}})</span>
                    <span ng-show="ctrl2.mentions.mode == 'rrid' && ctrl2.mentions.page < ctrl2.mentions.page_links.length">({{ ctrl2.mentions.page * 100 - 100 + 1}} ~ {{ ctrl2.mentions.page * 100 }} of {{ctrl2.mentions.rrid_count}})</span>
                    <span ng-show="ctrl2.mentions.mode == 'rrid' && ctrl2.mentions.page == ctrl2.mentions.page_links.length">({{ ctrl2.mentions.page * 100 - 100 + 1}} ~ {{ ctrl2.mentions.rrid_count }} of {{ctrl2.mentions.rrid_count}})</span>
                    <button style="display: none;" ng-submit="ctrl2.mentions.changePage(ctrl2.mentions.pageVal, ctrl2.mentions.page_links.length)">Search Page</button>
                </div>
            </div>
        </div>
    <?php
        $cell["body"] = Array(Array("html" => ob_get_clean()));
        $row[] = $cell;
        $rows[] = $row;
    ?>
    <!-- /all mentions -->
<?php endif ?>

<?php if($dknet_flag): ?>
    <?php echo \helper\htmlElement("collection-modals", Array("user" => $_SESSION["user"], "community" => $community, "uuids" => $result->getRRIDField("uuid"), "views" => $view)); ## added resource item to authentication report ?>
<?php endif ?>

<span ng-controller="mentionsModalCaller"></span>

<div class="rin" id="single-item-app" ng-controller="singleItemController as ctrl">
    <input id="search-single-item-view" type="hidden" value="<?php echo $view ?>" />
    <input id="search-single-item-rrid" type="hidden" value="<?php echo $rrids ?>" />
    <input id="search-single-item-organization-mentions-rrids" type="hidden" value="<?php echo $organization_mentions_rrids ?>" />
    <input id="search-single-item-mode" type="hidden" value="<?php echo $page_type ?>" />
    <input id="search-single-item-proper-citation" type="hidden" value="<?php echo $result->getRRIDField("proper-citation") ?>" />
    <?php
        if($page_type == "info") {
            $controller_name = "infoController";
        } else {
            $controller_name = "mentionsController";
        }
    ?>
    <div ng-controller="<?php echo $controller_name ?> as ctrl2">

        <?php

        $breadcrumbs = Array(
            Array("text" => "Home", "url" => $community->fullURL()),
            Array("text" => "Resource Reports", "url" => $community->fullURL() . "/rin/rrids"),
            Array("text" => $search_manager->getName(true), "url" => $community->fullURL() . "/data/source/" . $view . "/search"),
        );
        if($page_type == "mentions") {
            $breadcrumbs[] = Array("text" => "Resource Summary Report", "url" => $community->fullURL() . "/data/record/" . $view . "/" . $rrid . "/resolver?q=" . $_GET['q'] . "&i=" . $id);
        } elseif($page_type == "co-mentions") {
            $breadcrumbs[] = Array("text" => "Resource Summary Report", "url" => $community->fullURL() . "/data/record/" . $view . "/" . $rrid . "/resolver?i=" . $id);
            $breadcrumbs[] = Array("text" => "Resource Usage Report", "url" => $community->fullURL() . "/data/record/" . $view . "/" . $rrid . "/resolver/mentions?i=" . $id);
        } elseif($page_type == "organizations") {
            $breadcrumbs[] = Array("text" => "Resource Summary Report", "url" => $community->fullURL() . "/data/record/" . $view . "/" . $rrid . "/resolver?i=" . $id);
        } elseif($page_type == "organization-mentions") {
            $breadcrumbs[] = Array("text" => "Resource Summary Report", "url" => $community->fullURL() . "/data/record/" . $view . "/" . $rrid . "/resolver?i=" . $id);
            $breadcrumbs[] = Array("text" => "Organizational Usage Report", "url" => $community->fullURL() . "/data/record/" . $view . "/" . $rrid . "/resolver/organizations?i=" . $id);
        }
        $breadcrumbs[] = Array("text" => $page_title, "active" => true);

        $rin_data = Array(
            "title" => $page_title,
            "breadcrumbs" => $breadcrumbs,
            "rows" => $rows,
        );
        echo \helper\htmlElement("rin-style-page", $rin_data);

        ?>

    </div>
</div>

<script>
addthis_share = {
    title: "<?php echo $community->portalName ?> Resource Report: <?php echo $result->getRRIDField("name") ?> (<?php echo $result->getRRIDField("curie") ?>)",
    description: "<?php echo $result->getRRIDField("description") ?>"
};
</script>

<script>
function showHideData() {
        var ele1 = document.getElementById("data_info");
        var ele2 = document.getElementById("data_expand");
        var ele3 = document.getElementById("data_collapse");
        if(ele1.style.display == "block") {
                ele1.style.display = "none";
                ele2.style.display = "block";
                ele3.style.display = "none";
          }
        else {
            ele1.style.display = "block";
            ele2.style.display = "none";
            ele3.style.display = "block";
        }
    }
</script>
