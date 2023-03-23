<link rel="stylesheet" href="/css/custom.css">
<!-- Go to www.addthis.com/dashboard to customize your tools -->
<script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=ra-5c34b34e285f1d57"></script>

<?php
    include 'process-elastic-search.php';

    $view = $data["view"];
    $vars = $data["vars"];
    $itemID = $data["itemID"];
    // $tab = $data["tab"];
    $id = $_GET["i"];   // get uid
    $community = $data["community"];

    $dknet_flag = false;
    if($community->rinStyle()) {
        $dknet_flag = true;
    }

    $search_manager = ElasticRRIDManager::esManagerByViewID($view);
    if(is_null($search_manager)) {
        return;
    }
    $results = $search_manager->searchItemID(str_replace('$U+002F;', '/', $itemID));
    if($results->hitCount() == 0) {
        echo \helper\errorPage("noresource", NULL, false);
        return;
    }

    $result = findResult($results, $id);
    $id = $result->getRRIDField("id");
?>

<!-- /name -->
<?php
$rows = Array();
$row = Array();
$cell = Array();
ob_start();
?>
    <div class="row">
          <div class="col-md-12">
              <span class="fa-stack fa-md">
                  <i class="fa fa-circle fa-stack-2x" style="color:#FBBD1A"></i>
                  <i class="fa fa-flask fa-stack-1x fa-inverse"></i>
              </span>
              Item Name <span class="help-tooltip" data-name="resource-report-name.html"></span>
          </div>
    </div>
<?php $cell["title"] = ob_get_clean(); ?>
<?php ob_start(); ?>
    <div class="row">
        <div class="rrid-name col-md-12">
          <a target="_self" href="" style="color:#1C2D5C"><?php echo $result->getRRIDField("name") ?></a>
            <?php if($result->getRRIDField("url")): ?>
                <a target="_blank" href="<?php echo $result->getRRIDField('url') ?>"><i class='fa fa-external-link'></i></a>
            <?php endif ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="rrid">
                <?php echo $result->getRRIDField("item-curie") ?>
            </div>
        </div>
    </div>
<?php
$cell["body"] = Array(Array("html" => ob_get_clean()));
$row[] = $cell;
$rows[] = $row;
?>
<!-- /name -->

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
    Item Information <span class="help-tooltip" data-name="resource-report-information.html"></span>
<?php $cell["title"] = ob_get_clean(); ?>
<?php ob_start(); ?>
    <div class="truncate-desc">
        <?php if($result->getRRIDField("url")): ?>
            <p>
                <strong>URL:</strong>
                <a target="_blank" href="<?php echo $result->getRRIDField("url") ?>"><?php echo $result->getRRIDField("url") ?></a>
            </p>
        <?php endif ?>
        <?php foreach($search_manager->fields() as $field_name): ?>
            <?php if (in_array($field_name->name, ["Uid", "Mentions Count"])) continue; ## removed "Uid" & "Mentions Count"column ?>
            <?php if(!$result->getField($field_name->name) || !$field_name->visible("single-item") || $result->getField($field_name->name) == "CVCL:") continue; ?>
            <p>
                <strong><?php echo $field_name->name ?>:</strong>
                <?php
                    switch($field_name->name) {
                        case "References":   ## added references link -- Vicky-2019-1-15
                        case "RRIDs used":
                            echo implode(", ", buildLinks($result->getField($field_name->name), $community));
                            break;
                        case "Hierarchy":   ## modified "Hierarchy" & "Originate from Same Individual" -- Vicky-2019-1-31
                        case "Originate from Same Individual":
                            echo str_replace(":", "_", $result->getField($field_name->name));
                            break;
                        // case "Cross References":
                        //     echo "<a target='_blank' href='https://www.ncbi.nlm.nih.gov/bioproject/".$result->getField($field_name->name)."'>".$result->getField($field_name->name)."</a>";
                        //     break;
                        case "Comments":
                            $comment = str_replace(['<font color="#ff6347"></> ', '<font color="#000000"></> '], "", $result->getField($field_name->name));
                            if (strpos(strtolower($result->getRRIDField("issues")), "problematic") !== false) {
                                $comment = "<font color='red'>".$comment."</font>";
                            }
                            echo $comment;
                            break;
                        case "Target Antigen":
                            if (trim($result->getField($field_name->name)) == ",") echo "";
                            else echo $result->getField($field_name->name);
                            break;
                        default:
                            echo $result->getField($field_name->name);
                    }

                  // if ($field_name->name == "Reference"){
                  //   $values = explode(",", $result->getField($field_name->name));
                  //   foreach($values as $value){
                  //     $tmp = explode(":", $value);
                  //     echo $tmp[0].":<a href='".$community->fullURL()."/".$tmp[1]."'>$tmp[1]</a>&nbsp;";
                  //   }
                  // }
                  // else echo $result->getField($field_name->name);
                ?>
            </p>
        <?php endforeach ?>
    </div>
<?php
    $cell["body"] = Array(Array("html" => ob_get_clean()));
    $row[] = $cell;
    $rows[] = $row;
?>
<!-- /information -->

    <div>

        <?php

            if(!$dknet_flag) {
                echo Connection::createBreadCrumbs("Discovery ES Report", array('Home', 'Discovery Sources', $search_manager->getName(true)),array('/'.$community->portalName, '/'.$community->portalName . '/discovery/source/all/search', '/'.$community->portalName . '/discovery/source/'. $view . '/search'), $result->getRRIDField("name"));
            }

            $breadcrumbs = Array(
                Array("text" => "Home", "url" => $community->fullURL()),
                Array("text" => "Discovery Sources", "url" => $community->fullURL() . "/discovery/source/all/search"),
                Array("text" => $search_manager->getName(true), "url" => $community->fullURL() . "/discovery/source/" . $view . "/search"),
            );
            $breadcrumbs[] = Array("text" => $result->getRRIDField("name"), "active" => true);

            $rin_data = Array(
                "title" => "Discovery ES Report",
                "breadcrumbs" => $breadcrumbs,
                "rows" => $rows,
            );
            echo \helper\htmlElement("rin-style-page", $rin_data);

        ?>

    </div>
