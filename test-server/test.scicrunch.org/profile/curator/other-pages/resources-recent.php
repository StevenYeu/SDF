<?php

// get page and offsets
$page = isset($_GET["page_number"]) ? (int) $_GET["page_number"] : 1;
$per_page = 50;
$offset = ($page - 1) * $per_page;

// page url path
$url_path = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

// build mysql query strings
$select_types = "";
$select_vars = Array();
$select_where = "";
if(isset($_GET["status"])) {
    $status = $_GET["status"];
    $select_types .= "s";
    $select_vars[] = $status;
    $select_where .= "where status = ? ";
}
$select_types .= "ii";
$select_vars = array_merge($select_vars, Array($offset, $per_page));
$select_where .= "order by time desc limit ?,?";

// mysql call
$cxn = new Connection();
$cxn->connect();

// get this page versions
$versions = $cxn->select("resource_versions", Array("*"), $select_types, $select_vars, $select_where);

// get the total count
if(isset($status)) $total_count_array = $cxn->select("resource_versions", Array("count(*)"), "s", Array($status), "where status = ?");
else $total_count_array = $cxn->select("resource_versions", Array("count(*)"), "", Array(), "");
$total_count = $total_count_array[0]["count(*)"];

$cxn->close();

function status2CSSClass($status) {
    switch($status) {
        case "Pending": return "warning";
        case "Curated": return "success";
        case "Rejected": return "danger";
        case "First Curated": return "info";
    }
}

function getNewStatus($version) {
    // check if this is the first curated version

    $status = $version["status"];
    if($status !== "Curated") return $status;
    if($version["version"] == 1) return "First Curated";
    $cxn = new Connection();
    $cxn->connect();
    $prev = $cxn->select("resource_versions", Array("count(*)"), "ii", Array($version["rid"], $version["version"]), "where rid = ? and status = 'Curated' and version < ?");
    $cxn->close();
    if($prev[0]["count(*)"] == 0) return "First Curated";
    return $status;
}

function activeCSS($status) {
    return isset($_GET["status"]) && $_GET["status"] == $status ? " active" : "";
}

?>

<div class="tab-pane fade in active">
    <div class="container">
        <div class="btn-group" role="group">
            <a href="<?php echo $url_path ?>"><button type="button" class="btn btn-default">All</button></a>
            <a href="<?php echo $url_path ?>?status=Curated"><button type="button" class="btn btn-<?php echo status2CSSClass("Curated"); echo activeCSS("Curated"); ?>">Curated</button></a>
            <a href="<?php echo $url_path ?>?status=Pending"><button type="button" class="btn btn-<?php echo status2CSSClass("Pending"); echo activeCSS("Pending"); ?>">Pending</button></a>
            <a href="<?php echo $url_path ?>?status=Rejected"><button type="button" class="btn btn-<?php echo status2CSSClass("Rejected"); echo activeCSS("Rejected"); ?>">Rejected</button></a>
        </div>
        <ul class="list-group">
            <?php foreach($versions as $ver): ?>
                <?php
                    $resource = new Resource();
                    $resource->getByID($ver["rid"]);
                    if(!$resource->id) continue;
                    $resource->getColumns();
                    $resource_name = $resource->columns["Resource Name"];
                    $new_status = getNewStatus($ver);
                ?>
                <ul class="list-group-item list-group-item-<?php echo status2CSSClass($new_status) ?>">
                    <a target="_blank" href="/browse/resourcesedit/<?php echo $resource->rid ?>">
                        <?php echo $resource_name . " (" . $resource->rid . ")"; ?>
                        | <?php echo $new_status ?> | 
                        <?php echo date("D, M j, Y - g:i A", $ver["time"]) ?>
                    </a>
                </ul>
            <?php endforeach ?>
        </ul>
    </div>
</div>
<?php
     $pagination_data = Array(
        "count" => $total_count,
        "per_page" => $per_page,
        "current_page" => $page,
        "page_location" => "query",
        "query_param_name" => "page_number",
        "base_url" => $url_path,
    );
    if(isset($_GET["status"])) $pagination_data["params"] = "status=" . $_GET["status"];
    echo \helper\htmlElement("pagination", $pagination_data);
?>
