<?php
$page_number = isset($_GET["page_number"]) ? \helper\aR($_GET["page_number"]) : 1;
$per_page = 100;
$offset = ($page_number - 1) * $per_page;
$failures = SearchFederationFailureLog::loadArrayBy(Array("result_count"), Array(0), false, $per_page, false, $offset, "timestamp desc");
?>

<style>
    .search-row {
        word-wrap: break-word;
        max-width: 200px;
    }
</style>

<div class="tab-pane fade in active">
    <div class="table-search-v2 margin-bottom-20">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Query</th>
                        <th>Referer</th>
                        <th>Community</th>
                        <th>Source</th>
                        <th>Category</th>
                        <th>Subcategory</th>
                        <th>NIF Status Code</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($failures as $failure): ?>
                        <?php
                            $comm = new Community();
                            $comm->getByID($failure->cid);
                        ?>
                        <tr>
                            <td class="search-row"><?php echo $failure->query ?></td>
                            <td class="search-row"><?php echo $failure->referer ?></td>
                            <td class="search-row"><?php echo $comm->portalName ?></td>
                            <td class="search-row"><?php echo $failure->source ?></td>
                            <td class="search-row"><?php echo $failure->category ?></td>
                            <td class="search-row"><?php echo $failure->subcategory ?></td>
                            <td class="search-row"><?php echo $failure->status_code ?></td>
                            <td class="search-row"><?php echo date("H:i - M j, Y", $failure->timestamp) ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
    $pagination_data = Array(
        "count" => SearchFederationFailureLog::totalCount(),
        "per_page" => $per_page,
        "current_page" => $page_number,
        "params" => "",
        "base_url" => $link_base . "/failed-searches-log",
        "page_location" => "query",
        "query_param_name" => "page_number",
    );
    echo \helper\htmlElement("pagination", $pagination_data);
?>
