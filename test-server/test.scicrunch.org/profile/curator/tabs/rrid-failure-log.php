<?php
$page_number = isset($_GET["page_number"]) ? \helper\aR($_GET["page_number"]) : 1;
$per_page = 100;
$offset = ($page_number - 1) * $per_page;
$failures = RRIDFailureLog::loadArrayBy(Array(), Array(), false, $per_page, false, $offset, "timestamp desc");
?>

<div class="tab-pane fade in active">
    <div class="table-search-v2 margin-bottom-20">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Query</th>
                        <th>Referer</th>
                        <th>Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($failures as $failure): ?>
                        <tr>
                            <td><?php echo $failure->searched_id ?></td>
                            <td><?php echo $failure->referer ?></td>
                            <td><?php echo date("M j, Y", $failure->timestamp) ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
    $pagination_data = Array(
        "count" => RRIDFailureLog::totalCount(),
        "per_page" => $per_page,
        "current_page" => $page_number,
        "params" => "",
        "base_url" => $link_base . "/rrid-failure-log",
        "page_location" => "query",
        "query_param_name" => "page_number",
    );
    echo \helper\htmlElement("pagination", $pagination_data);
?>
