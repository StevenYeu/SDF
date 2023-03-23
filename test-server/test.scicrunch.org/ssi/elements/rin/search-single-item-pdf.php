<?php

use Dompdf\Dompdf;
use Dompdf\Options;

include 'process-elastic-search.php';

$view = $data["view"];
$rrid = $data["rrid"];
$id = $data["id"];
$community = $data["community"];

$protocol_flag = false;
if($view == "protocol") {
    $rrid = str_replace('$U+002F;', '/', $rrid);
    $protocol_flag = true;
}

$item_mentions = \search\searchSingleItemMentions($view, $rrid, NULL, NULL, NULL, NULL, "all", 0, 20);

$search_manager = ElasticRRIDManager::managerByViewID($view);
if($protocol_flag) $results = $search_manager->searchDOI($rrid);
else $results = $search_manager->searchRRID($rrid);

if($results->hitCount() == 0) {
    return;
}
## check unique id -- Vicky-2018-12-20
foreach($results as $result){
  if ($result->getRRIDField("id") == $id) break;
}

##changed the source name and added source database in the Data and Source Informationin the pdf report -- Vicky-2018-12-6
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
    case "Cell Line":
        $type_name = "Cell Line";
        $source = "Cellosaurus";
        $item_views = NULL;
        $source_database = NULL;
        break;
    case "Organism":
        $type_name = "Organism";
        $source = "Integrated Animals";
        $item_views = NULL;
        $source_database = $result->getField("Database");
        break;
    case "Plasmid":
        $type_name = "Plasmid";
        $source = "Addgene";
        $item_views = NULL;
        $source_database = NULL;
        break;
    case "Biosample":
        $type_name = "Biosample";
        $source = "NCBI Biosample";
        $item_views = NULL;
        $source_database = NULL;
        break;
    case "Protocol":
        $type_name = "Protocol";
        $source = "Protocols.io";
        $item_views = NULL;
        $source_database = NULL;
        break;
}

## generated data information
$data_info = Array();
$data_info['URL'] = '<a target="_blank" href="'.$result->getRRIDField("url").'">'.$result->getRRIDField("url").'</a>';
$data_info['Description'] = $result->getRRIDField("description");
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
        case "External URL":
            $data_info[$field_name->name] = '<a target="_blank" href="'.$result->getField($field_name->name).'">'.$result->getField($field_name->name).'</a>';
            break;
        default:
            $data_info[$field_name->name] = $result->getField($field_name->name);
    }
}

$data_order = array_keys($data_info);
if(!empty($result->getSpecialField("report-data-order"))) {
    if(!empty($result->getSpecialField("report-data-order")["data_order"])) $data_order = $result->getSpecialField("report-data-order")["data_order"];
    if(!empty($result->getSpecialField("report-data-order")["top_info_count"])) $top_info_count = $result->getSpecialField("report-data-order")["top_info_count"];
}

$style = file_get_contents(__DIR__ . "/../../../css/rin.css");
$style = $style."
    a { color: #408DC9; }
    .rin .report .section .body { margin-left: 20px !important; margin-bottom: 10px !important; }
    .rrid-name { font-size: 20pt !important; margin-bottom: 10px !important; }
    .rrid { font-size: 18px !important; }
";
$style = "<style>".$style."</style>";

ob_start();
?>

<div class="rin">
    <div class="report">
        <div style="padding: 5px; height: 100px; background: #1C2D5C; color: #FFFFFF;">
            <h2 style="margin: 5px 0px;">Resource Summary Report</h2>
            <p>Generated by <a style="color: white" href="<?php echo $community->fullURL() ?>"><?php echo $community->shortName ?></a> on <?php echo \helper\dateFormat("date", time()) ?></p>
        </div>
        <div class="wrapper" style="top: 0px; margin: 0px; border-top: none">
            <div class="section">
                <!--<div class="title">Resource Name</div>-->
                <div class="body" style="margin-left: 0px !important">
                    <div class="rrid-name">
                        <a href="<?php echo $community->fullURL() ?>/data/record/<?php echo $view ?>/<?php echo $rrid ?>/resolver"><?php echo $result->getRRIDField("name") ?></a>
                    </div>
                    <div class="rrid"><?php echo $result->getRRIDField("curie") ?></div>
                    <div class="rrid">Type: <?php echo ucwords($result->getRRIDField("type")) ?></div>
                </div>
                <div class="section">
                    <div class="title">Proper Citation</div>
                    <div class="body">
                        <p>
                          <?php  ## modified Proper Citation format -- Vicky-2019-1-23
                              /*$values = explode(" (", $result->getRRIDField("proper-citation"));
                              if (count($values) > 1) echo "(".trim($values[0]).", ".$values[1];
                              else echo $values[0];*/
                              echo $result->getField("Proper Citation")
                          ?>
                        </p>
                    </div>
                </div>
                <div class="section">
                    <div class="title"><?php echo $type_name ?> Information</div>
                    <div class="body">
                        <?php foreach($data_order as $data_name): ?>
                            <?php if($data_info[$data_name] != ""): ?>
                                <p>
                                    <strong><?php echo $data_name ?>:</strong>
                                    <?php echo $data_info[$data_name] ?>
                                </p>
                            <?php endif ?>
                        <?php endforeach ?>
                    </div>
                </div>
            <?php if(!$protocol_flag): ?>
                <div class="section">
                    <div class="title">Ratings and Alerts</div>
                    <div class="body">
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
                                                <a href="<?php echo $rating["url"] ?>"><?php echo $rating["url"] ?></a>
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
                                <?php foreach($result->getSpecialField("alerts") as $alert): ?>
                                    <?php if($alert["type"] == "warning"): ?>
                                        <span style="color:red">Warning: </span>
                                    <?php endif ?>
                                    <?php echo $alert["text"] ?>
                                    <br>
                                    <?php echo $comment ?>
                                <?php endforeach ?>
                            </p>
                        <?php else: ?>
                            <p>No alerts have been found for <?php echo $result->getRRIDField("name") ?>.</p>
                        <?php endif ?>
                    </div>
                </div>
            <?php endif ?>
                <div class="section">
                    <div class="title">Data and Source Information</div>
                    <div class="body">
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
                        <?php if($source_database): ?>
                            <p>
                                <strong>Source Database:</strong>
                                <?php echo $source_database; ?>
                            </p>
                        <?php endif ?>
                    </div>
                </div>
                <?php if(!$protocol_flag): ?>
                <div class="section">
                    <div class="title">Usage and Citation Metrics</div>
                    <div class="body">
                        <?php if($item_mentions["hits"]["total"] > 0): ?>
                            <p>We found <?php echo $item_mentions["hits"]["total"] ?> mentions in open access literature.</p>
                            <p>
                                <strong>Listed below are recent publications.</strong>
                                The full list is available at <a href="<?php echo $community->fullURL() ?>/data/record/<?php echo $view ?>/<?php echo $rrid ?>/resolver/mentions"><?php echo $community->shortName ?></a>.
                            </p>
                            <?php foreach($item_mentions["hits"]["hits"] as $mention): ?>
                                <p>
                                    <?php ## modified mentions information -- Vicky-2019-1-17
                                      echo $mention["_source"]["dc"]["creators"][0]["familyName"]." ".$mention["_source"]["dc"]["creators"][0]["initials"].", et al. ";
                                      echo "(".$mention["_source"]["dc"]["publicationYear"].") ";
                                      echo $mention["_source"]["dc"]["title"]." ";
                                      echo $mention["_source"]["dc"]["publishers"][0]["name"];
                                      if ($mention["_source"]["dc"]["publishers"][0]["volume"]) echo ", ".$mention["_source"]["dc"]["publishers"][0]["volume"];
                                      if ($mention["_source"]["dc"]["publishers"][0]["issue"]) echo "(".$mention["_source"]["dc"]["publishers"][0]["issue"].")";
                                      if ($mention["_source"]["dc"]["publishers"][0]["pagination"]) echo ", ".$mention["_source"]["dc"]["publishers"][0]["pagination"];
                                    ?>.
                                </p>
                            <?php endforeach ?>
                        <?php else: ?>
                            <p>We have not found any literature mentions for this resource.</p>
                        <?php endif ?>
                    </div>
                    <?php endif ?>
                </div>
            </div>
        </div>
    </div>
</div>

<?php
$html = ob_get_clean();

$full_data = $style.$html;

$dompdf = new Dompdf();
$dompdf->loadHtml($full_data);
$dompdf->render();
$pdf = $dompdf->output();

header("Content-type: application/pdf");
echo $pdf;
//echo $full_data;

?>
