<?php

require_once $_SERVER[DOCUMENT_ROOT] . '/classes/schemas/schemas.class.php';

$user = isset($_SESSION['user']) ? $_SESSION['user'] : NULL;
$custom = new View();
$custom->getByCommView($community->cid, $vars['view']);

$source_info = getSourceInfo($vars['view']);    // for the source description tab

$columns = getColumnsData($vars['view'], $vars['uuid']);    // for the information tab
$tile_data = reshapeColumnsData($columns, $resource);   // converts the inforamation into a more usable format and gets the google maps data

$all_sources_keys = array_keys($allSources);
$views_data = NULL;
foreach($all_sources_keys as $ask) {
    if(\helper\startsWith($ask, $resource->original_id)) {
        $views_data = \helper\getViewsFromOriginalID($resource->original_id);
        break;
    }
    if(\helper\startsWith($ask, $resource->rid)) {
        $views_data = \helper\getViewsFromOriginalID($resource->rid);
        break;
    }
}

$lit_data = getLitData($columns);   // for the literature tab
$months = array('','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');

$description_active = Array("header" => ' class="active"', "tab" => " in active");
$referencedby_active = Array("header" => "", "tab" => "");

$notif_input_string = "";
if(isset($_GET["notif"])){
    $notif = \helper\aR($_GET["notif"], "s");
    $temp = $referencedby_active;
    $referencedby_active = $description_active;
    $description_active = $temp;
    Subscription::clearNotification($notif, $_SESSION["user"]);

    $notif_type = isset($_GET["notif_email"]) ? "email" : "web";
    $notif_input_string = '<input type="hidden" id="subscription-id" readonly value="' . $notif . '" />';
    $notif_input_string .= '<input type="hidden" id="subscription-type" readonly value="' . $notif_type . '" />';
}


// FUNCTION DEFINITIONS ***************************************************************************

function getColumnsData($view, $uuid){
    // retrieves each category of data from the neuinfo for this resource
    $url = Connection::environment() . '/v1/federation/data/' . $view . '.xml?q=*&exportType=all&filter=v_uuid:' . $uuid;
    $xml = simplexml_load_file($url);

    $columns = Array();
    if ($xml) {
        foreach ($xml->result->results->row->data as $data) {
            $columns[(string)$data->name] = (string)$data->value;
        }
    }
    $columns['schemas'] = SchemaGeneratorResources::generate($columns);
    return $columns;
}

function reshapeColumnsData($columns, $resource, $user){
    $tile_fields = Array(
        "Keywords" => "Keywords",
        "Resource ID" => "Resource ID",
        "Alternate IDs" => "Alternate IDs",
        "Related Condition" => "Related Condition",
        "Website Status" => "Website Status",
        "Parent Organization" => "Parent Organization",
        "abbreviation" => "Abbreviation(s)",
        "related_application" => "Related Application(s)",
        "related_to" => "Related To",
        "species" => "Species",
        "resource_type" => "Resource Type",
        "funding_information" => "Funding Information",
        "usedby" => "Used By",
        "uses" => "Uses",
        "availability" => "Availability",
        "synonym" => "Synonym(s)",
        "proper_citation" => "Proper citation",
        "terms_of_use_urls" => "Terms of Use URLs",
        "Alternate URLs" => "Alternate URLs",
        "Old URLs" => "Old URLs",
        "Canonical ID" => "Canonical ID",
        "Reference" => "Reference",
    );
    if(isset($columns["Resource ID"]) && isset($columns["Alternate IDs"]) && strip_tags($columns["Resource ID"]) === strip_tags($columns["Alternate IDs"])) unset($tile_fields["Alternate IDs"]);
    if($user && $resource->isAuthorizedOwner($user)){
        $tile_fields['comment'] = 'Comment';
        $tile_fields['located_in'] = 'Located In';
        $tile_fields['editorial_note'] = 'Editorial Note';
        $tile_fields['processing'] = 'Processing';
    }
    $map_field = "Parent Organization";
    $info_data = Array("sections" => Array());	// 2 members: sections which is an array and map for google maps stuff
    foreach($tile_fields as $title => $new_title){
        $this_info = Array();
        $this_info['title'] = $new_title;
        if($new_title == "Resource ID"){
            $this_info['text'] = strip_tags($columns[$title]);
        }else{
            $this_info['text'] = $columns[$title];
        }
        if($this_info['text'] && $this_info['text'] != "NULL") array_push($info_data['sections'], $this_info);
    }
    if($columns[$map_field]){
        $url = 'https://maps.google.com/maps/api/geocode/xml?address=' . $columns[$map_field] . '&sensor=false';
        $xml = simplexml_load_file($url);
        if($xml && ($xml->result->geometry->location || $xml->result->geometry->location->lat)){
            $this_info['title'] = $map_field;
            $this_info['lat'] = $xml->result->geometry->location->lat;
            $this_info['lng'] = $xml->result->geometry->location->lng;
            $this_info['divid'] = str_replace(' ', '_', $this_info['title']);
            $this_info['point'] = $columns[$this_info['title']];
            $info_data['map'] = $this_info;
        }
    }
    return $info_data;
}

function getLitData($columns){
    $col_section = "defining_citation";
    $citations = explode(",", $columns[$col_section]);
    $cite_arrays = Array();
    if(count($citations) > 0){
        $regex = Array(
            "/^(PMID):?[ ]?(.*)/",
            "/^(DOI):?[ ]?(.*)/"
        );
        foreach($citations as $cite){
            $cite = trim($cite);
            $matches = Array();
            $found = false;
            foreach($regex as $rx){
                if(preg_match($rx, $cite, $matches)){
                    $found = true;
                    break;
                }
            }
            if(!isset($cite_arrays[$matches[1]])) $cite_arrays[$matches[1]] = Array();
            $cite_arrays[$matches[1]][] = $matches[2];
        }
    }

    $data_array = Array();
    if(isset($cite_arrays["PMID"])){
        $pmids = $cite_arrays["PMID"];
        $url = Connection::environment() . '/v1/literature/pmid?pmid='.implode('&pmid=',$pmids);
        $xml = simplexml_load_file($url);
        $data_array["PMID"] = $xml;
    }
    if(isset($cite_arrays["DOI"])){
        $dois = $cite_arrays["DOI"];
        $data_array["DOI"] = Array();
        foreach($dois as $doi){
            $doi_json = \helper\getDOIJSON($doi);
            if(!is_null($doi_json)) $data_array["DOI"][] = $doi_json;
        }
    }

    return $data_array;
}

function getSourceInfo($view){
    $record = Array();
    $splits = explode('-', $view);
    $rootID = join('-', array_slice($splits, 0, count($splits) - 1));
    $url = Connection::environment() . '/v1/federation/data/nlx_144509-1.xml?q=*&count=1&filter=original_id:' . $rootID;
    $xml = simplexml_load_file($url);
    if ($xml) {
        foreach ($xml->result->results->row->data as $data) {
            $record[(string)$data->name] = (string)$data->value;
        }
    }
    return $record;
}

/******************************************************************************************************************************************************************************************************/
?>
<input type="hidden" id="resource-primary-id" readonly value="<?php echo $resource->id ?>" />
<input type="hidden" id="resource-id" readonly value="<?php echo $resource->rid ?>" />
<input type="hidden" id="rrid" readonly value="<?php echo $resource->original_id ?>" />
<input type="hidden" id="cid" readonly value="<?php echo $community->id ?>" />
<input type="hidden" id="community-portal-name" readonly value="<?php echo $community->portalName ?>" />
<?php echo $notif_input_string ?>
<style>
    .tab-pane {
        background: #f8f8f8;
        padding: 15px 15px;
        border-bottom: 1px solid #dedede;
    }
    .tab-v5 {
        margin-top: 30px;
    }
    .tab-v5 .tab-content {
        margin: 0;
        padding: 0;
    }
    .tag-box {
        margin-bottom: 20px;
    }
    .map {
        width: 100%;
        height: 350px;
        border-top: solid 1px #eee;
        border-bottom: solid 1px #eee;
    }
    .node {
        stroke: #fff;
        stroke-width: 0.5px;
    }
    .link {
        stroke-opacity: .6;
    }
    .d3-tip {
        line-height:1;
        font-weight:bold;
        background: rgba(0,0,0,0.8);
        color: #fff;
        border-radius: 2px;
    }
    .tab-v5 .nav-tabs > li > a {
        font-size: 12pt;
    }
    .proper-citation {
        padding: 2px 5px;
        background-color: #eee;
        border: 1px solid black;
    }
</style>
<link rel="stylesheet" type="text/css" href="/css/curator.css" />
<link rel="stylesheet" type="text/css" href="/css/analytics-resource-comentions.css" />
<script src="/js/Highcharts-6.0.7/code/js/highcharts.js"></script>
<script src="/js/Highcharts-6.0.7/code/js/modules/heatmap.js"></script>
<script src="/js/Highcharts-6.0.7/code/js/modules/exporting.js"></script>
<link rel="stylesheet" href="/js/Highcharts-6.0.7/code/css/highcharts.css" />
<script src="/js/resolver.js"></script>
<script src="/js/module-resource.js"></script>
<script src="/js/module-resource-directives.js"></script>
<script src="/js/view-resource.js"></script>
<script src="/js/analytics-resource-comentions.js"></script>


<meta property="og:title" content="<?php echo strip_tags($columns[$custom->title]) ?>" />
<meta property="og:description" content="<?php echo strip_tags(\helper\formattedDescription($columns[$custom->description])) ?>" />

<div class="container margin-bottom-50" style="margin-top:50px" ng-app="viewResourceApp" ng-cloak>
<?php
    if (!empty($columns['schemas'])) {
        foreach ($columns['schemas'] as $schema) {
            echo '<script type="application/ld+json">' . $schema->generateJSON() . '</script>';
        }
    }
?>

    <span ng-controller="mentionsModalCaller"></span>
    <div class="row" ng-controller="resourceFields as rf">
        <div ng-show="resource_image" ng-class="{'col-md-1': resource_image}">
            <img class="img-responsive" ng-src="{{ resource_image }}"/>
        </div>
        <div class="col-md-5">
            <h1 style="display:inline"><?php echo strip_tags($columns[$custom->title]) ?> (RRID:<?php echo strip_tags($columns["Resource ID"]) ?>)</h1>
            <?php if(isset($_SESSION['user'])): ?>
                &nbsp&nbsp<a target="_self" href="https://scicrunch.org/browse/resourcesedit/<?php echo $resource->rid ?>"><i class="glyphicon glyphicon-pencil"></i></a>
                <?php if($_SESSION["user"]->role > 0): ?>
                    <a ng-controller="altIDs as ai" ng-show="is_curator" class="fa fa-exchange" uib-popover-template="ai.dynamicPopover.templateUrl" popover-placement="right" href="javascript:void(0)"></a>
                <?php endif ?>
            <?php endif ?>
            <br/><?php echo '<a target="_blank" href="' . $columns["url"] . '">' . $columns["url"] . '</a>'; ?>
        </div>
        <div class="col-md-6 text-right">
            <?php if(isset($_SESSION["user"])): ?><a target="_blank" href="/<?php echo $community->portalName ?>/account/resources"><i class="fa fa-cog fa-lg"></i></a><?php endif ?>
            <span ng-controller="resourceMentions as rm" resource-mention-user-subscription-dir></span resource-mention-user-subscription-dir>
            <span claim-resource-ownership-dir></span claim-resource-ownership-dir>
        </div>

    </div>
    <div class="row">
        <p>
            <?php if(isset($_GET["redirectid"])): ?>
                <b>Redirected from:</b> <?php echo $_GET["redirectid"] ?>
            <?php endif ?>
        </p>
    </div>

    <div class="row">
        <div class="col-md-12">
            <p class="truncate-desc" style="font-size: 16px">
                <?php echo \helper\formattedDescription($columns[$custom->description]) ?>
            </p>
            <div class="addthis_inline_share_toolbox"></div>
        </div>
    </div>

    <hr/>

    <div class="tab-v5">
        <ul class="nav nav-tabs nav-tabs-js" role="tablist">
            <li<?php echo $description_active["header"] ?>><a id="tab-information" href="#description" role="tab" data-toggle="tab">Information</a></li>
            <li><a id="tab-relationships" href="#relationships" role="tab" data-toggle="tab">Relationships</a></li>
            <?php if(count($lit_data) > 0): ?>
                <li><a id="tab-references" href="#literature" role="tab" data-toggle="tab">References</a></li>
            <?php endif ?>
            <li<?php echo $referencedby_active["header"] ?>><a id="tab-referenced-by" href="#referencedby" role="tab" data-toggle="tab">Referenced By</a></li>
            <li><a id="tab-analytics" href="#analytics" role="tab" data-toggle="tab">Analytics</a></li>
            <?php if(!is_null($views_data)): ?>
                <li><a id="tab-data" href="#views" role="tab" data-toggle="tab">Data</a></li>
                <li><a id="tab-data-licenses" href="#views-licenses" role="tab" data-toggle="tab">Data Licenses</a></li>
            <?php endif ?>
            <li><a id="tab-source" href="#source" role="tab" data-toggle="tab">Source</a></li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade<?php echo $description_active["tab"] ?>" id="description">
                <div class="row">
                    <?php $row_counter = 0 ?>
                    <?php foreach($tile_data['sections'] as $i => $section): ?>
                        <?php if($row_counter == 0): ?><div class="row"><?php endif ?>
                        <?php $row_counter += 1; ?>
                        <div class="col-lg-3" style="word-wrap: break-word">
                            <div class="truncate-column tag-box tag-box-v2 box-shadow shadow-effect-1">
                                <h2><?php echo $section['title'] ?></h2>
                                <p><?php echo $section['text'] ?></p>
                            </div>
                        </div>
                        <?php if($row_counter >= 4): $row_counter = 0;?></div><?php endif ?>
                    <?php endforeach ?>
                    <?php if($row_counter != 0): ?></div><?php endif ?>
                    <?php if(isset($tile_data['map'])): ?>
                        <div class="col-lg-6">
                            <div id="<?php echo $tile_data['map']['divid'] ?>" class="map" lat="<?php echo $tile_data['map']['lat'] ?>" lng="<?php echo $tile_data['map']['lng'] ?>" point="<?php echo $tile_data['map']['point'] ?>"></div>
                        </div>
                    <?php endif ?>
                    <div class="tag-box tag-box-v2 box-shadow shadow-effect-1" id="comention-rec-container">
                        <h2>Other resources frequently mentioned in the literature with this resource</h2>
                        <div style="display:none" class="owl-carousel" id="comention-rec">

                        </div>
                        <div id="comention-rec-loading">
                            <h4><i class="fa fa-spinner fa-spin"></i></h4>
                        </div>
                        <div id="comention-rec-nonefound" style="display:none">
                            <p>We were unable to find any frequently comentioned resources.</p>
                        </div>
                    </div>
                    <!-- Description -->

                </div>
            </div>

            <div class="tab-pane fade" id="source">
                <div class="row">
                    <div class="col-md-8">
                        <h2><?php echo $source_info['Resource Name'] ?></h2>
                        <p class="truncate-desc"><?php echo \helper\formattedDescription($source_info['Description']) ?></p>
                    </div>
                    <div class="col-md-4"><a target="_self" href="/<?php echo $community->portalName ?>/about/sources/<?php echo $vars['view'] ?>" class="btn-u btn-u-lg">View Source Info</a></div>
                </div>
            </div>

            <?php if(count($lit_data) > 0): ?>
                <div class="tab-pane fade" id="literature">
                <?php if(isset($lit_data["PMID"])): ?>
                    <?php foreach($lit_data["PMID"]->publication as $paper): ?>
                        <?php echo \helper\htmlElement("literature-item", Array(
                            "type" => "pmid",
                            "pmid" => $paper["pmid"],
                            "title" => $paper->title,
                            "author" => $paper->authors->author,
                            "year" => $paper->year,
                            "month" => (int) $paper->month,
                            "day" => $paper->day,
                            "abstract" => $paper->abstract,
                            "journal" => $paper->journalShort,
                        )) ?>
                    <?php endforeach ?>
                <?php endif ?>
                <?php if(isset($lit_data["DOI"])): ?>
                    <?php foreach($lit_data["DOI"] as $paper): ?>
                        <?php
                            $created_date = $paper["created"]["date-parts"][0];
                            if(isset($paper["author"]) && count($paper["author"]) > 0) {
                                $author = $paper["author"][0]["family"] . ", " . $paper["author"][0]["given"];
                            } else {
                                $author = "";
                            }
                            echo \helper\htmlElement("literature-item", Array(
                                "type" => "doi",
                                "doi" => $paper["DOI"],
                                "url" => $paper["URL"],
                                "title" => $paper["title"],
                                "author" => $author,
                                "year" => $created_date[0],
                                "month" => $created_date[1],
                                "day" => $created_date[2],
                                "abstract" => "",
                                "journal" => $paper["publisher"],
                            ));
                        ?>
                    <?php endforeach ?>
                <?php endif ?>
                </div>
            <?php endif ?>

            <div id="referencedby" class="tab-pane fade<?php echo $referencedby_active["tab"] ?>" ng-controller="resourceMentions as rm">
                <div resource-mentions-dir></div>
            </div>

            <div id="analytics" class="tab-pane fade">
                <div class="container">
                    <div class="row">
                        <h2>Co-mentions heatmap</h2>
                        <p>Load a heatmap of the top 20 resources that share the most co-mentions with this resource in the literature.</p>
                        <button class="load-comention-grid">Load co-mentions</button><br/>
                        <label><input class="comention-grid-hc-toggle" type="checkbox" checked /> Show only high confidence mentions</label>
                    </div>
                    <div class="row"><div id="comention-grid-infoclick"></div></div>
                    <div class="row"><div id="comention-grid" style="width: 800px; height: 800px;"></div></div>
                </div>
            </div>

            <?php if(!is_null($views_data)): ?>
                <div id="views" class="tab-pane fade">
                    <?php echo \helper\generateHTMLViewsTab($views_data['views'], $community); ?>
                </div>
                <div id="views-licenses" class="tab-pane fade">
                    <div style="margin-bottom: 10px;"><b>License URL: </b> <a href="<?php echo $views_data['license-url'] ?>"><?php echo $views_data['license-url'] ?></a></div>
                    <div style="margin-bottom: 10px;margin-left: 15px;text-indent: -15px"><b>License Information: </b><br/><?php echo $views_data['license'] ?></div>
                </div>
            <?php endif ?>

            <div id="relationships" class="tab-pane fade" ng-controller="resourceRelationships as rr">
                <div class="panel panel-success">
                    <div class="panel-heading"><div class="container"><div resource-relationships-filter-dir></div></div></div>
                    <div class="panel-body"><div resource-relationships-list-dir></div></div>
                </div>
            </div>
        </div>
    </div>
</div>
<ol id="joyRideTipContent">
    <li data-id="tab-information" data-text="next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Information</h2>
        <p>Information on this specific resource.</p>
    </li>
    <li data-id="tab-relationships" data-text="next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Relationships</h2>
        <p>See other resources that this resources is related to.</p>
    </li>
    <li data-id="tab-references" data-text="next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>References</h2>
        <p>Publications describing this resource.</p>
    </li>
    <li data-id="tab-referenced-by" data-text="next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Referenced by</h2>
        <p>Publications that reference this resource.  These references are discovered by human submissions and automated crawling through various journals.</p>
    </li>
    <li data-id="tab-analytics" data-text="next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Analytics</h2>
        <p>Search for other resources that are referenced by publications that reference this resource.</p>
    </li>
    <li data-id="tab-data" data-text="next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Data</h2>
        <p>This resource is also a data repository used by SciCrunch.  Search through the data.</p>
    </li>
    <li data-id="tab-data-licenses" data-text="next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Data Licenses</h2>
        <p>The licenses the data is under.</p>
    </li>
    <li data-id="tab-source" data-text="next" data-options="tipLocation:bottom;tipAnimation:fade">
        <h2>Source</h2>
        <p>The data repository this resource is listed from.</p>
    </li>
</ol>
<script>
    $(function() {
        if(window.location.hash === "#used-in-literature") {
            window.location.hash = "#referencedby";
            $('#tab-referenced-by').tab("show");
        }

        var rid = $("#resource-id").val();
        $.get("/php/resource-comentions.php?rid=" + rid + "&count=10&hc&sl")
            .then(function(response) {
                var comentions = response;
                $("#comention-rec-loading").hide();
                if(comentions.length == 0) {
                    $("#comention-rec-nonefound").show();
                    return;
                }
                $("#comention-rec").show();
                for(var i = 0; i < comentions.length; i++) {
                    var first = (rid !== comentions[i].rid1);
                    var uuid = first ? comentions[i].uuid1 : comentions[i].uuid2;
                    var name = first ? comentions[i].name1 : comentions[i].name2;
                    var corid = first ? comentions[i].rid1 : comentions[i].rid2;
                    var description = first ? comentions[i].description1 : comentions[i].description2;
                    var count = comentions[i].count;
                    if(!description) {
                        description = "";
                    }
                    if(description.length > 100) {
                        description = description.substr(0, 100) + "...";
                    }
                    $("#comention-rec").append(
                        '<div class="item">' +
                            '<div class="truncate-column tag-box tag-box-v2 box-shadow shadow-effect-1 text-center" style="border:1px solid black;margin:5px;padding:5px">' +
                                '<a target="_blank" href="/<?php echo $community->portalName ?>/Any/record/nlx_144509-1/'+uuid+'/search">' +
                                    '<h4>' + name + '</h4><h6>' + description + '</h6>' +
                                '</a>' +
                            '</div>' +
                        '</div>'
                    );
                }
                $("#comention-rec").owlCarousel({
                    autoPlay: 5000,
                    pagination: false
                });
            });
    });
</script>
